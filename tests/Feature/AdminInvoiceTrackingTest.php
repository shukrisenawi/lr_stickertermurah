<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInvoiceTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_tracking_number_on_paid_invoice(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-TRACKING-TEST',
            'issue_date' => now()->toDateString(),
            'amount' => 100,
            'payment_status' => 'paid',
            'tracking_no' => null,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.invoices.tracking.update', $invoice), [
            'tracking_no' => 'JNT123456789',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'tracking_no' => 'JNT123456789',
        ]);
    }
}
