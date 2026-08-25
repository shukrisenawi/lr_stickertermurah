<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function approve(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->payment_status === 'paid', 403, 'Invoice telah dibayar penuh.');

        $validated = $request->validate([
            'payment_note' => ['nullable', 'string', 'max:1000'],
            'payment_amount' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $invoiceAmount = (float) $invoice->amount;
        $paymentAmount = $validated['payment_amount'] !== null
            ? round((float) $validated['payment_amount'], 2)
            : (float) ($invoice->payment_amount ?? $invoiceAmount);

        $totalPaid = round((float) $invoice->total_paid + $paymentAmount, 2);
        $isFullyPaid = $totalPaid >= $invoiceAmount - 0.01;

        $invoice->update([
            'payment_status' => $isFullyPaid ? 'paid' : 'partial',
            'payment_amount' => $paymentAmount,
            'total_paid' => min($totalPaid, $invoiceAmount),
            'paid_at' => $isFullyPaid ? now() : null,
            'approved_by' => Auth::id(),
            'payment_note' => $validated['payment_note'] ?? $invoice->payment_note,
        ]);

        InvoicePayment::query()->create([
            'invoice_id' => $invoice->id,
            'amount' => $paymentAmount,
            'method' => $invoice->payment_method,
            'type' => $invoice->payment_type,
            'status' => 'approved',
            'receipt_path' => $invoice->payment_receipt_path,
            'note' => $validated['payment_note'] ?? null,
            'approved_by' => Auth::id(),
            'paid_at' => now(),
        ]);

        // Sync medan bayaran setiap kali invoice berubah, termasuk bayaran kedua selepas order menjadi partial.
        if ($invoice->order_id) {
            $order = Order::query()->find($invoice->order_id);
            if ($order) {
                // Bayaran separa berada dalam payment_status; jangan simpan partial dalam enum status order.
                $orderStatus = $order->status === 'partial' ? 'pending' : $order->status;
                if ($isFullyPaid && in_array($orderStatus, ['pending', 'paid'], true)) {
                    $orderStatus = 'paid';
                }

                $order->update([
                    'status' => $orderStatus,
                    'payment_status' => $isFullyPaid ? 'paid' : 'partial',
                    'payment_type' => $invoice->payment_type,
                    'deposit_amount' => (float) $invoice->total_paid,
                    'balance_due' => round($invoiceAmount - (float) $invoice->total_paid, 2),
                ]);
            }
        }

        $message = $isFullyPaid
            ? 'Pembayaran invoice diluluskan. Invoice telah dibayar penuh.'
            : 'Pembayaran diluluskan. Baki belum dijelaskan: RM '.number_format(round($invoiceAmount - (float) $invoice->total_paid, 2), 2).'.';

        return back()->with('success', $message);
    }

    public function reject(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->payment_status === 'paid', 403, 'Invoice telah dibayar.');

        $validated = $request->validate([
            'payment_note' => ['required', 'string', 'max:1000'],
        ]);

        $invoice->update([
            'payment_status' => 'rejected',
            'approved_by' => Auth::id(),
            'payment_note' => $validated['payment_note'],
        ]);

        return back()->with('success', 'Pembayaran invoice ditolak. Pelanggan akan dimaklumkan.');
    }

    public function reset(Invoice $invoice): RedirectResponse
    {
        $invoice->update([
            'payment_status' => 'unpaid',
            'paid_at' => null,
            'payment_submitted_at' => null,
            'approved_by' => null,
            'payment_note' => null,
            'total_paid' => 0,
        ]);

        return back()->with('success', 'Status pembayaran direset.');
    }
}
