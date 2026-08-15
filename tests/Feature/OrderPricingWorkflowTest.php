<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\PriceSetting;
use App\Models\StickerDesign;
use App\Models\StickerSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderPricingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_order_form_with_selected_design(): void
    {
        $category = Category::query()->create(['name' => 'Test', 'slug' => 'test']);
        $design = StickerDesign::query()->create([
            'category_id' => $category->id,
            'name' => 'Design Test',
            'is_active' => true,
        ]);

        $this->get(route('orders.create', ['design_id' => $design->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/OrderForm')
                ->where('initialDesign.id', $design->id)
                ->where('initialDesign.name', 'Design Test')
            );
    }

    public function test_auto_priced_order_can_be_invoiced_without_customer_approval(): void
    {
        [$member, $design, $size] = $this->productSetup();
        $size->update(['qty_per_a3' => 10]);
        PriceSetting::query()->create([
            'sticker_type' => 'Mirrorcote',
            'qty_from' => 1,
            'qty_to' => null,
            'price_per_a3' => 12,
            'is_active' => true,
        ]);

        $this->actingAs($member)->post(route('orders.store'), $this->orderData($design, $size, 100))->assertRedirect();
        $order = Order::query()->latest('id')->firstOrFail();

        $this->assertSame('auto_priced', $order->pricing_status);
        $this->assertSame('120.00', (string) $order->total);

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->post(route('admin.invoices.store', $order))->assertRedirect();

        $this->assertDatabaseHas('invoices', ['order_id' => $order->id, 'amount' => 120]);
    }

    public function test_custom_price_requires_customer_approval_before_invoice(): void
    {
        [$member, $design, $size] = $this->productSetup();

        $this->actingAs($member)->post(route('orders.store'), $this->orderData($design, $size, 100))->assertRedirect();
        $order = Order::query()->latest('id')->firstOrFail();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.invoices.store', $order))->assertRedirect();
        $this->assertDatabaseMissing('invoices', ['order_id' => $order->id]);

        $this->actingAs($admin)->post(route('admin.orders.quote', $order), [
            'amount' => 88,
            'price_note' => 'Termasuk caj custom.',
        ])->assertRedirect();

        $order->refresh();
        $this->assertSame('awaiting_customer_approval', $order->pricing_status);

        $this->actingAs($admin)->post(route('admin.invoices.store', $order))->assertRedirect();
        $this->assertDatabaseMissing('invoices', ['order_id' => $order->id]);

        $this->actingAs($member)->post(route('member.orders.approve-price', $order))->assertRedirect();
        $this->assertSame('approved', $order->refresh()->pricing_status);

        $this->actingAs($admin)->post(route('admin.invoices.store', $order))->assertRedirect();
        $this->assertDatabaseHas('invoices', ['order_id' => $order->id, 'amount' => 88]);
    }

    public function test_custom_size_order_can_be_submitted_without_sticker_size(): void
    {
        [$member, $design] = $this->productSetup();

        $this->actingAs($member)->post(route('orders.store'), [
            'design_id' => $design->id,
            'size_id' => null,
            'requested_size' => '3x10',
            'quantity' => 100,
            'cut_type' => 'standard',
            'customer_name' => 'Customer Custom Size',
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Custom Size',
        ])->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();
        $item = $order->items()->firstOrFail();

        $this->assertSame('pending_admin', $order->pricing_status);
        $this->assertNull($item->sticker_size_id);
        $this->assertSame('3x10', $item->requested_size);
    }

    private function productSetup(): array
    {
        $member = User::factory()->create(['is_admin' => false]);
        $category = Category::query()->create(['name' => 'Test', 'slug' => 'test']);
        $design = StickerDesign::query()->create(['category_id' => $category->id, 'name' => 'Design Test', 'is_active' => true]);
        $size = StickerSize::query()->create([
            'name' => 'Saiz Test',
            'width_cm' => 5,
            'height_cm' => 5,
            'price' => 0,
            'qty_per_a3' => null,
            'is_active' => true,
        ]);

        return [$member, $design, $size];
    }

    private function orderData(StickerDesign $design, StickerSize $size, int $quantity): array
    {
        return [
            'design_id' => $design->id,
            'size_id' => $size->id,
            'quantity' => $quantity,
            'cut_type' => 'standard',
            'customer_name' => 'Customer Test',
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Test',
        ];
    }
}
