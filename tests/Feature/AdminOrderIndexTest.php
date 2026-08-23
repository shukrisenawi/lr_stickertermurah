<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PriceSetting;
use App\Models\StickerDesign;
use App\Models\StickerSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOrderIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_order_index_has_only_pending_and_completed_groups(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $this->createOrder($customer, 'ORD-PENDING', 'pending');
        $this->createOrder($customer, 'ORD-PROCESSING', 'processing');
        $completedOrder = $this->createOrder($customer, 'ORD-COMPLETED', 'completed');

        $this->actingAs($admin)
            ->get(route('admin.orders.index', ['status' => 'pending']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.status', 'pending')
                ->has('orders.data', 2)
            );

        $this->actingAs($admin)
            ->get(route('admin.orders.index', ['status' => 'completed']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.status', 'completed')
                ->has('orders.data', 1)
                ->where('orders.data.0.order_no', $completedOrder->order_no)
            );
    }

    public function test_admin_can_delete_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder($customer, 'ORD-DELETE', 'pending');

        $response = $this->actingAs($admin)
            ->delete(route('admin.orders.destroy', $order));

        $response->assertRedirect(route('admin.orders.index'))
            ->assertSessionHas('success', 'Order berjaya dipadam.');
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_admin_order_form_lists_customers(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin)
            ->get(route('admin.orders.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/OrderForm')
                ->where('adminMode', true)
                ->where('customers.0.id', $customer->id)
            );
    }

    public function test_admin_can_create_order_for_selected_customer(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($admin)->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat penghantaran',
            'custom_description' => 'Design custom customer',
            'requested_size' => '5cm x 5cm',
            'quantity' => 100,
            'cut_type' => 'standard',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
        ]);
    }

    public function test_admin_order_creation_auto_creates_invoice_and_redirects_to_confirmation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $category = Category::query()->create(['name' => 'Test', 'slug' => 'test']);
        $design = StickerDesign::query()->create([
            'category_id' => $category->id,
            'name' => 'Design Test',
            'is_active' => true,
        ]);
        $size = StickerSize::query()->create([
            'name' => 'Saiz Test',
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

        $response = $this->actingAs($admin)->post(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat penghantaran',
            'design_id' => $design->id,
            'size_id' => $size->id,
            'quantity' => 100,
            'cut_type' => 'standard',
        ]);

        $order = Order::query()->latest('id')->firstOrFail();
        $invoice = Invoice::query()->where('order_id', $order->id)->firstOrFail();
        $address = CustomerAddress::query()->where('user_id', $customer->id)->where('address', 'Alamat penghantaran')->firstOrFail();

        $response->assertRedirect(route('admin.invoices.edit', $invoice))
            ->assertSessionHas('success');
        $this->assertSame('120.00', (string) $invoice->amount);
        $this->assertSame($address->id, $order->customer_address_id);
        $this->assertSame($address->id, $invoice->customer_address_id);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'quantity' => 100,
            'line_total' => 120,
        ]);
    }

    private function createOrder(User $customer, string $orderNo, string $status): Order
    {
        return Order::query()->create([
            'order_no' => $orderNo,
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat Customer',
            'material' => 'Mirrorcote',
            'status' => $status,
            'subtotal' => 100,
            'total' => 100,
        ]);
    }
}
