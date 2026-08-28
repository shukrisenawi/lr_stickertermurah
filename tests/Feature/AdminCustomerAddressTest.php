<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\GoogleContactConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
            'address' => 'JALAN DAMAI,43000 KAJANG, SELANGOR ,MALAYSIA',
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
            'address' => 'Jalan Damai, 43000 Kajang, Selangor, Malaysia',
        ]);
        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => null,
            'address' => 'Alamat Sudah Baik',
        ]);
    }

    public function test_admin_can_trim_address_after_state_and_postcode(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        CustomerAddress::query()->create([
            'user_id' => null,
            'recipient_name' => 'Penerima',
            'address' => 'NO.43 LORONG IS 93 PERKAMPUNGAN INDERASEMPURNA, 25150 JALAN KUANTAN-PEKAN PAHANG, 25150, KUANTAN 2, KUANTAN, PAHANG',
            'no_hp' => null,
            'is_default' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.customer-addresses.repair-addresses'));

        $response->assertSessionHas('success', '1 alamat berjaya ditukar kepada format Ucwords.');
        $this->assertDatabaseHas('customer_addresses', [
            'address' => 'No.43 Lorong Is 93 Perkampungan Inderasempurna, 25150 Jalan Kuantan-Pekan Pahang',
        ]);
    }

    public function test_admin_can_repair_addresses_without_failing_on_duplicate_result(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Alamat Sedia Ada',
            'address' => 'No.43 Lorong Is 93 Perkampungan Inderasempurna, 25150 Jalan Kuantan-Pekan Pahang',
            'no_hp' => null,
            'is_default' => true,
        ]);
        CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Alamat Pendua',
            'address' => 'NO.43 LORONG IS 93 PERKAMPUNGAN INDERASEMPURNA, 25150 JALAN KUANTAN-PEKAN PAHANG, 25150, KUANTAN 2, KUANTAN, PAHANG',
            'no_hp' => null,
            'is_default' => false,
        ]);
        CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Alamat Lain',
            'address' => 'JALAN DAMAI,43000 KAJANG, SELANGOR ,MALAYSIA',
            'no_hp' => null,
            'is_default' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.customer-addresses.repair-addresses'));

        $response->assertSessionHas('success', '1 alamat berjaya ditukar kepada format Ucwords. 1 alamat tidak diubah kerana akan menjadi alamat pendua untuk user yang sama.');
        $this->assertDatabaseHas('customer_addresses', [
            'recipient_name' => 'Alamat Pendua',
            'address' => 'NO.43 LORONG IS 93 PERKAMPUNGAN INDERASEMPURNA, 25150 JALAN KUANTAN-PEKAN PAHANG, 25150, KUANTAN 2, KUANTAN, PAHANG',
        ]);
        $this->assertDatabaseHas('customer_addresses', [
            'recipient_name' => 'Alamat Lain',
            'address' => 'Jalan Damai, 43000 Kajang, Selangor, Malaysia',
        ]);
    }

    public function test_admin_can_repair_customer_address_phone_numbers(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        CustomerAddress::query()->create([
            'user_id' => null,
            'recipient_name' => 'Penerima Pertama',
            'address' => 'Jalan Damai',
            'no_hp' => '60195168839',
            'is_default' => false,
        ]);
        CustomerAddress::query()->create([
            'user_id' => null,
            'recipient_name' => 'Penerima Kedua',
            'address' => 'Jalan Sejahtera',
            'no_hp' => '+60 11-1234 5678',
            'is_default' => false,
        ]);
        CustomerAddress::query()->create([
            'user_id' => null,
            'recipient_name' => 'Penerima Ketiga',
            'address' => 'Jalan Baik',
            'no_hp' => '019-516 8839',
            'is_default' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.customer-addresses.repair-phones'));

        $response->assertSessionHas('success', '2 no. telefon berjaya diformatkan.');
        $this->assertDatabaseHas('customer_addresses', ['no_hp' => '019-516 8839']);
        $this->assertDatabaseHas('customer_addresses', ['no_hp' => '011-1234 5678']);
    }

    public function test_admin_can_link_unlinked_addresses_to_google_contacts_by_phone(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $connection = GoogleContactConnection::query()->create([
            'user_id' => $admin->id,
            'google_email' => 'admin-google@example.com',
            'access_token' => 'access-token',
        ]);
        $connection->contacts()->create([
            'resource_name' => 'people/contact-abu',
            'name' => 'Sc Abu Ahmad',
            'normalized_phone' => '601122223333',
            'phone' => '011-2222 3333',
        ]);

        $firstAddress = CustomerAddress::query()->create([
            'user_id' => null,
            'recipient_name' => 'Abu Ahmad Rumah',
            'address' => 'Jalan Rumah',
            'no_hp' => '011-2222 3333',
            'is_default' => false,
        ]);
        $secondAddress = CustomerAddress::query()->create([
            'user_id' => null,
            'recipient_name' => 'Abu Ahmad Pejabat',
            'address' => 'Jalan Pejabat',
            'no_hp' => '601122223333',
            'is_default' => false,
        ]);
        $unmatchedAddress = CustomerAddress::query()->create([
            'user_id' => null,
            'recipient_name' => 'Tiada Padanan',
            'address' => 'Jalan Lain',
            'no_hp' => '019-999 8888',
            'is_default' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.customer-addresses.link-by-phone'));

        $response->assertSessionHas('success', '1 user baharu dan 2 alamat berjaya dipautkan berdasarkan no. telefon.');

        $customer = User::query()->where('no_tel', '601122223333')->firstOrFail();
        $this->assertSame('Abu Ahmad', $customer->name);
        $this->assertFalse($customer->is_admin);
        $this->assertTrue($customer->must_change_password);
        $this->assertTrue(Hash::check('123', $customer->password));
        $this->assertDatabaseHas('customer_addresses', [
            'id' => $firstAddress->id,
            'user_id' => $customer->id,
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('customer_addresses', [
            'id' => $secondAddress->id,
            'user_id' => $customer->id,
            'is_default' => false,
        ]);
        $this->assertDatabaseHas('customer_addresses', [
            'id' => $unmatchedAddress->id,
            'user_id' => null,
        ]);
        $this->assertSame(1, User::query()->where('no_tel', '601122223333')->count());
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
