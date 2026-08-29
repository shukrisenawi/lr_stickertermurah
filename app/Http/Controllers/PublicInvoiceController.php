<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Inertia\Inertia;
use Inertia\Response;

class PublicInvoiceController extends Controller
{
    public function show(Invoice $invoice): Response
    {
        $invoice->load(['items', 'order.items.design', 'order.items.project', 'order.items.size']);

        $items = $invoice->items->map(fn ($item): array => [
            'id' => $item->id,
            'description' => $item->description,
            'quantity' => (int) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'line_total' => (float) $item->line_total,
        ]);

        if ($items->isEmpty() && $invoice->order) {
            $items = $invoice->order->items->map(fn ($item): array => [
                'id' => $item->id,
                'description' => collect([
                    $item->design?->name,
                    $item->custom_design_description,
                    $item->size?->name,
                    $item->requested_size ? "Saiz: {$item->requested_size}" : null,
                    $item->quoted_sticker_type ? "Jenis: {$item->quoted_sticker_type}" : null,
                    $item->quoted_qty_per_a3 && $item->quoted_price_per_a3
                        ? "Kiraan: {$item->quoted_qty_per_a3} pcs/A3 @ RM".number_format((float) $item->quoted_price_per_a3, 2).'/A3'
                        : null,
                    $item->cut_type === 'die-cut' ? 'Potong Ikut Bentuk' : 'Potong Standard',
                ])->filter()->implode(' • ') ?: 'Sticker',
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) ($item->line_total ?? $item->subtotal),
            ]);
        }

        $customQuotes = ($invoice->order?->items ?? collect())
            ->filter(fn ($item): bool => (bool) $item->quoted_qty_per_a3 && (bool) $item->quoted_price_per_a3)
            ->map(fn ($item): array => [
                'id' => $item->id,
                'name' => $item->design?->name ?? $item->project?->title ?? $item->custom_design_description ?? 'Sticker',
                'size' => $item->size?->name ?? $item->requested_size ?? 'Saiz custom',
                'quantity' => (int) $item->quantity,
                'sticker_type' => $item->quoted_sticker_type,
                'quoted_qty_per_a3' => (int) $item->quoted_qty_per_a3,
                'quoted_price_per_a3' => (float) $item->quoted_price_per_a3,
            ])
            ->values();

        return Inertia::render('Public/InvoiceShow', [
            'invoice' => [
                'invoice_no' => $invoice->invoice_no,
                'issue_date' => $invoice->issue_date?->format('Y-m-d'),
                'amount' => (float) $invoice->amount,
                'notes' => $invoice->notes,
                'payment_status' => $invoice->payment_status,
                'payment_type' => $invoice->payment_type,
                'paid_at' => $invoice->paid_at?->toISOString(),
                'customer_name' => $invoice->customer_name ?? $invoice->order?->customer_name,
                'customer_phone' => $invoice->customer_phone ?? $invoice->order?->customer_phone,
                'customer_address' => $invoice->customer_address ?? $invoice->order?->customer_address,
                'tracking_no' => $invoice->tracking_no,
                'order' => $invoice->order ? ['tracking_no' => $invoice->order->tracking_no] : null,
                'items' => $items->values(),
                'custom_quotes' => $customQuotes,
            ],
        ]);
    }
}
