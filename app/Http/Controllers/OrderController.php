<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use App\Models\CustomerProject;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentSetting;
use App\Models\PriceSetting;
use App\Models\Setting;
use App\Models\StickerSize;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function store(Request $request, InvoiceService $invoiceService): RedirectResponse
    {
        $adminMode = $request->routeIs('admin.orders.store');

        $validated = $request->validate([
            'customer_id' => [
                $adminMode ? 'required' : 'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('is_admin', false)),
            ],
            'customer_address_id' => [
                'nullable',
                'integer',
                Rule::exists('customer_addresses', 'id')->where(fn ($query) => $query->where('user_id', $adminMode ? $request->input('customer_id') : Auth::id())),
            ],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_address' => ['required', 'string'],
            'design_id' => ['nullable', 'integer', 'exists:sticker_designs,id'],
            'project_id' => ['nullable', 'integer', 'exists:customer_projects,id'],
            'custom_description' => ['nullable', 'string', 'max:2000'],
            'order_note' => ['nullable', 'string', 'max:2000'],
            'size_id' => ['nullable', 'integer', 'exists:sticker_sizes,id'],
            'requested_size' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'cut_type' => ['required', Rule::in(['standard', 'die-cut'])],
            'customer_design_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'repeat_from_order_id' => ['nullable', 'integer', 'exists:orders,id'],
        ]);

        abort_unless($adminMode ? Auth::user()?->is_admin : Auth::check(), 403);

        $customerId = $adminMode ? (int) $validated['customer_id'] : Auth::id();

        // Die-cut must be 5cm or above
        if ($validated['cut_type'] === 'die-cut' && ! empty($validated['size_id'])) {
            $size = StickerSize::query()->find($validated['size_id']);
            if ($size && max($size->width_cm, $size->height_cm) < 5) {
                return redirect()->back()->with('error', 'Potong ikut bentuk (die-cut) hanya boleh untuk saiz 5cm ke atas.')->withInput();
            }
        }

        if (! empty($validated['repeat_from_order_id'])) {
            $isOwnedRepeatOrder = Order::query()
                ->whereKey($validated['repeat_from_order_id'])
                ->where('user_id', $customerId)
                ->exists();

            abort_if(! $isOwnedRepeatOrder, 403);
        }

        $customerProject = null;
        if (! empty($validated['project_id'])) {
            $customerProject = CustomerProject::query()
                ->whereKey($validated['project_id'])
                ->where('user_id', $customerId)
                ->with('customerAddress')
                ->first();

            abort_if(! $customerProject, 403);
        }

        $customerAddress = $customerProject?->customerAddress;
        if (! $customerAddress && ! empty($validated['customer_address_id'])) {
            $customerAddress = CustomerAddress::query()->find((int) $validated['customer_address_id']);
        }

        if ($customerAddress) {
            $validated['customer_address_id'] = $customerAddress->id;
            $validated['customer_name'] = $customerAddress->recipient_name ?: $validated['customer_name'];
            $validated['customer_phone'] = $customerAddress->no_hp ?: $validated['customer_phone'];
            $validated['customer_address'] = $customerAddress->address;
        }

        $paymentSettings = PaymentSetting::query()->first();
        $depositAmount = $paymentSettings?->deposit_amount ?? 20;

        $customerDesignPath = $request->hasFile('customer_design_image')
            ? $request->file('customer_design_image')->store('customer-designs', 'public')
            : null;

        $order = DB::transaction(function () use ($validated, $depositAmount, $customerDesignPath, $customerProject, $customerId, $customerAddress) {
            $resolvedCustomerAddress = $customerAddress ?? CustomerAddress::query()->firstOrCreate([
                'user_id' => $customerId,
                'address' => $validated['customer_address'],
            ], [
                'recipient_name' => $validated['customer_name'],
                'no_hp' => $validated['customer_phone'],
            ]);

            // Calculate price using new formula: ceil(qty / qty_per_a3) * price_per_a3
            $subtotal = 0;
            $isPending = false;

            if (! empty($validated['size_id']) && ! empty($validated['quantity'])) {
                $size = StickerSize::query()->find($validated['size_id']);

                if ($size && $size->qty_per_a3) {
                    $a3Sheets = (int) ceil($validated['quantity'] / $size->qty_per_a3);

                    $priceSetting = PriceSetting::query()
                        ->where('is_active', true)
                        ->where('sticker_type', 'Mirrorcote')
                        ->where('qty_from', '<=', $a3Sheets)
                        ->where(function ($q) use ($a3Sheets) {
                            $q->where('qty_to', '>=', $a3Sheets)
                                ->orWhereNull('qty_to');
                        })
                        ->orderBy('qty_from')
                        ->first();

                    if ($priceSetting) {
                        $subtotal = (int) $a3Sheets * (float) $priceSetting->price_per_a3;
                    } else {
                        $isPending = true;
                    }
                } else {
                    $isPending = true;
                }
            } else {
                $isPending = true;
            }

            $order = Order::query()->create([
                'user_id' => $customerId,
                'customer_address_id' => $resolvedCustomerAddress->id,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'material' => 'Mirrorcote',
                'custom_request' => ! empty($validated['order_note'])
                    ? $validated['order_note']
                    : ($validated['custom_description'] ?? null),
                'custom_description' => $validated['custom_description'] ?? null,
                'repeat_from_order_id' => $validated['repeat_from_order_id'] ?? null,
                'status' => 'pending',
                'subtotal' => $isPending ? 0 : $subtotal,
                'total' => $isPending ? 0 : $subtotal,
                'pricing_status' => $isPending ? 'pending_admin' : 'auto_priced',
                'deposit_amount' => $isPending ? 0 : $depositAmount,
                'balance_due' => $isPending ? 0 : ($subtotal - $depositAmount),
                'payment_status' => 'pending',
            ]);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'sticker_design_id' => $customerProject ? null : ($validated['design_id'] ?? null),
                'customer_project_id' => $customerProject?->id,
                'custom_design_description' => $customerProject?->title ?? (empty($validated['design_id']) ? ($validated['custom_description'] ?? null) : null),
                'sticker_size_id' => $validated['size_id'] ?? null,
                'requested_size' => $validated['requested_size'] ?? null,
                'quantity' => $validated['quantity'],
                'cut_type' => $validated['cut_type'],
                'customer_design_path' => $customerDesignPath,
                'unit_price' => $isPending ? 0 : ($subtotal / $validated['quantity']),
                'line_total' => $isPending ? 0 : $subtotal,
            ]);

            return $order;
        });

        $this->sendToN8n($order, $customerDesignPath);

        if (! $adminMode) {
            return redirect()->route('orders.thank-you', $order);
        }

        if ((float) $order->total > 0 && ! in_array($order->pricing_status, ['pending_admin', 'awaiting_customer_approval'], true)) {
            $invoice = $invoiceService->createForOrder($order);

            return redirect()
                ->route('admin.invoices.edit', $invoice)
                ->with('success', 'Order dan invoice berjaya dicipta. Sila semak dan sahkan invoice.');
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('info', 'Order berjaya dicipta, tetapi invoice menunggu harga diluluskan.');
    }

    private function sendToN8n(Order $order, ?string $customerDesignPath): void
    {
        $webhookUrl = Setting::getValue('n8n_webhook_url');
        if (! $webhookUrl) {
            return;
        }

        $linkGambar = $customerDesignPath
            ? url('storage/'.$customerDesignPath)
            : null;

        $message = "Tempahan Baru! 🎉\n\n"
            ."No. Order: {$order->order_no}\n"
            ."Pelanggan: {$order->customer_name}\n"
            ."Telefon: {$order->customer_phone}\n"
            ."Alamat: {$order->customer_address}\n"
            ."Status: {$order->status}\n"
            .'Jumlah: RM'.number_format($order->total, 2)."\n";

        if ($linkGambar) {
            $message .= "Gambar: {$linkGambar}\n";
        }

        try {
            Http::timeout(10)->post($webhookUrl, [
                'message' => $message,
                'link_gambar' => $linkGambar,
            ]);
        } catch (\Throwable $e) {
            logger()->error('N8n webhook failed: '.$e->getMessage());
        }
    }

    public function thankYou(Order $order): Response
    {
        abort_if($order->user_id !== Auth::id(), 403);

        $paymentSettings = PaymentSetting::query()->first();
        if ($paymentSettings && $paymentSettings->qr_image_path) {
            $paymentSettings->qr_image_url = Storage::disk('public')->url($paymentSettings->qr_image_path);
        }

        return Inertia::render('Public/OrderThankYou', [
            'order' => $order->load(['items.design', 'items.project', 'items.size', 'invoice']),
            'paymentSettings' => $paymentSettings,
        ]);
    }

    public function lookup(Request $request): Response
    {
        $validated = $request->validate([
            'customer_phone' => ['required', 'string', 'max:30'],
        ]);

        $orders = Order::query()
            ->where('customer_phone', $validated['customer_phone'])
            ->with(['items.design', 'items.size', 'invoice'])
            ->latest()
            ->get();

        return Inertia::render('Public/LookupOrder', [
            'orders' => $orders,
            'customerPhone' => $validated['customer_phone'],
        ]);
    }
}
