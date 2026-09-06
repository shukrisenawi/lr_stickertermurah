<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PaymentSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class InvoicePdfService
{
    public function __construct(
        private readonly StickerPricingService $stickerPricing,
    ) {}

    public function download(Invoice $invoice): Response
    {
        $invoice->load([
            'items',
            'order.items.design',
            'order.items.project',
            'order.items.size',
            'order.user',
            'user',
        ]);

        $items = $invoice->items->map(fn ($item): array => [
            'description' => trim((string) $item->description) ?: 'Sticker',
            'quantity' => (int) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'line_total' => (float) $item->line_total,
        ]);

        if ($items->isEmpty() && $invoice->order) {
            $items = $invoice->order->items->map(fn ($item): array => [
                'description' => $this->stickerPricing->stickerDescription($item),
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) ($item->line_total ?? $item->subtotal),
            ]);
        }

        $settings = PaymentSetting::query()->first([
            'company_name',
            'company_address',
            'company_phone',
            'company_logo_path',
            'admin_phone',
            'admin_email',
        ]);
        $brandName = trim((string) ($settings?->company_name ?? '')) ?: 'StickerTermurah';
        $brandPhone = trim((string) ($settings?->company_phone ?? ''))
            ?: (trim((string) ($settings?->admin_phone ?? '')) ?: '011-69409606');
        $brandEmail = trim((string) ($settings?->admin_email ?? '')) ?: 'stickertermurah@gmail.com';
        $paymentStatus = (string) ($invoice->payment_status ?: 'unpaid');
        $trackingNo = $invoice->customerTrackingNo();

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'items' => $items->values(),
            'totalQty' => $items->sum(fn (array $item): int => $item['quantity']),
            'customerName' => $invoice->customer_name ?? $invoice->order?->customer_name ?? $invoice->user?->name ?? '-',
            'customerPhone' => $invoice->customer_phone ?? $invoice->order?->customer_phone ?? '-',
            'customerAddress' => $invoice->customer_address ?? $invoice->order?->customer_address ?? '-',
            'trackingNo' => $trackingNo,
            'paymentStatusLabel' => $this->paymentStatusLabel($paymentStatus),
            'brandName' => $brandName,
            'brandTagline' => $brandName === 'StickerTermurah' ? 'SH Best Creative Design' : null,
            'brandAddress' => $settings?->company_address,
            'brandPhone' => $brandPhone,
            'brandEmail' => $brandEmail,
            'logoDataUri' => $this->logoDataUri($settings?->company_logo_path),
            'issueDate' => $this->formatMalayDate($invoice->issue_date),
            'paidAt' => $invoice->paid_at ? $this->formatMalayDate($invoice->paid_at) : null,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isRemoteEnabled', false)
            ->addInfo([
                'Title' => 'Invoice '.$invoice->invoice_no,
                'Author' => $brandName,
            ]);

        return $pdf->download($this->filename($invoice));
    }

    private function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            'submitted' => 'Menunggu Semakan',
            'rejected' => 'Ditolak',
            'partial' => 'Bayaran Separa',
            'paid' => 'Telah Bayar',
            default => 'Belum Bayar',
        };
    }

    private function formatMalayDate(?\DateTimeInterface $date): string
    {
        if (! $date) {
            return '-';
        }

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Mac',
            4 => 'April',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Julai',
            8 => 'Ogos',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Disember',
        ];

        return $date->format('d').' '.($months[(int) $date->format('n')] ?? $date->format('F')).' '.$date->format('Y');
    }

    private function filename(Invoice $invoice): string
    {
        $invoiceNo = Str::slug((string) $invoice->invoice_no) ?: 'invoice';

        return 'invoice-'.$invoiceNo.'.pdf';
    }

    private function logoDataUri(?string $path): ?string
    {
        $contents = null;

        if ($path) {
            try {
                if (Storage::disk('public')->exists($path)) {
                    $contents = Storage::disk('public')->get($path);
                }
            } catch (\Throwable) {
                $contents = null;
            }
        }

        if (! is_string($contents) || $contents === '') {
            $fallbackPath = public_path('images/logo-baru.png');
            if (is_file($fallbackPath)) {
                $contents = file_get_contents($fallbackPath);
            }
        }

        if (! is_string($contents) || $contents === '') {
            return null;
        }

        if (function_exists('imagecreatefromstring') && function_exists('imagepng')) {
            $image = @imagecreatefromstring($contents);
            if ($image) {
                ob_start();
                imagepng($image);
                $pngContents = ob_get_clean();
                imagedestroy($image);

                if (is_string($pngContents) && $pngContents !== '') {
                    return 'data:image/png;base64,'.base64_encode($pngContents);
                }
            }
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);
        if (! in_array($mime, ['image/gif', 'image/jpeg', 'image/png'], true)) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
