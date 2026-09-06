<?php

namespace App\Services;

use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(
        private readonly ShippingService $shippingService,
        private readonly StickerPricingService $stickerPricing,
    ) {}

    public function createForOrder(Order $order, ?string $notes = null): Invoice
    {
        $existingInvoice = $order->invoice()->with('items')->first();
        if ($existingInvoice) {
            return $existingInvoice;
        }

        $order->loadMissing('user');
        $this->applyPersistentDiscountToOrder($order);
        $order->refresh();

        $invoice = Invoice::query()->create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'customer_address_id' => $order->customer_address_id,
            'invoice_no' => $this->generateInvoiceNo(),
            'issue_date' => now()->toDateString(),
            'amount' => $order->total,
            'discount_amount' => $order->discount_amount,
            'discount_forever' => $order->discount_forever,
            'notes' => $notes,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_address' => $order->customer_address,
        ]);

        $order->loadMissing('items.design', 'items.size');

        foreach ($order->items as $item) {
            $pricing = $this->stickerPricing->invoiceItemPricing($item);

            $invoice->items()->create([
                'description' => $this->stickerPricing->stickerDescription($item),
                'quantity' => $pricing['quantity'],
                'unit_price' => $pricing['unit_price'],
                'line_total' => $pricing['line_total'],
            ]);
        }

        if ($order->shipping_region !== null || (float) $order->shipping_fee > 0) {
            $shippingFee = round((float) $order->shipping_fee, 2);

            $invoice->items()->create([
                'description' => $this->shippingService->description($order->shipping_region, $shippingFee),
                'quantity' => 1,
                'unit_price' => $shippingFee,
                'line_total' => $shippingFee,
            ]);
        }

        $this->createDiscountItem($invoice, (float) $order->discount_amount);

        if ($order->user_id && $order->customer_address && $order->customer_phone) {
            $this->syncCustomerAddress($order->user_id, $order->customer_address, $order->customer_phone);
        }

        return $invoice;
    }

    public function updateDiscount(Invoice $invoice, float $requestedAmount, string $duration): float
    {
        $invoice->load(['items', 'order.user', 'user']);

        $discountItems = $invoice->items->filter(fn ($item): bool => $item->isCustomerDiscount());
        $normalItems = $invoice->items->reject(fn ($item): bool => $item->isCustomerDiscount());
        $normalTotal = round($normalItems->sum(fn ($item): float => (float) $item->line_total), 2);
        $order = $invoice->order;
        $orderSubtotal = $order ? (float) $order->subtotal : 0.0;
        if ($order && $orderSubtotal <= 0 && $normalTotal > 0) {
            $orderSubtotal = max(0, $normalTotal - (float) $order->shipping_fee);
        }

        $discountBase = $order && $orderSubtotal > 0 ? $orderSubtotal : $normalTotal;
        $discountAmount = round(min(max(0, $requestedAmount), max(0, $discountBase)), 2);
        $newAmount = round(max(0, $normalTotal - $discountAmount), 2);

        if ($newAmount + 0.01 < (float) $invoice->total_paid) {
            throw ValidationException::withMessages([
                'discount_amount' => 'Diskaun ini akan menjadikan jumlah invoice kurang daripada bayaran yang telah diterima.',
            ]);
        }

        $discountForever = $duration === 'forever' && $discountAmount > 0;
        $customer = $invoice->user ?: $order?->user;
        if ($discountForever && ! $customer) {
            throw ValidationException::withMessages([
                'discount_duration' => 'Diskaun selamanya memerlukan invoice yang dipautkan kepada akaun customer.',
            ]);
        }

        DB::transaction(function () use ($invoice, $discountItems, $discountAmount, $discountForever, $newAmount, $order, $orderSubtotal, $customer): void {
            if ($discountItems->isNotEmpty()) {
                $invoice->items()->whereKey($discountItems->modelKeys())->delete();
            }

            $invoice->update([
                'amount' => $newAmount,
                'discount_amount' => $discountAmount,
                'discount_forever' => $discountForever,
            ]);

            $this->createDiscountItem($invoice, $discountAmount);

            if ($order) {
                $orderTotal = round(max(0, $orderSubtotal + (float) $order->shipping_fee - $discountAmount), 2);
                $order->update([
                    'discount_amount' => $discountAmount,
                    'discount_forever' => $discountForever,
                    'total' => $orderTotal,
                    'balance_due' => max(0, $orderTotal - (float) $invoice->total_paid),
                ]);
            }

            if ($customer) {
                $customer->update($discountForever
                    ? [
                        'discount_amount' => $discountAmount,
                        'discount_forever' => true,
                    ]
                    : [
                        'discount_amount' => 0,
                        'discount_forever' => false,
                    ]);
            }
        });

        return $discountAmount;
    }

    public function syncCustomerAddress(int $userId, string $address, string $phone): void
    {
        $existing = CustomerAddress::query()
            ->where('user_id', $userId)
            ->where('address', $address)
            ->where('no_hp', $phone)
            ->exists();

        if ($existing) {
            return;
        }

        $hasAddresses = CustomerAddress::query()->where('user_id', $userId)->exists();

        CustomerAddress::query()->create([
            'user_id' => $userId,
            'address' => $address,
            'no_hp' => $phone,
            'is_default' => ! $hasAddresses,
        ]);
    }

    public function generateInvoiceNo(): string
    {
        do {
            $invoiceNo = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (Invoice::query()->where('invoice_no', $invoiceNo)->exists());

        return $invoiceNo;
    }

    private function applyPersistentDiscountToOrder(Order $order): void
    {
        if ((float) $order->discount_amount > 0 || ! $order->user?->discount_forever) {
            return;
        }

        $discountAmount = round(min(
            max(0, (float) $order->user->discount_amount),
            max(0, (float) $order->subtotal),
        ), 2);

        if ($discountAmount <= 0) {
            return;
        }

        $order->update([
            'discount_amount' => $discountAmount,
            'discount_forever' => true,
            'total' => round(max(0, (float) $order->subtotal + (float) $order->shipping_fee - $discountAmount), 2),
            'balance_due' => max(0, round((float) $order->subtotal + (float) $order->shipping_fee - $discountAmount - (float) $order->deposit_amount, 2)),
        ]);
    }

    private function createDiscountItem(Invoice $invoice, float $discountAmount): void
    {
        $discountAmount = round(max(0, $discountAmount), 2);
        if ($discountAmount <= 0) {
            return;
        }

        $invoice->items()->create([
            'description' => Invoice::CUSTOMER_DISCOUNT_DESCRIPTION,
            'quantity' => 1,
            'unit_price' => -$discountAmount,
            'line_total' => -$discountAmount,
        ]);
    }
}
