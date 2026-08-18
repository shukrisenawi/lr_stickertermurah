<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminCustomerCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_customer_creation_form(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.customers.create'))
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Customers/Create'));
    }

    public function test_admin_can_create_customer_with_default_address(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.customers.store'), [
            'name' => 'Customer Baharu',
            'email' => 'customer-baharu@example.com',
            'no_tel' => '0112222333',
            'address' => 'Alamat customer baharu',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.customers.index'))
            ->assertSessionHas('success', 'Customer berjaya didaftarkan.');

        $customer = User::query()->where('email', 'customer-baharu@example.com')->firstOrFail();

        $this->assertSame('60112222333', $customer->no_tel);
        $this->assertFalse($customer->is_admin);
        $this->assertTrue(Hash::check('password123', $customer->password));
        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => $customer->id,
            'recipient_name' => 'Customer Baharu',
            'address' => 'Alamat customer baharu',
            'no_hp' => '60112222333',
            'is_default' => true,
        ]);
    }

    public function test_admin_cannot_create_customer_with_existing_phone(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['no_tel' => '60112222333', 'is_admin' => false]);

        $response = $this->actingAs($admin)->post(route('admin.customers.store'), [
            'name' => 'Customer Duplicate',
            'no_tel' => '0112222333',
            'address' => 'Alamat customer',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['no_tel']);
        $this->assertDatabaseMissing('users', ['name' => 'Customer Duplicate']);
        $this->assertDatabaseCount('customer_addresses', 0);
    }
}
