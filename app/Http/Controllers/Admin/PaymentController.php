<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Support\CustomerNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
                    $orderStatus = 'processing';
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
        CustomerNotifier::forInvoice(
            $invoice,
            'Status bayaran dikemaskini',
            $message,
            route('member.invoices.show', $invoice),
            'payment',
        );

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
        CustomerNotifier::forInvoice(
            $invoice,
            'Bayaran perlu dihantar semula',
            "Bayaran untuk invoice {$invoice->invoice_no} ditolak. Sila semak sebab dan hantar semula.",
            route('member.invoices.show', ['invoice' => $invoice, 'pay' => 1]),
            'payment',
        );

        return back()->with('success', 'Pembayaran invoice ditolak. Pelanggan akan dimaklumkan.');
    }

    public function updateStatus(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'payment_status' => ['required', Rule::in(['unpaid', 'submitted', 'partial', 'paid', 'rejected'])],
        ]);

        $paymentStatus = $validated['payment_status'];
        $invoiceAmount = round((float) $invoice->amount, 2);
        $totalPaid = round((float) $invoice->total_paid, 2);

        if ($paymentStatus === 'paid') {
            $totalPaid = $invoiceAmount;
        } elseif ($paymentStatus === 'unpaid') {
            $totalPaid = 0;
        } elseif ($paymentStatus === 'partial') {
            $totalPaid = min(
                max($totalPaid, (float) ($invoice->payment_amount ?? 0)),
                max(0, $invoiceAmount - 0.01),
            );
        }

        $invoice->update([
            'payment_status' => $paymentStatus,
            'total_paid' => $totalPaid,
            'payment_amount' => $paymentStatus === 'paid' ? $invoiceAmount : $invoice->payment_amount,
            'paid_at' => $paymentStatus === 'paid' ? now() : null,
            'payment_submitted_at' => $paymentStatus === 'submitted'
                ? ($invoice->payment_submitted_at ?? now())
                : null,
            'approved_by' => in_array($paymentStatus, ['paid', 'partial', 'rejected'], true)
                ? Auth::id()
                : ($paymentStatus === 'unpaid' ? null : $invoice->approved_by),
        ]);

        $invoice->refresh();
        $this->syncOrderPayment($invoice);

        $statusLabels = [
            'unpaid' => 'Belum Bayar',
            'submitted' => 'Menunggu Semakan',
            'partial' => 'Bayaran Separa',
            'paid' => 'Telah Bayar',
            'rejected' => 'Ditolak',
        ];
        $statusLabel = $statusLabels[$paymentStatus];
        $message = "Status bayaran invoice {$invoice->invoice_no} ditetapkan sebagai {$statusLabel}.";

        CustomerNotifier::forInvoice(
            $invoice,
            'Status bayaran dikemaskini',
            $message,
            route('member.invoices.show', $invoice),
            'payment',
        );

        return back()->with('success', $message);
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
        CustomerNotifier::forInvoice(
            $invoice,
            'Status bayaran direset',
            "Status bayaran invoice {$invoice->invoice_no} telah direset oleh admin.",
            route('member.invoices.show', $invoice),
            'payment',
        );

        return back()->with('success', 'Status pembayaran direset.');
    }

    private function syncOrderPayment(Invoice $invoice): void
    {
        if (! $invoice->order_id) {
            return;
        }

        $order = Order::query()->find($invoice->order_id);
        if (! $order) {
            return;
        }

        $orderStatus = $order->status;
        if ($invoice->payment_status === 'paid') {
            $orderStatus = $orderStatus === 'partial' ? 'pending' : $orderStatus;
            if (in_array($orderStatus, ['pending', 'paid'], true)) {
                $orderStatus = 'processing';
            }
        }

        $order->update([
            'status' => $orderStatus,
            'payment_status' => match ($invoice->payment_status) {
                'paid' => 'paid',
                'partial' => 'partial',
                default => 'pending',
            },
            'payment_type' => $invoice->payment_type,
            'deposit_amount' => (float) $invoice->total_paid,
            'balance_due' => round(max(0, (float) $invoice->amount - (float) $invoice->total_paid), 2),
        ]);
    }
}
