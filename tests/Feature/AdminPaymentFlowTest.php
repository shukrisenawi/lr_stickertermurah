<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_payment_syncs_an_order_that_is_already_partial(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createPartialOrder($customer);
        $invoice = $this->createInvoice($order, $customer);

        $this->actingAs($admin)
            ->post(route('admin.invoices.approve', $invoice), [
                'payment_amount' => 80,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'payment_status' => 'paid',
            'total_paid' => 100,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
            'payment_status' => 'paid',
            'deposit_amount' => 100,
            'balance_due' => 0,
        ]);
    }

    public function test_admin_can_record_payment_without_receipt(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createPartialOrder($customer, 'pending');
        $invoice = $this->createInvoice($order, $customer);
        $invoice->update([
            'payment_status' => 'unpaid',
            'payment_type' => null,
            'payment_method' => null,
            'payment_amount' => null,
            'total_paid' => 0,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.invoices.approve', $invoice), [
                'payment_amount' => 100,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'payment_status' => 'paid',
            'total_paid' => 100,
            'payment_receipt_path' => null,
        ]);
        $this->assertDatabaseHas('invoice_payments', [
            'invoice_id' => $invoice->id,
            'amount' => 100,
            'status' => 'approved',
            'receipt_path' => null,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);
    }

    public function test_first_partial_payment_keeps_order_in_pending_lifecycle(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createPartialOrder($customer, 'pending');
        $invoice = $this->createInvoice($order, $customer);
        $invoice->update([
            'payment_status' => 'submitted',
            'payment_type' => 'deposit',
            'payment_amount' => 20,
            'total_paid' => 0,
        ]);

        $this->actingAs($admin)->post(route('admin.invoices.approve', $invoice), [
            'payment_amount' => 20,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
            'payment_status' => 'partial',
        ]);
    }

    public function test_final_payment_does_not_move_an_order_back_from_production(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createPartialOrder($customer, 'processing');
        $invoice = $this->createInvoice($order, $customer);

        $this->actingAs($admin)->post(route('admin.invoices.approve', $invoice), [
            'payment_amount' => 80,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);
    }

    public function test_admin_can_update_invoice_payment_status_manually(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createPartialOrder($customer);
        $invoice = $this->createInvoice($order, $customer);

        $this->actingAs($admin)
            ->put(route('admin.invoices.status.update', $invoice), [
                'payment_status' => 'paid',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'payment_status' => 'paid',
            'total_paid' => 100,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
            'payment_status' => 'paid',
            'balance_due' => 0,
        ]);
    }

    private function createPartialOrder(User $customer, string $status = 'partial'): Order
    {
        return Order::query()->create([
            'user_id' => $customer->id,
            'order_no' => 'ORD-PAYMENT-'.strtoupper(substr(md5($status), 0, 5)),
            'customer_name' => $customer->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat pembayaran',
            'material' => 'Mirrorcote',
            'status' => $status,
            'subtotal' => 100,
            'total' => 100,
            'deposit_amount' => 20,
            'balance_due' => 80,
            'payment_status' => 'partial',
            'payment_type' => 'deposit',
        ]);
    }

    private function createInvoice(Order $order, User $customer): Invoice
    {
        return Invoice::query()->create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'invoice_no' => 'INV-PAYMENT-'.strtoupper(substr(md5($order->status), 0, 5)),
            'issue_date' => now()->toDateString(),
            'amount' => 100,
            'total_paid' => 20,
            'payment_status' => 'submitted',
            'payment_type' => 'custom',
            'payment_method' => 'transfer',
            'payment_amount' => 80,
        ]);
    }
}
