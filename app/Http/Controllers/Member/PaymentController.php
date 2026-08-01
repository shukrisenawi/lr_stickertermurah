<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function uploadReceipt(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);

        abort_if($invoice->payment_status === 'paid', 403, 'Invoice ini telah dibayar.');

        $validated = $request->validate([
            'payment_receipt' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'payment_type' => ['required', 'in:deposit,full'],
            'payment_method' => ['nullable', 'string', 'max:255'],
        ]);

        $path = $request->file('payment_receipt')->store('payment-receipts', 'public');

        // Padam resit lama jika ada
        if ($invoice->payment_receipt_path) {
            Storage::disk('public')->delete($invoice->payment_receipt_path);
        }

        $invoice->update([
            'payment_receipt_path' => $path,
            'payment_type' => $validated['payment_type'],
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