<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use App\Models\CustomerProject;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentSetting;
use App\Models\Setting;
use App\Models\StickerSize;
use App\Services\InvoiceService;
use App\Services\ShippingService;
use App\Services\StickerPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function store(
        Request $request,
        InvoiceService $invoiceService,
        ShippingService $shippingService,
        StickerPricingService $stickerPricing,
    ): RedirectResponse {
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
            'shipping_region' => ['nullable', Rule::in([
                ShippingService::PENINSULAR,
                ShippingService::SABAH_SARAWAK,
            ])],
            'shipping_free' => ['nullable', 'boolean'],
            'shipping_free_forever' => ['nullable', 'boolean'],
            'quantity' => ['required', 'integer', 'min:1'],
            'cut_type' => ['required', Rule::in(['standard', 'die-cut'])],
            'manual_qty_per_a3' => $adminMode
                ? ['nullable', 'integer', 'min:1']
                : ['prohibited'],
            'customer_design_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'customer_design_images' => ['nullable', 'array', 'max:10'],
            'customer_design_images.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'repeat_from_order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'previous_order_item_id' => ['nullable', 'integer', 'exists:order_items,id'],
            'items' => ['nullable', 'array', 'min:1', 'max:50'],
            'items.*.design_id' => ['nullable', 'integer', 'exists:sticker_designs,id'],
            'items.*.project_id' => ['nullable', 'integer', 'exists:customer_projects,id'],
            'items.*.custom_description' => ['nullable', 'string', 'max:2000'],
            'items.*.size_id' => ['nullable', 'integer', 'exists:sticker_sizes,id'],
            'items.*.requested_size' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.cut_type' => ['required', Rule::in(['standard', 'die-cut'])],
            'items.*.manual_qty_per_a3' => $adminMode
                ? ['nullable', 'integer', 'min:1']
                : ['prohibited'],
            'items.*.customer_design_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'items.*.customer_design_images' => ['nullable', 'array', 'max:10'],
            'items.*.customer_design_images.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'items.*.previous_order_item_id' => ['nullable', 'integer', 'exists:order_items,id'],
        ]);

        abort_unless($adminMode ? Auth::user()?->is_admin : Auth::check(), 403);

        $customerId = $adminMode ? (int) $validated['customer_id'] : Auth::id();
        $validated['shipping_region'] = $shippingService->normalize($validated['shipping_region'] ?? null);
        $shippingFreeForever = $adminMode && (bool) ($validated['shipping_free_forever'] ?? false);
        $shippingFree = $adminMode && ($shippingFreeForever || (bool) ($validated['shipping_free'] ?? false));

        $rawItems = array_key_exists('items', $validated)
            ? $validated['items']
            : [[
                'design_id' => $validated['design_id'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'custom_description' => $validated['custom_description'] ?? null,
                'size_id' => $validated['size_id'] ?? null,
                'requested_size' => $validated['requested_size'] ?? null,
                'quantity' => $validated['quantity'],
                'cut_type' => $validated['cut_type'],
                'manual_qty_per_a3' => $validated['manual_qty_per_a3'] ?? null,
                'customer_design_image' => $validated['customer_design_image'] ?? null,
                'customer_design_images' => $validated['customer_design_images'] ?? [],
                'previous_order_item_id' => $validated['previous_order_item_id'] ?? null,
            ]];
        $items = array_values($rawItems);

        foreach ($items as $item) {
            // Die-cut must be 5cm or above for every item in the order.
            if (($item['cut_type'] ?? null) === 'die-cut' && ! empty($item['size_id'])) {
                $size = StickerSize::query()->find($item['size_id']);
                if ($size && max($size->width_cm, $size->height_cm) < 5) {
                    return redirect()->back()->with('error', 'Potong ikut bentuk (die-cut) hanya boleh untuk saiz 5cm ke atas.')->withInput();
                }
            }
        }

        $repeatOrder = null;
        if (! empty($validated['repeat_from_order_id'])) {
            $repeatOrder = Order::query()
                ->whereKey($validated['repeat_from_order_id'])
                ->where('user_id', $customerId)
                ->with('items')
                ->first();

            abort_if(! $repeatOrder, 403);
        }

        $customerProjects = collect();
        foreach ($items as $item) {
            if (empty($item['project_id'])) {
                continue;
            }

            $customerProject = CustomerProject::query()
                ->whereKey($item['project_id'])
                ->where('user_id', $customerId)
                ->with('customerAddress')
                ->first();

            abort_if(! $customerProject, 403);
            $customerProjects->push($customerProject);
        }
        $customerProject = $customerProjects->first();

        $previousOrderItemIds = collect($items)
            ->pluck('previous_order_item_id')
            ->filter(fn ($id): bool => filled($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();
        $previousOrderItems = $previousOrderItemIds->isEmpty()
            ? collect()
            : OrderItem::query()
                ->whereIn('id', $previousOrderItemIds)
                ->whereHas('order', fn ($query) => $query->where('user_id', $customerId))
                ->get()
                ->keyBy('id');

        abort_if($previousOrderItems->count() !== $previousOrderItemIds->count(), 403);

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

        $customerDesignPaths = [];
        foreach ($items as $index => $item) {
            $files = $item['customer_design_images'] ?? [];
            $files = is_array($files) ? $files : [$files];

            if ($files === [] && ! empty($item['customer_design_image'])) {
                $files = [$item['customer_design_image']];
            }

            $customerDesignPaths[$index] = collect($files)
                ->filter()
                ->map(fn ($file): string => $file->store('customer-designs', 'public'))
                ->values()
                ->all();
        }

        $repeatFreeShipping = $this->repeatQualifiesForFreeShipping(
            $repeatOrder,
            $items,
            $previousOrderItems,
            $customerDesignPaths,
        );
        if (! $adminMode) {
            $shippingFree = $repeatFreeShipping;
            $shippingFreeForever = $repeatFreeShipping;
        }

        $order = DB::transaction(function () use ($validated, $items, $depositAmount, $customerDesignPaths, $customerProjects, $previousOrderItems, $customerId, $customerAddress, $shippingService, $stickerPricing, $shippingFree, $shippingFreeForever, $adminMode) {
            $resolvedCustomerAddress = $customerAddress ?? CustomerAddress::query()->firstOrCreate([
                'user_id' => $customerId,
                'address' => $validated['customer_address'],
            ], [
                'recipient_name' => $validated['customer_name'],
                'no_hp' => $validated['customer_phone'],
            ]);

            $subtotal = 0;
            $isPending = false;
            $itemPricing = [];

            foreach ($items as $index => $item) {
                $lineTotal = 0;
                $itemIsPending = false;
                $manualQtyPerA3 = $adminMode && filled($item['manual_qty_per_a3'] ?? null)
                    ? (int) $item['manual_qty_per_a3']
                    : null;
                $quotedQtyPerA3 = null;
                $quotedPricePerA3 = null;
                $quotedStickerType = null;
                $size = ! empty($item['size_id'])
                    ? StickerSize::query()->find($item['size_id'])
                    : null;
                $previousItem = ! empty($item['previous_order_item_id'])
                    ? $previousOrderItems->get((int) $item['previous_order_item_id'])
                    : null;
                $hasNewDesign = $stickerPricing->hasDesign(
                    isset($item['design_id']) ? (int) $item['design_id'] : null,
                    isset($item['project_id']) ? (int) $item['project_id'] : null,
                    null,
                    $customerDesignPaths[$index] ?? [],
                );
                $hasDesign = $previousItem
                    ? $stickerPricing->hasExistingDesign($previousItem) || $hasNewDesign
                    : $hasNewDesign;

                $previousQuoteIsValid = $previousItem
                    && (int) ($item['size_id'] ?? 0) === (int) ($previousItem->sticker_size_id ?? 0)
                    && trim((string) ($item['requested_size'] ?? '')) === trim((string) ($previousItem->requested_size ?? ''))
                    && (int) $previousItem->quoted_qty_per_a3 > 0
                    && (float) $previousItem->quoted_price_per_a3 > 0;
                if ($previousQuoteIsValid && $manualQtyPerA3 === null) {
                    $a3Sheets = $stickerPricing->a3Sheets((int) $item['quantity'], (int) $previousItem->quoted_qty_per_a3, $hasDesign);
                    $lineTotal = round($a3Sheets * (float) $previousItem->quoted_price_per_a3, 2);
                    $quotedQtyPerA3 = (int) $previousItem->quoted_qty_per_a3;
                    $quotedPricePerA3 = round((float) $previousItem->quoted_price_per_a3, 2);
                    $quotedStickerType = $previousItem->quoted_sticker_type ?: 'Mirrorcote';
                } elseif ($size && ! empty($item['quantity'])) {
                    if ($size->qty_per_a3) {
                        $pricing = $stickerPricing->calculate($size, (int) $item['quantity'], $hasDesign);

                        if ($pricing) {
                            $lineTotal = $pricing['line_total'];
                        } else {
                            $itemIsPending = true;
                        }
                    } else {
                        $itemIsPending = true;
                    }
                } else {
                    $itemIsPending = true;
                }

                if ($itemIsPending && $manualQtyPerA3 !== null) {
                    $a3Sheets = $stickerPricing->a3Sheets((int) $item['quantity'], $manualQtyPerA3, $hasDesign);
                    $priceSetting = $stickerPricing->priceFor('Mirrorcote', $a3Sheets);

                    if ($priceSetting) {
                        $lineTotal = round($a3Sheets * (float) $priceSetting->price_per_a3, 2);
                        $itemIsPending = false;
                        $quotedQtyPerA3 = $manualQtyPerA3;
                        $quotedPricePerA3 = round((float) $priceSetting->price_per_a3, 2);
                        $quotedStickerType = $priceSetting->sticker_type;
                    }
                }

                $itemPricing[$index] = [
                    'line_total' => $lineTotal,
                    'is_pending' => $itemIsPending,
                    'quoted_qty_per_a3' => $quotedQtyPerA3,
                    'quoted_price_per_a3' => $quotedPricePerA3,
                    'quoted_sticker_type' => $quotedStickerType,
                ];
                $subtotal += $lineTotal;
                $isPending = $isPending || $itemIsPending;
            }

            $shippingFee = $isPending
                ? 0
                : $shippingService->calculate($subtotal, $validated['shipping_region'], $shippingFree);
            $total = $isPending ? 0 : $subtotal + $shippingFee;
            $deposit = $isPending ? 0 : min($depositAmount, $total);

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
                'total' => $total,
                'shipping_region' => $validated['shipping_region'],
                'shipping_fee' => $shippingFee,
                'shipping_free' => $shippingFree,
                'shipping_free_forever' => $shippingFreeForever,
                'pricing_status' => $isPending ? 'pending_admin' : 'auto_priced',
                'deposit_amount' => $deposit,
                'balance_due' => max(0, $total - $deposit),
                'payment_status' => 'pending',
            ]);

            foreach ($items as $index => $item) {
                $lineTotal = $itemPricing[$index]['line_total'];
                $itemIsPending = $itemPricing[$index]['is_pending'];
                $itemProject = ! empty($item['project_id'])
                    ? $customerProjects->firstWhere('id', $item['project_id'])
                    : null;
                $previousItem = ! empty($item['previous_order_item_id'])
                    ? $previousOrderItems->get((int) $item['previous_order_item_id'])
                    : null;
                $hasNewCustomerDesign = ! empty($customerDesignPaths[$index]);
                $previousSourcePaths = $previousItem
                    ? collect($previousItem->admin_source_paths ?: [$previousItem->admin_source_path])->filter()->values()->all()
                    : [];
                $previousPreviewPaths = $previousItem
                    ? collect($previousItem->customer_preview_paths ?: [$previousItem->customer_preview_path])->filter()->values()->all()
                    : [];
                $previousCustomerDesignPaths = $previousItem
                    ? collect($previousItem->customer_design_paths ?: [$previousItem->customer_design_path])->filter()->values()->all()
                    : [];

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'sticker_design_id' => $itemProject ? null : ($item['design_id'] ?? $previousItem?->sticker_design_id),
                    'customer_project_id' => $itemProject?->id,
                    'custom_design_description' => $itemProject?->title
                        ?? (empty($item['design_id'])
                            ? (($item['custom_description'] ?? null) ?: $previousItem?->custom_design_description)
                            : null),
                    'sticker_size_id' => $item['size_id'] ?? null,
                    'requested_size' => $item['requested_size'] ?? null,
                    'quantity' => $item['quantity'],
                    'cut_type' => $item['cut_type'],
                    'customer_design_path' => $hasNewCustomerDesign
                        ? ($customerDesignPaths[$index][0] ?? null)
                        : ($previousCustomerDesignPaths[0] ?? null),
                    'customer_design_paths' => $hasNewCustomerDesign
                        ? ($customerDesignPaths[$index] ?: null)
                        : ($previousCustomerDesignPaths ?: null),
                    'admin_source_path' => $hasNewCustomerDesign ? null : ($previousSourcePaths[0] ?? null),
                    'admin_source_paths' => $hasNewCustomerDesign ? null : ($previousSourcePaths ?: null),
                    'customer_preview_path' => $hasNewCustomerDesign ? null : ($previousPreviewPaths[0] ?? null),
                    'customer_preview_paths' => $hasNewCustomerDesign ? null : ($previousPreviewPaths ?: null),
                    'unit_price' => $itemIsPending ? 0 : ($lineTotal / $item['quantity']),
                    'line_total' => $itemIsPending ? 0 : $lineTotal,
                    'quoted_qty_per_a3' => $itemPricing[$index]['quoted_qty_per_a3'],
                    'quoted_price_per_a3' => $itemPricing[$index]['quoted_price_per_a3'],
                    'quoted_sticker_type' => $itemPricing[$index]['quoted_sticker_type'],
                ]);
            }

            return $order;
        });

        if (! $adminMode) {
            $this->sendToN8n($order, collect($customerDesignPaths)->flatten()->filter()->values()->all());
        }

        $invoice = null;
        if ((float) $order->total > 0 && ! in_array($order->pricing_status, ['pending_admin', 'awaiting_customer_approval'], true)) {
            $invoice = $invoiceService->createForOrder($order);
        }

        if (! $adminMode) {
            return redirect()->route('orders.thank-you', $order);
        }

        if ($invoice) {
            return redirect()
                ->route('admin.invoices.edit', $invoice)
                ->with('success', 'Order dan invoice berjaya dicipta. Sila semak dan sahkan invoice.');
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('info', 'Order berjaya dicipta, tetapi invoice menunggu harga diluluskan.');
    }

    private function sendToN8n(Order $order, array $customerDesignPaths): void
    {
        $webhookUrl = Setting::getValue('n8n_webhook_url');
        if (! $webhookUrl) {
            return;
        }

        $imageLinks = collect($customerDesignPaths)
            ->map(fn (string $path): string => url('storage/'.$path))
            ->values()
            ->all();
        $linkGambar = $imageLinks[0] ?? null;

        $message = "Tempahan Baru! 🎉\n\n"
            ."No. Order: {$order->order_no}\n"
            ."Pelanggan: {$order->customer_name}\n"
            ."Telefon: {$order->customer_phone}\n"
            ."Alamat: {$order->customer_address}\n"
            ."Status: {$order->status}\n"
            .'Jumlah: RM'.number_format($order->total, 2)."\n";

        if ($imageLinks !== []) {
            $message .= "Gambar:\n".collect($imageLinks)->map(fn (string $link): string => "- {$link}")->implode("\n")."\n";
        }

        try {
            Http::timeout(10)->post($webhookUrl, [
                'message' => $message,
                'link_gambar' => $linkGambar,
                'link_gambar_list' => $imageLinks,
            ]);
        } catch (\Throwable $e) {
            logger()->error('N8n webhook failed: '.$e->getMessage());
        }
    }

    public function thankYou(Order $order, StickerPricingService $stickerPricing): Response
    {
        abort_if($order->user_id !== Auth::id(), 403);

        $paymentSettings = PaymentSetting::query()->first();
        if ($paymentSettings && $paymentSettings->qr_image_path) {
            $paymentSettings->qr_image_url = Storage::disk('public')->url($paymentSettings->qr_image_path);
        }

        $order = $order->load(['items.design', 'items.project', 'items.size', 'invoice']);
        $order->items->each(function (OrderItem $item) use ($stickerPricing): void {
            $item->setAttribute('has_design', $stickerPricing->hasExistingDesign($item));
        });

        return Inertia::render('Member/OrderThankYou', [
            'order' => $order,
            'paymentSettings' => $paymentSettings,
        ]);
    }

    private function repeatQualifiesForFreeShipping(
        ?Order $repeatOrder,
        array $items,
        Collection $previousOrderItems,
        array $customerDesignPaths,
    ): bool {
        if ($repeatOrder === null || ! $repeatOrder->shipping_free_forever) {
            return false;
        }

        $sourceItems = $repeatOrder->items->values();
        if ($sourceItems->count() !== count($items)) {
            return false;
        }

        $matchedSourceIds = [];
        foreach ($items as $index => $item) {
            $previousItemId = filled($item['previous_order_item_id'] ?? null)
                ? (int) $item['previous_order_item_id']
                : null;
            $sourceItem = $previousItemId !== null
                ? $previousOrderItems->get($previousItemId)
                : $sourceItems->get($index);

            if (! $sourceItem || (int) $sourceItem->order_id !== (int) $repeatOrder->id) {
                return false;
            }

            if (in_array($sourceItem->id, $matchedSourceIds, true)) {
                return false;
            }
            $matchedSourceIds[] = $sourceItem->id;

            if (! empty($customerDesignPaths[$index])
                || (int) ($item['quantity'] ?? 0) !== (int) $sourceItem->quantity
                || ! $this->repeatSizeMatches($sourceItem, $item)
                || ! $this->repeatDesignMatches($sourceItem, $item, $previousItemId)) {
                return false;
            }
        }

        return count($matchedSourceIds) === $sourceItems->count();
    }

    private function repeatSizeMatches(OrderItem $sourceItem, array $item): bool
    {
        return (int) ($item['size_id'] ?? 0) === (int) ($sourceItem->sticker_size_id ?? 0)
            && $this->normalizeRepeatText($item['requested_size'] ?? null) === $this->normalizeRepeatText($sourceItem->requested_size);
    }

    private function repeatDesignMatches(OrderItem $sourceItem, array $item, ?int $previousItemId): bool
    {
        $designId = filled($item['design_id'] ?? null) ? (int) $item['design_id'] : null;
        $projectId = filled($item['project_id'] ?? null) ? (int) $item['project_id'] : null;

        if ($sourceItem->sticker_design_id !== null) {
            return $projectId === null
                && (($designId !== null && $designId === (int) $sourceItem->sticker_design_id)
                    || ($designId === null && $previousItemId === (int) $sourceItem->id));
        }

        if ($sourceItem->customer_project_id !== null) {
            return $designId === null && $projectId === (int) $sourceItem->customer_project_id;
        }

        if ($designId !== null || $projectId !== null) {
            return false;
        }

        $submittedDescription = $this->normalizeRepeatText($item['custom_description'] ?? null);
        $sourceDescription = $this->normalizeRepeatText($sourceItem->custom_design_description);

        return $submittedDescription === $sourceDescription
            || ($submittedDescription === '' && $previousItemId === (int) $sourceItem->id);
    }

    private function normalizeRepeatText(?string $value): string
    {
        return preg_replace('/\s+/', ' ', strtolower(trim((string) $value))) ?? '';
    }

    public function lookup(Request $request): Response|RedirectResponse
    {
        $validated = $request->validate([
            'order_no' => ['required', 'string', 'max:50'],
            'customer_phone' => ['required', 'string', 'max:30'],
        ]);

        $order = Order::query()
            ->where('order_no', strtoupper(trim($validated['order_no'])))
            ->whereIn('customer_phone', $this->phoneVariants($validated['customer_phone']))
            ->with('invoice')
            ->first();

        if (! $order) {
            return back()
                ->withErrors(['lookup' => 'No. order dan nombor telefon tidak sepadan.'])
                ->withInput();
        }

        return Inertia::render('Public/LookupOrder', [
            'order' => [
                'order_no' => $order->order_no,
                'status' => $order->status,
                'pricing_status' => $order->pricing_status,
                'payment_status' => $order->invoice?->payment_status ?? $order->payment_status ?? 'pending',
                'total' => (float) $order->total,
                'created_at' => $order->created_at?->toISOString(),
                'tracking_no' => $order->customerTrackingNo(),
            ],
        ]);
    }

    private function phoneVariants(string $phone): array
    {
        $raw = trim($phone);
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        $international = str_starts_with($digits, '0')
            ? '60'.substr($digits, 1)
            : $digits;
        $local = str_starts_with($international, '60')
            ? '0'.substr($international, 2)
            : null;

        return array_values(array_filter(array_unique([$raw, $digits, $international, $local])));
    }
}
