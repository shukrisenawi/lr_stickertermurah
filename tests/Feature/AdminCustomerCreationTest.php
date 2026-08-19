<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
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
            'email' => 'customer-baharu@example.com',
            'no_tel' => '0112222333',
            'mode' => 'new',
            'recipient_name' => 'Customer Baharu',
            'address' => 'Alamat customer baharu',
        ]);

        $response->assertRedirect(route('admin.customers.index'))
            ->assertSessionHas('success', 'Customer berjaya didaftarkan.');

        $customer = User::query()->where('email', 'customer-baharu@example.com')->firstOrFail();

        $response->assertSessionHas('created_customer_id', $customer->id);
        $this->assertSame('60112222333', $customer->no_tel);
        $this->assertFalse($customer->is_admin);
        $this->assertTrue($customer->must_change_password);
        $this->assertTrue(Hash::check('123', $customer->password));
        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => $customer->id,
            'recipient_name' => 'Customer Baharu',
            'address' => 'Alamat customer baharu',
            'no_hp' => '60112222333',
            'is_default' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.customers.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('createdCustomer.id', $customer->id)
                ->where('createdCustomer.name', 'Customer Baharu')
            );
    }

    public function test_admin_cannot_create_customer_with_existing_phone(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['no_tel' => '60112222333', 'is_admin' => false]);

        $response = $this->actingAs($admin)->post(route('admin.customers.store'), [
            'no_tel' => '0112222333',
            'address' => 'Alamat customer',
        ]);

        $response->assertSessionHasErrors(['no_tel']);
        $this->assertDatabaseMissing('users', ['name' => 'Customer Duplicate']);
        $this->assertDatabaseCount('customer_addresses', 0);
    }

    public function test_admin_can_claim_existing_unlinked_address_during_registration(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $address = CustomerAddress::query()->create([
            'recipient_name' => 'Penerima Lama',
            'address' => 'Alamat Lama',
            'no_hp' => '0113333444',
            'is_default' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.customers.create', ['no_tel' => '0113333444']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('lookup.phone', '60113333444')
                ->where('lookup.account_exists', false)
                ->where('lookup.addresses.0.id', $address->id)
            );

        $response = $this->actingAs($admin)->post(route('admin.customers.store'), [
            'no_tel' => '0113333444',
            'mode' => 'matched',
            'address_id' => $address->id,
            'email' => 'penerima-lama@example.com',
        ]);

        $response->assertRedirect(route('admin.customers.index'));
        $customer = User::query()->where('email', 'penerima-lama@example.com')->firstOrFail();

        $this->assertDatabaseHas('customer_addresses', [
            'id' => $address->id,
            'user_id' => $customer->id,
            'is_default' => true,
        ]);
    }
}
