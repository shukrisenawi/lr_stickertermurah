<?php

namespace Tests\Feature;

use App\Models\Order;
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
