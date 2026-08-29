<?php

namespace App\Services;

use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Str;

class InvoiceService
{
    public function __construct(private readonly ShippingService $shippingService) {}

    public function createForOrder(Order $order, ?string $notes = null): Invoice
    {
        $existingInvoice = $order->invoice()->with('items')->first();
        if ($existingInvoice) {
            return $existingInvoice;
        }

        $invoice = Invoice::query()->create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'customer_address_id' => $order->customer_address_id,
            'invoice_no' => $this->generateInvoiceNo(),
            'issue_date' => now()->toDateString(),
            'amount' => $order->total,
            'notes' => $notes,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_address' => $order->customer_address,
        ]);

        $order->loadMissing('items.design', 'items.size');

        foreach ($order->items as $item) {
            $description = collect([
                $item->design?->name,
                $item->custom_design_description,
                $item->size?->name,
                $item->requested_size ? "Saiz: {$item->requested_size}" : null,
                $item->quoted_sticker_type ? "Jenis: {$item->quoted_sticker_type}" : null,
                $item->quoted_qty_per_a3 && $item->quoted_price_per_a3
                    ? "Kiraan: {$item->quoted_qty_per_a3} pcs/A3 @ RM".number_format((float) $item->quoted_price_per_a3, 2).'/A3'
                    : null,
                $item->cut_type === 'die-cut' ? 'Potong Ikut Bentuk' : 'Potong Standard',
            ])->filter()->implode(' • ');

            $invoice->items()->create([
                'description' => $description ?: 'Sticker',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
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

        if ($order->user_id && $order->customer_address && $order->customer_phone) {
            $this->syncCustomerAddress($order->user_id, $order->customer_address, $order->customer_phone);
        }

        return $invoice;
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
}
