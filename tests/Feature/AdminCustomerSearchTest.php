<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminCustomerSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_customers_by_phone_number(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $matchingCustomer = User::factory()->create([
            'name' => 'Pelanggan Telefon',
            'is_admin' => false,
        ]);
        $otherCustomer = User::factory()->create([
            'name' => 'Pelanggan Lain',
            'is_admin' => false,
        ]);

        CustomerAddress::query()->create([
            'user_id' => $matchingCustomer->id,
            'recipient_name' => $matchingCustomer->name,
            'address' => 'Alamat pelanggan',
            'no_hp' => '0123456789',
            'is_default' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.customers.index', ['q' => '0123456789']));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('search', '0123456789')
            ->has('customers.data', 1)
            ->where('customers.data.0.id', $matchingCustomer->id)
        );
    }
}
