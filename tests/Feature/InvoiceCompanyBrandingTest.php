<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\PaymentSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InvoiceCompanyBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_invoice_uses_company_branding_from_payment_settings(): void
    {
        PaymentSetting::query()->create([
            'company_name' => 'Kedai Sticker Maju',
            'company_address' => "No. 12, Jalan Maju\n43000 Kajang, Selangor",
            'company_phone' => '012-3456789',
            'company_logo_path' => 'company/invoice-logo.webp',
            'admin_phone' => '011-69409606',
            'admin_email' => 'admin@example.com',
        ]);
        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-BRANDING-TEST',
            'issue_date' => now()->toDateString(),
            'amount' => 120,
            'customer_name' => 'Pelanggan Test',
            'customer_phone' => '019-1111111',
            'customer_address' => 'Alamat pelanggan',
        ]);

        $this->get(URL::temporarySignedRoute('invoices.public', now()->addDay(), ['invoice' => $invoice]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('app.company_name', 'Kedai Sticker Maju')
                ->where('app.company_address', "No. 12, Jalan Maju\n43000 Kajang, Selangor")
                ->where('app.company_phone', '012-3456789')
                ->where('app.company_logo_url', asset('storage/company/invoice-logo.webp'))
                ->where('pdfUrl', fn (string $url): bool => str_contains($url, 'signature='))
            );
    }
}
