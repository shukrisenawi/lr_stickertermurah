<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentSetting;
use App\Models\StickerDesign;
use App\Models\StickerPriceTier;
use App\Models\StickerSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_address' => ['required', 'string'],
            'design_id' => ['nullable', 'integer', 'exists:sticker_designs,id'],
            'custom_description' => ['nullable', 'string', 'max:2000'],
            'size_id' => ['nullable', 'integer', 'exists:sticker_sizes,id'],
            'requested_size' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'repeat_from_order_id' => ['nullable', 'integer', 'exists:orders,id'],
        ]);

        if (! empty($validated['repeat_from_order_id'])) {
            $isOwnedRepeatOrder = Order::query()
                ->whereKey($validated['repeat_from_order_id'])
                ->where('user_id', Auth::id())
                ->exists();

            abort_if(! $isOwnedRepeatOrder, 403);
        }

        $paymentSettings = PaymentSetting::query()->first();
        $depositAmount = $paymentSettings?->deposit_amount ?? 20;

        $order = DB::transaction(function () use ($validated, $depositAmount) {
            CustomerAddress::query()->firstOrCreate([
                'user_id' => Auth::id(),
                'address' => $validated['customer_address'],
            ]);

            // Calculate price
            $subtotal = 0;
            $isPending = false;

            if (! empty($validated['size_id']) && ! empty($validated['quantity'])) {
                $tiers = StickerPriceTier::query()
                    ->where('sticker_size_id', $validated['size_id'])
                    ->orderByDesc('quantity')
                    ->get();

                $match = $tiers->first(fn ($t) => $validated['quantity'] >= $t->quantity);
                if ($match) {
                    $subtotal = $match->total_price;
                } else {
                    $isPending = true;
                }
            } else {
                $isPending = true;
            }

            $order = Order::query()->create([
                'user_id' => Auth::id(),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'material' => 'Mirrorcote',
                'custom_request' => $validated['custom_description'] ?? null,
                'custom_description' => $validated['custom_description'] ?? null,
                'repeat_from_order_id' => $validated['repeat_from_order_id'] ?? null,
                'status' => 'pending',
                'subtotal' => $isPending ? 0 : $subtotal,
                'total' => $isPending ? 0 : $subtotal,
                'deposit_amount' => $isPending ? 0 : $depositAmount,
                'balance_due' => $isPending ? 0 : ($subtotal - $depositAmount),
                'payment_status' => 'pending',
            ]);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'sticker_design_id' => $validated['design_id'] ?? null,
                'custom_design_description' => empty($validated['design_id']) ? ($validated['custom_description'] ?? null) : null,
                'sticker_size_id' => $validated['size_id'] ?? null,
                'requested_size' => $validated['requested_size'] ?? null,
                'quantity' => $validated['quantity'],
                'unit_price' => $isPending ? 0 : ($subtotal / $validated['quantity']),
                'line_total' => $isPending ? 0 : $subtotal,
            ]);

            return $order;
        });

        return redirect()->route('orders.thank-you', $order)->with('success', 'Tempahan berjaya dihantar!');
    }

    public function thankYou(Order $order): Response
    {
        abort_if($order->user_id !== Auth::id(), 403);

        $paymentSettings = PaymentSetting::query()->first();
        if ($paymentSettings && $paymentSettings->qr_image_path) {
            $paymentSettings->qr_image_url = Storage::disk('public')->url($paymentSettings->qr_image_path);
        }

        return Inertia::render('Public/OrderThankYou', [
            'order' => $order->load(['items.design', 'items.size']),
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
