<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminCustomerEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_customer_edit_form_without_email(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false, 'email' => null]);

        $this->actingAs($admin)
            ->get(route('admin.customers.edit', $customer))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Customers/Edit')
                ->where('customer.id', $customer->id)
                ->where('customer.email', null)
            );
    }

    public function test_admin_can_update_customer_without_email(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create([
            'is_admin' => false,
            'name' => 'Nama Lama',
            'email' => 'lama@example.com',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.customers.update', $customer), [
            'name' => 'Nama Baharu',
            'email' => '',
            'phone' => '',
            'address' => '',
        ]);

        $response->assertRedirect(route('admin.customers.index'))
            ->assertSessionHas('success', 'Maklumat pelanggan berjaya dikemaskini.');
        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Nama Baharu',
            'email' => null,
        ]);
    }
}
