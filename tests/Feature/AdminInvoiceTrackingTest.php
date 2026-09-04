<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Order;
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

    public function test_tracking_number_on_invoice_completes_related_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = Order::query()->create([
            'order_no' => 'ORD-TRACKING-INVOICE',
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat customer',
            'material' => 'Mirrorcote',
            'status' => 'processing',
            'subtotal' => 100,
            'total' => 107,
            'shipping_fee' => 7,
        ]);
        $invoice = Invoice::query()->create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'invoice_no' => 'INV-TRACKING-ORDER-TEST',
            'issue_date' => now()->toDateString(),
            'amount' => 107,
            'payment_status' => 'paid',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.invoices.tracking.update', $invoice), [
                'tracking_no' => ' JNT987654321 ',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'No. tracking J&T berjaya dikemaskini.');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'tracking_no' => 'JNT987654321',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'tracking_no' => 'JNT987654321',
            'status' => 'completed',
        ]);
    }
}
