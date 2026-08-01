<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function uploadReceipt(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);

        abort_if($invoice->payment_status === 'paid', 403, 'Invoice ini telah dibayar penuh.');

        $paymentSettings = PaymentSetting::query()->first();
        $minDeposit = (float) ($paymentSettings?->deposit_amount ?? 20);
        $invoiceAmount = (float) $invoice->amount;
        $totalPaid = (float) $invoice->total_paid;
        $balanceDue = round(max(0, $invoiceAmount - $totalPaid), 2);

        $validated = $request->validate([
            'payment_receipt' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'payment_type' => ['required', 'in:deposit,full'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(['bank in', 'transfer', 'qr'])],
        ]);

        $paymentAmount = (float) $validated['payment_amount'];

        if ($validated['payment_type'] === 'full') {
            if (abs($paymentAmount - $balanceDue) > 0.01) {
                throw ValidationException::withMessages([
                    'payment_amount' => 'Bayaran penuh mesti sama dengan baki invoice (RM '.number_format($balanceDue, 2).').',
                ]);
            }
        } else {
            if ($balanceDue <= $minDeposit) {
                throw ValidationException::withMessages([
                    'payment_type' => 'Baki invoice (RM '.number_format($balanceDue, 2).') tidak melebihi deposit minimum (RM '.number_format($minDeposit, 2).'). Sila buat bayaran penuh.',
                ]);
            }
            if ($paymentAmount < $minDeposit) {
                throw ValidationException::withMessages([
                    'payment_amount' => 'Jumlah deposit tidak boleh kurang daripada RM '.number_format($minDeposit, 2).'.',
                ]);
            }
            if ($paymentAmount >= $balanceDue) {
                throw ValidationException::withMessages([
                    'payment_amount' => 'Jumlah bayaran mesti kurang daripada baki invoice (RM '.number_format($balanceDue, 2).').',
                ]);
            }
        }

        $path = $request->file('payment_receipt')->store('payment-receipts', 'public');

        // Padam resit lama jika ada
        if ($invoice->payment_receipt_path) {
            Storage::disk('public')->delete($invoice->payment_receipt_path);
        }

        $invoice->update([
            'payment_receipt_path' => $path,
            'payment_type' => $validated['payment_type'],
            'payment_amount' => $paymentAmount,
            'payment_method' => $validated['payment_method'] ?? null,
            'payment_status' => 'submitted',
            'payment_submitted_at' => now(),
            'payment_note' => null,
        ]);

        return back()->with('success', 'Resit bayaran berjaya dihantar. Menunggu pengesahan admin.');
    }

    public function cancelSubmission(Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);

        abort_if($invoice->payment_status !== 'submitted', 403);

        if ($invoice->payment_receipt_path) {
            Storage::disk('public')->delete($invoice->payment_receipt_path);
        }

        $invoice->update([
            'payment_receipt_path' => null,
            'payment_status' => 'unpaid',
            'payment_submitted_at' => null,
            'payment_type' => null,
            'payment_amount' => null,
            'payment_method' => null,
        ]);

        return back()->with('success', 'Penghantaran resit dibatalkan. Anda boleh hantar resit semula.');
    }

    private function authorizeInvoice(Invoice $invoice): void
    {
        $userId = Auth::id();
        $owns = $invoice->user_id === $userId || $invoice->order?->user_id === $userId;
        abort_unless($owns, 403);
    }
}