<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminInvoiceEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_edit_invoice_details_and_items(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-EDIT-TEST',
            'issue_date' => '2026-08-01',
            'amount' => 10,
            'customer_name' => 'Pelanggan Lama',
            'customer_phone' => '0100000000',
            'customer_address' => 'Alamat lama',
            'payment_status' => 'unpaid',
        ]);
        $invoice->items()->create([
            'description' => 'Item lama',
            'quantity' => 1,
            'unit_price' => 10,
            'line_total' => 10,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.invoices.edit', $invoice))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Invoices/Edit')
                ->where('invoice.invoice_no', 'INV-EDIT-TEST')
            );

        $response = $this->actingAs($admin)->put(route('admin.invoices.update', $invoice), [
            'invoice_no' => 'INV-EDIT-UPDATED',
            'issue_date' => '2026-08-15',
            'customer_name' => 'Pelanggan Baharu',
            'customer_phone' => '0111111111',
            'customer_address' => 'Alamat baharu',
            'notes' => 'Nota dikemaskini',
            'items' => [
                [
                    'description' => 'Item baharu',
                    'quantity' => 2,
                    'unit_price' => 12.5,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.invoices.show', $invoice));
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'invoice_no' => 'INV-EDIT-UPDATED',
            'amount' => 25,
            'customer_name' => 'Pelanggan Baharu',
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Item baharu',
            'quantity' => 2,
            'unit_price' => 12.50,
            'line_total' => 25.00,
        ]);
    }

    public function test_invoice_show_exposes_signed_public_customer_link(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-PUBLIC-TEST',
            'issue_date' => '2026-08-01',
            'amount' => 10,
            'customer_name' => 'Pelanggan',
            'customer_phone' => '0100000000',
            'customer_address' => 'Alamat pelanggan',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.invoices.show', $invoice))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Invoices/Show')
                ->where('customerInvoiceUrl', fn (string $url): bool => str_contains($url, 'signature='))
                ->where('pdfUrl', fn (string $url): bool => str_contains($url, '/admin/invoices/'.$invoice->id.'/pdf'))
            );

        $publicUrl = URL::temporarySignedRoute('invoices.public', now()->addDay(), ['invoice' => $invoice]);

        $this->get($publicUrl)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/InvoiceShow')
                ->where('invoice.invoice_no', 'INV-PUBLIC-TEST')
            );
    }

    public function test_edit_preserves_invoice_total_when_legacy_unit_price_was_rounded(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-PRECISION-TEST',
            'issue_date' => '2026-08-01',
            'amount' => 136,
            'customer_name' => 'Pelanggan',
            'customer_phone' => '0100000000',
            'customer_address' => 'Alamat pelanggan',
            'payment_status' => 'unpaid',
        ]);
        $invoice->items()->create([
            'description' => 'Sticker : 8 x 4.5cm',
            'quantity' => 500,
            'unit_price' => 0.27,
            'line_total' => 136,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.invoices.edit', $invoice))
            ->assertInertia(fn (Assert $page) => $page
                ->where('invoice.items.0.unit_price', fn ($unitPrice): bool => abs((float) $unitPrice - 0.272) < 0.0001)
            );

        $this->actingAs($admin)->put(route('admin.invoices.update', $invoice), [
            'invoice_no' => $invoice->invoice_no,
            'issue_date' => '2026-08-01',
            'customer_name' => 'Pelanggan',
            'customer_phone' => '0100000000',
            'customer_address' => 'Alamat pelanggan',
            'notes' => null,
            'items' => [[
                'description' => 'Sticker : 8 x 4.5cm',
                'quantity' => 500,
                'unit_price' => 0.272,
            ]],
        ])->assertRedirect(route('admin.invoices.show', $invoice));

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'amount' => 136,
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'quantity' => 500,
            'unit_price' => 0.2720,
            'line_total' => 136,
        ]);
    }
}
