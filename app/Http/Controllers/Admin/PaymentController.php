<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function approve(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->payment_status === 'paid', 403, 'Invoice telah dibayar.');

        $validated = $request->validate([
            'payment_note' => ['nullable', 'string', 'max:1000'],
            'payment_amount' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $paymentAmount = $validated['payment_amount'] !== null
            ? round((float) $validated['payment_amount'], 2)
            : (float) ($invoice->payment_amount ?? $invoice->amount);

        $invoice->update([
            'payment_status' => 'paid',
            'payment_amount' => $paymentAmount,
            'paid_at' => now(),
            'approved_by' => Auth::id(),
            'payment_note' => $validated['payment_note'] ?? $invoice->payment_note,
        ]);

        // Sync order status kepada 'paid' jika invoice ada order
        if ($invoice->order_id) {
            $order = Order::query()->find($invoice->order_id);
            if ($order && $order->status === 'pending') {
                $order->update([
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'payment_type' => $invoice->payment_type,
                ]);
            }
        }

        return back()->with('success', 'Pembayaran invoice diluluskan.');
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
        ]);

        return back()->with('success', 'Status pembayaran direset.');
    }
}