<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StickerDesign;
use App\Models\StickerSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_address' => ['required', 'string'],
            'custom_request' => ['nullable', 'string'],
            'repeat_from_order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'payment_receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sticker_design_id' => ['required', 'integer', 'exists:sticker_designs,id'],
            'items.*.sticker_size_id' => ['required', 'integer', 'exists:sticker_sizes,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $receiptPath = $request->file('payment_receipt')?->store('payment-receipts', 'public');

        $order = DB::transaction(function () use ($validated, $receiptPath) {
            $order = Order::query()->create([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'material' => 'Mirrorcote',
                'custom_request' => $validated['custom_request'] ?? null,
                'payment_receipt_path' => $receiptPath,
                'repeat_from_order_id' => $validated['repeat_from_order_id'] ?? null,
                'status' => 'pending',
            ]);

            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $design = StickerDesign::query()->findOrFail($item['sticker_design_id']);
                $size = StickerSize::query()->findOrFail($item['sticker_size_id']);
                $lineTotal = $size->price * (int) $item['quantity'];

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'sticker_design_id' => $design->id,
                    'sticker_size_id' => $size->id,
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => $size->price,
                    'line_total' => $lineTotal,
                ]);

                $subtotal += $lineTotal;
            }

            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);

            return $order;
        });

        return redirect()->route('orders.thank-you', $order)->with('success', 'Order berjaya dihantar. Simpan nombor order anda.');
    }

    public function thankYou(Order $order): View
    {
        return view('frontend.order-thank-you', [
            'order' => $order->load(['items.design', 'items.size']),
        ]);
    }

    public function lookup(Request $request): View
    {
        $validated = $request->validate([
            'customer_phone' => ['required', 'string', 'max:30'],
        ]);

        $orders = Order::query()
            ->where('customer_phone', $validated['customer_phone'])
            ->with(['items.design', 'items.size', 'invoice'])
            ->latest()
            ->get();

        return view('frontend.lookup-order', [
            'orders' => $orders,
            'customerPhone' => $validated['customer_phone'],
        ]);
    }
}
