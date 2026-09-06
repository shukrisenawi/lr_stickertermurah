<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PriceSetting;
use App\Models\StickerDesign;
use App\Models\StickerSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInvoiceDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_apply_a_one_time_invoice_discount(): void
    {
        [$admin, $member, $invoice] = $this->invoiceSetup();

        $this->actingAs($admin)
            ->from(route('admin.invoices.show', $invoice))
            ->put(route('admin.invoices.discount.update', $invoice), [
                'discount_amount' => 10,
                'discount_duration' => 'once',
            ])
            ->assertRedirect(route('admin.invoices.show', $invoice));

        $invoice->refresh();
        $member->refresh();

        $this->assertSame('117.00', (string) $invoice->amount);
        $this->assertSame('10.00', (string) $invoice->discount_amount);
        $this->assertFalse($invoice->discount_forever);
        $this->assertFalse($member->discount_forever);
        $this->assertSame('117.00', (string) $invoice->order->refresh()->total);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Diskaun pelanggan',
            'line_total' => -10,
        ]);
    }

    public function test_forever_discount_is_applied_to_a_customer_repeat_order(): void
    {
        [$admin, $member, $invoice] = $this->invoiceSetup();

        $this->actingAs($admin)
            ->put(route('admin.invoices.discount.update', $invoice), [
                'discount_amount' => 10,
                'discount_duration' => 'forever',
            ])
            ->assertRedirect();

        $category = Category::query()->create(['name' => 'Diskaun Test', 'slug' => 'diskaun-test']);
        $design = StickerDesign::query()->create([
            'category_id' => $category->id,
            'name' => 'Design Diskaun Test',
            'is_active' => true,
        ]);
        $size = StickerSize::query()->create([
            'name' => 'Saiz Diskaun Test',
            'width_cm' => 5,
            'height_cm' => 5,
            'price' => 0,
            'qty_per_a3' => 10,
            'is_active' => true,
        ]);
        PriceSetting::query()->create([
            'sticker_type' => 'Mirrorcote',
            'qty_from' => 1,
            'qty_to' => null,
            'price_per_a3' => 12,
            'is_active' => true,
        ]);

        $this->actingAs($member)
            ->post(route('orders.store'), [
                'design_id' => $design->id,
                'size_id' => $size->id,
                'quantity' => 100,
                'cut_type' => 'standard',
                'shipping_region' => 'peninsular',
                'customer_name' => $member->name,
                'customer_phone' => '0123456789',
                'customer_address' => 'Alamat repeat',
                'repeat_from_order_id' => $invoice->order_id,
            ])
            ->assertRedirect();

        $newOrder = Order::query()->latest('id')->firstOrFail();
        $newInvoice = $newOrder->invoice()->with('items')->firstOrFail();

        $this->assertSame('10.00', (string) $member->refresh()->discount_amount);
        $this->assertTrue($member->discount_forever);
        $this->assertSame('117.00', (string) $newOrder->total);
        $this->assertSame('10.00', (string) $newOrder->discount_amount);
        $this->assertTrue($newOrder->discount_forever);
        $this->assertSame('117.00', (string) $newInvoice->amount);
        $this->assertTrue($newInvoice->items->contains(fn ($item): bool => $item->isCustomerDiscount()));
    }

    public function test_admin_can_create_a_manual_invoice_with_a_forever_discount(): void
    {
        [$admin, $member] = $this->invoiceSetup();

        $this->actingAs($admin)
            ->post(route('admin.invoices.manual.store'), [
                'user_id' => $member->id,
                'customer_name' => $member->name,
                'customer_phone' => '0123456789',
                'customer_address' => 'Alamat manual',
                'invoice_no' => 'INV-MANUAL-DISCOUNT-TEST',
                'issue_date' => '2026-09-06',
                'amount' => 110,
                'discount_amount' => 10,
                'discount_duration' => 'forever',
                'items' => [[
                    'description' => 'Sticker manual',
                    'quantity' => 1,
                    'unit_price' => 120,
                ]],
            ])
            ->assertRedirect();

        $invoice = Invoice::query()->where('invoice_no', 'INV-MANUAL-DISCOUNT-TEST')->firstOrFail();

        $this->assertSame('110.00', (string) $invoice->amount);
        $this->assertSame('10.00', (string) $invoice->discount_amount);
        $this->assertTrue($invoice->discount_forever);
        $this->assertSame('10.00', (string) $member->refresh()->discount_amount);
        $this->assertTrue($member->discount_forever);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Diskaun pelanggan',
            'line_total' => -10,
        ]);
    }

    private function invoiceSetup(): array
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $member = User::factory()->create(['is_admin' => false]);
        $order = Order::query()->create([
            'user_id' => $member->id,
            'order_no' => 'ORD-DISCOUNT-TEST',
            'customer_name' => $member->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat asal',
            'material' => 'Mirrorcote',
            'status' => 'pending',
            'subtotal' => 120,
            'total' => 127,
            'shipping_region' => 'peninsular',
            'shipping_fee' => 7,
            'pricing_status' => 'auto_priced',
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'quantity' => 100,
            'unit_price' => 1.2,
            'line_total' => 120,
            'cut_type' => 'standard',
        ]);
        $invoice = Invoice::query()->create([
            'order_id' => $order->id,
            'user_id' => $member->id,
            'invoice_no' => 'INV-DISCOUNT-TEST',
            'issue_date' => '2026-09-06',
            'amount' => 127,
            'customer_name' => $member->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat asal',
        ]);
        $invoice->items()->createMany([
            [
                'description' => 'Sticker : Saiz Test',
                'quantity' => 10,
                'unit_price' => 12,
                'line_total' => 120,
            ],
            [
                'description' => 'Pos - Semenanjung Malaysia',
                'quantity' => 1,
                'unit_price' => 7,
                'line_total' => 7,
            ],
        ]);

        return [$admin, $member, $invoice];
    }
}
