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
            ->where('tab', 'members')
            ->where('addresses.data.0.user.id', $customer->id)
            ->where('addresses.data.0.recipient_name', 'Penerima Customer')
        );
    }

    public function test_admin_can_filter_addresses_by_member_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Ahli Berpaut',
            'address' => 'Jalan Ahli',
            'no_hp' => '0123456789',
            'is_default' => true,
        ]);

        CustomerAddress::query()->create([
            'user_id' => null,
            'recipient_name' => 'Bukan Ahli',
            'address' => 'Jalan Bukan Ahli',
            'no_hp' => '0198765432',
            'is_default' => false,
        ]);

        $memberResponse = $this->actingAs($admin)->get(route('admin.customer-addresses.index', ['tab' => 'members']));

        $memberResponse->assertInertia(fn (Assert $page) => $page
            ->where('tab', 'members')
            ->has('addresses.data', 1)
            ->where('addresses.data.0.recipient_name', 'Ahli Berpaut')
        );

        $nonMemberResponse = $this->actingAs($admin)->get(route('admin.customer-addresses.index', ['tab' => 'non-members']));

        $nonMemberResponse->assertInertia(fn (Assert $page) => $page
            ->where('tab', 'non-members')
            ->has('addresses.data', 1)
            ->where('addresses.data.0.recipient_name', 'Bukan Ahli')
        );
    }

    public function test_admin_can_repair_customer_addresses_to_ucwords(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Penerima Pertama',
            'address' => 'JALAN DAMAI, 43000 KAJANG',
            'no_hp' => '0123456789',
            'is_default' => true,
        ]);
        CustomerAddress::query()->create([
            'user_id' => null,
            'recipient_name' => 'Penerima Kedua',
            'address' => 'Alamat Sudah Baik',
            'no_hp' => '0198765432',
            'is_default' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.customer-addresses.repair-addresses'));

        $response->assertSessionHas('success', '1 alamat berjaya ditukar kepada format Ucwords.');
        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => $customer->id,
            'address' => 'Jalan Damai, 43000 Kajang',
        ]);
        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => null,
            'address' => 'Alamat Sudah Baik',
        ]);
    }

    public function test_admin_can_create_update_and_delete_customer_address(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        $createResponse = $this->actingAs($admin)->get(route('admin.customer-addresses.create', ['tab' => 'members']));

        $createResponse->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CustomerAddresses/Form')
            ->where('address', null)
            ->where('tab', 'members')
        );

        $storeResponse = $this->post(route('admin.customer-addresses.store'), [
            'user_id' => $customer->id,
            'recipient_name' => 'Penerima Baharu',
            'address' => 'Jalan Baharu',
            'no_hp' => '0123456789',
            'is_default' => true,
        ]);

        $storeResponse->assertRedirect(route('admin.customer-addresses.index', ['tab' => 'members']));
        $address = CustomerAddress::query()->firstOrFail();
        $this->assertDatabaseHas('customer_addresses', [
            'id' => $address->id,
            'user_id' => $customer->id,
            'address' => 'Jalan Baharu',
        ]);

        $editResponse = $this->get(route('admin.customer-addresses.edit', [
            'customerAddress' => $address,
            'tab' => 'members',
        ]));

        $editResponse->assertInertia(fn (Assert $page) => $page
            ->component('Admin/CustomerAddresses/Form')
            ->where('address.id', $address->id)
            ->where('address.user_id', $customer->id)
        );

        $updateResponse = $this->put(route('admin.customer-addresses.update', $address), [
            'user_id' => $customer->id,
            'recipient_name' => 'Penerima Dikemaskini',
            'address' => 'Jalan Dikemaskini',
            'no_hp' => '0198765432',
            'is_default' => false,
        ]);

        $updateResponse->assertRedirect(route('admin.customer-addresses.index', ['tab' => 'members']));
        $this->assertDatabaseHas('customer_addresses', [
            'id' => $address->id,
            'recipient_name' => 'Penerima Dikemaskini',
            'address' => 'Jalan Dikemaskini',
        ]);

        $deleteResponse = $this->delete(route('admin.customer-addresses.destroy', $address));

        $deleteResponse->assertRedirect(route('admin.customer-addresses.index', ['tab' => 'members']));
        $this->assertDatabaseMissing('customer_addresses', ['id' => $address->id]);
    }
}
