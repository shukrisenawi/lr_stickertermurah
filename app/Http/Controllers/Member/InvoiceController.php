<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentSetting;
use App\Services\InvoicePdfService;
use App\Services\StickerPricingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class InvoiceController extends Controller
{
    public function index(): Response
    {
        $invoices = Invoice::query()
            ->where(function ($query) {
                $query->where('user_id', Auth::id())
                    ->orWhereHas('order', function ($q) {
                        $q->where('user_id', Auth::id());
                    });
            })
            ->with(['items', 'order'])
            ->latest()
            ->paginate(10);
        $invoices->through(function (Invoice $invoice): Invoice {
            $trackingNo = $invoice->customerTrackingNo();
            $invoice->setAttribute('tracking_no', $trackingNo);
            $invoice->order?->setAttribute('tracking_no', $trackingNo);

            return $invoice;
        });

        return Inertia::render('Member/Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    public function show(Invoice $invoice, StickerPricingService $stickerPricing): Response
    {
        $invoice->load(['items', 'order.items.design', 'order.items.size', 'approver', 'payments.approver']);

        abort_if($invoice->user_id !== Auth::id() && $invoice->order?->user_id !== Auth::id(), 403);

        $invoice->order?->items->each(function ($item) use ($stickerPricing): void {
            $item->setAttribute('has_design', $stickerPricing->hasExistingDesign($item));
        });

        $paymentSettings = PaymentSetting::query()->first();
        if ($paymentSettings && $paymentSettings->qr_image_path) {
            $paymentSettings->qr_image_url = Storage::disk('public')->url($paymentSettings->qr_image_path);
        }

        $receiptUrl = $invoice->payment_receipt_path
            ? Storage::disk('public')->url($invoice->payment_receipt_path)
            : null;

        $paymentHistory = $invoice->payments
            ->map(fn ($payment) => [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'method' => $payment->method,
                'type' => $payment->type,
                'status' => $payment->status,
                'note' => $payment->note,
                'approved_by' => $payment->approver?->name,
                'paid_at' => $payment->paid_at?->toIso8601String(),
            ])
            ->values()
            ->all();
        $trackingNo = $invoice->customerTrackingNo();
        $invoice->setAttribute('tracking_no', $trackingNo);
        $invoice->order?->setAttribute('tracking_no', $trackingNo);

        return Inertia::render('Member/Invoices/Show', [
            'invoice' => $invoice,
            'paymentSettings' => $paymentSettings,
            'receiptUrl' => $receiptUrl,
            'totalPaid' => (float) $invoice->total_paid,
            'balanceDue' => $invoice->balanceDue(),
            'paymentHistory' => $paymentHistory,
            'minimumA3SheetsWithoutDesign' => $stickerPricing->minimumA3SheetsWithoutDesign(),
        ]);
    }

    public function download(Invoice $invoice, InvoicePdfService $invoicePdf): HttpResponse
    {
        $invoice->loadMissing('order');

        abort_if($invoice->user_id !== Auth::id() && $invoice->order?->user_id !== Auth::id(), 403);

        return $invoicePdf->download($invoice);
    }
}
