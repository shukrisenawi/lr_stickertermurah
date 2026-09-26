<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminInvoiceShippingCostTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_default_shipping_cost_from_payment_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.payment-settings.update'), [
                'bank_name' => 'Bank Islam',
                'bank_account_no' => '123124',
                'bank_account_name' => 'SH BEST CREATIVE DESIGN',
                'admin_phone' => '011-69409606',
                'admin_email' => 'admin@example.com',
                'deposit_amount' => 20,
                'shipping_cost' => 8.25,
            ])
            ->assertRedirect(route('admin.payment-settings.index'));

        $this->assertDatabaseHas('payment_settings', ['shipping_cost' => 8.25]);
    }

    public function test_manual_invoice_uses_configured_shipping_cost_and_allows_admin_override(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        PaymentSetting::query()->create(['shipping_cost' => 8.25]);

        $this->actingAs($admin)
            ->get(route('admin.invoices.manual.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Invoices/ManualCreate')
                ->where('shippingCost', 8.25)
            );

        $this->actingAs($admin)
            ->post(route('admin.invoices.manual.store'), [
                'customer_name' => 'Pelanggan Pos',
                'customer_phone' => '0123456789',
                'customer_address' => 'Alamat Pos',
                'issue_date' => now()->toDateString(),
                'amount' => 100,
                'shipping_cost' => 12.34,
                'items' => [[
                    'description' => 'Sticker',
                    'quantity' => 1,
                    'unit_price' => 100,
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'customer_name' => 'Pelanggan Pos',
            'shipping_cost' => 12.34,
        ]);
    }

    public function test_order_invoice_uses_current_default_shipping_cost(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        PaymentSetting::query()->create(['shipping_cost' => 8.25]);
        $order = Order::query()->create([
            'order_no' => 'ORD-SHIPPING-COST-TEST',
            'customer_name' => 'Pelanggan Order',
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Order',
            'subtotal' => 100,
            'total' => 107,
            'pricing_status' => 'auto_priced',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.invoices.store-from-menu'), ['order_id' => $order->id])
            ->assertRedirect();

        $this->assertSame('8.25', (string) $order->invoice()->firstOrFail()->shipping_cost);
    }

    public function test_admin_can_edit_shipping_cost_without_changing_customer_total(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-SHIPPING-EDIT',
            'issue_date' => now()->toDateString(),
            'amount' => 100,
            'shipping_cost' => 5.60,
            'customer_name' => 'Pelanggan Edit Pos',
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Edit Pos',
        ]);
        $invoice->items()->create([
            'description' => 'Sticker',
            'quantity' => 1,
            'unit_price' => 100,
            'line_total' => 100,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.invoices.update', $invoice), [
                'invoice_no' => $invoice->invoice_no,
                'issue_date' => $invoice->issue_date->toDateString(),
                'shipping_cost' => 3.75,
                'customer_name' => $invoice->customer_name,
                'customer_phone' => $invoice->customer_phone,
                'customer_address' => $invoice->customer_address,
                'items' => [[
                    'description' => 'Sticker',
                    'quantity' => 1,
                    'unit_price' => 100,
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'amount' => 100,
            'shipping_cost' => 3.75,
        ]);
    }

    public function test_member_invoice_response_does_not_expose_actual_shipping_cost(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $invoice = Invoice::query()->create([
            'user_id' => $member->id,
            'invoice_no' => 'INV-SHIPPING-PRIVATE',
            'issue_date' => now()->toDateString(),
            'amount' => 100,
            'shipping_cost' => 12.34,
            'customer_name' => $member->name,
        ]);

        $this->actingAs($member)
            ->get(route('member.invoices.show', $invoice))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Member/Invoices/Show')
                ->missing('invoice.shipping_cost')
            );
    }
}
