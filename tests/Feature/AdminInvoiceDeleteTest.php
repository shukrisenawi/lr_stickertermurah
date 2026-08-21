<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminInvoiceDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_invoice_and_related_records(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-DELETE-TEST',
            'issue_date' => '2026-08-01',
            'amount' => 25,
            'payment_status' => 'paid',
            'payment_receipt_path' => 'payment-receipts/current.png',
        ]);
        $invoice->items()->create([
            'description' => 'Item invoice',
            'quantity' => 1,
            'unit_price' => 25,
            'line_total' => 25,
        ]);
        $invoice->payments()->create([
            'amount' => 25,
            'receipt_path' => 'payment-receipts/approved.png',
            'status' => 'approved',
        ]);
        Storage::disk('public')->put('payment-receipts/current.png', 'receipt');
        Storage::disk('public')->put('payment-receipts/approved.png', 'receipt');

        $response = $this->actingAs($admin)->delete(route('admin.invoices.destroy', $invoice));

        $response->assertRedirect(route('admin.invoices.index'));
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('invoice_items', ['invoice_id' => $invoice->id]);
        $this->assertDatabaseMissing('invoice_payments', ['invoice_id' => $invoice->id]);
        $this->assertFalse(Storage::disk('public')->exists('payment-receipts/current.png'));
        $this->assertFalse(Storage::disk('public')->exists('payment-receipts/approved.png'));
    }
}
