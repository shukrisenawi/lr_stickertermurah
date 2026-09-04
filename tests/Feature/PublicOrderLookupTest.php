<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicOrderLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_lookup_requires_order_number_and_phone_and_hides_private_details(): void
    {
        $order = $this->createOrder();

        $this->post(route('orders.lookup'), [
            'order_no' => $order->order_no,
            'customer_phone' => '60123456789',
        ])->assertInertia(fn (Assert $page) => $page
            ->component('Public/LookupOrder')
            ->where('order.order_no', $order->order_no)
            ->where('order.status', 'shipped')
            ->where('order.payment_status', 'paid')
            ->where('order.tracking_no', null)
            ->missing('order.customer_address')
            ->missing('order.customer_phone')
        );
    }

    public function test_public_lookup_shows_tracking_after_order_is_completed(): void
    {
        $order = $this->createOrder('completed');

        $this->post(route('orders.lookup'), [
            'order_no' => $order->order_no,
            'customer_phone' => '60123456789',
        ])->assertInertia(fn (Assert $page) => $page
            ->component('Public/LookupOrder')
            ->where('order.status', 'completed')
            ->where('order.tracking_no', 'JNT123456789')
        );
    }

    public function test_public_lookup_rejects_a_non_matching_phone(): void
    {
        $order = $this->createOrder();

        $this->from(route('orders.lookup-form'))
            ->post(route('orders.lookup'), [
                'order_no' => $order->order_no,
                'customer_phone' => '60119999999',
            ])
            ->assertRedirect(route('orders.lookup-form'))
            ->assertSessionHasErrors('lookup');
    }

    private function createOrder(string $status = 'shipped'): Order
    {
        return Order::query()->create([
            'order_no' => 'ORD-LOOKUP-TEST',
            'customer_name' => 'Pelanggan Lookup',
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat sulit dijangka',
            'material' => 'Mirrorcote',
            'status' => $status,
            'tracking_no' => 'JNT123456789',
            'subtotal' => 100,
            'total' => 100,
            'payment_status' => 'paid',
        ]);
    }
}
