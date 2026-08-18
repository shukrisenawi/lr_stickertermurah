<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_customer_without_deleting_historical_orders(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $address = CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => $customer->name,
            'address' => 'Alamat pelanggan',
            'no_hp' => '0123456789',
            'is_default' => true,
        ]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => '0123456789',
            'customer_address' => 'Alamat pelanggan',
            'material' => 'Mirrorcote',
            'status' => 'pending',
            'subtotal' => 100,
            'total' => 100,
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.customers.destroy', $customer));

        $response->assertRedirect(route('admin.customers.index'))
            ->assertSessionHas('success', 'Pelanggan berjaya dipadam.');
        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
        $this->assertDatabaseMissing('customer_addresses', ['id' => $address->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'user_id' => null]);
    }

    public function test_admin_account_cannot_be_deleted_from_customer_endpoint(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.customers.destroy', $admin));

        $response->assertRedirect(route('admin.customers.index'))
            ->assertSessionHas('error', 'Akaun admin tidak boleh dipadam.');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
