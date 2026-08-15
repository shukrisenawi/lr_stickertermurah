<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminCustomerAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_customer_addresses(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Penerima Customer',
            'address' => 'Jalan Customer',
            'no_hp' => '0123456789',
            'is_default' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.customer-addresses.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CustomerAddresses/Index')
            ->has('addresses.data', 1)
            ->where('addresses.data.0.user.id', $customer->id)
            ->where('addresses.data.0.recipient_name', 'Penerima Customer')
        );
    }
}
