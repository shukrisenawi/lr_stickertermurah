<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class InvoicePdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_public_invoice_pdf_can_be_downloaded(): void
    {
        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-PDF-TEST',
            'issue_date' => '2026-09-06',
            'amount' => 25,
            'customer_name' => 'Pelanggan PDF',
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat pelanggan',
        ]);
        $invoice->items()->create([
            'description' => 'Sticker : 8 x 4.5cm',
            'quantity' => 2,
            'unit_price' => 12.5,
            'line_total' => 25,
        ]);

        $response = $this->get(URL::temporarySignedRoute(
            'invoices.public.pdf',
            now()->addDay(),
            ['invoice' => $invoice],
        ));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('invoice-inv-pdf-test.pdf', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_member_can_download_owned_invoice_but_not_another_members_invoice(): void
    {
        $owner = User::factory()->create(['is_admin' => false]);
        $otherMember = User::factory()->create(['is_admin' => false]);
        $invoice = Invoice::query()->create([
            'user_id' => $owner->id,
            'invoice_no' => 'INV-MEMBER-PDF-TEST',
            'issue_date' => '2026-09-06',
            'amount' => 10,
            'customer_name' => $owner->name,
        ]);

        $this->actingAs($owner)
            ->get(route('member.invoices.download', $invoice))
            ->assertOk();

        $this->actingAs($otherMember)
            ->get(route('member.invoices.download', $invoice))
            ->assertForbidden();
    }

    public function test_admin_can_download_any_invoice_pdf(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-ADMIN-PDF-TEST',
            'issue_date' => '2026-09-06',
            'amount' => 10,
            'customer_name' => 'Pelanggan Admin',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.invoices.download', $invoice))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_public_invoice_pdf_requires_a_valid_signature(): void
    {
        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-UNSIGNED-PDF-TEST',
            'issue_date' => '2026-09-06',
            'amount' => 10,
        ]);

        $this->get(route('invoices.public.pdf', $invoice))
            ->assertForbidden();
    }
}
