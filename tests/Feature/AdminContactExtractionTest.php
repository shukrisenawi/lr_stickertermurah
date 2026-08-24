<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\GoogleContactConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminContactExtractionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.sumopod.endpoint', 'https://ai.sumopod.com/v1/chat/completions');
        Config::set('services.sumopod.api_key', 'test-sumopod-key');
        Config::set('services.sumopod.model', 'gpt-5.6-luna');
    }

    public function test_admin_can_extract_contacts_with_sumopod(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Http::fake([
            'https://ai.sumopod.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            [
                                'name' => 'Abu bin Ahmad',
                                'phone' => '011-2222 3333',
                                'address' => 'Jalan Damai, 43000 Kajang',
                                'postcode' => '43000',
                            ],
                        ]),
                    ],
                ]],
            ]),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.contacts.extract.run'), [
            'raw_text' => 'Maklumat Abu bin Ahmad 011-2222 3333 Jalan Damai, 43000 Kajang',
        ]);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Contacts/Extract')
            ->where('contacts.0.name', 'ABU BIN AHMAD')
            ->where('contacts.0.phone', '011-2222 3333')
            ->where('contacts.0.address', 'JALAN DAMAI, 43000 KAJANG')
            ->where('contacts.0.postcode', '43000')
        );

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://ai.sumopod.com/v1/chat/completions'
            && data_get($request->data(), 'model') === 'gpt-5.6-luna'
            && ! array_key_exists('temperature', $request->data())
            && $request->header('Authorization') === ['Bearer test-sumopod-key']);
    }

    public function test_multiline_contacts_use_local_fallback_when_ai_is_unavailable(): void
    {
        Config::set('services.sumopod.api_key', '');
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.contacts.extract.run'), [
            'raw_text' => "Shahirah Rajiha\n0139037288\n02-12, Blok P, Pangsapuri Jasa, Tmn Mutiara Rini, Skudai, 81300, Johor Bahru, Johor",
        ]);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Contacts/Extract')
            ->where('contacts.0.name', 'SHAHIRAH RAJIHA')
            ->where('contacts.0.phone', '0139037288')
            ->where('contacts.0.address', '02-12, BLOK P, PANGSAPURI JASA, TMN MUTIARA RINI, SKUDAI, 81300, JOHOR BAHRU, JOHOR')
            ->where('contacts.0.postcode', '81300')
        );
    }

    public function test_extract_returns_error_when_no_contact_can_be_extracted(): void
    {
        Config::set('services.sumopod.api_key', '');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.contacts.extract.run'), [
            'raw_text' => 'Teks ini tidak mengandungi maklumat contact yang lengkap.',
        ])->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Contacts/Extract')
            ->where('contacts', [])
            ->where('swalError', 'Tiada maklumat contact yang boleh diekstrak daripada teks tersebut. Sila semak format dan cuba lagi.')
        );
    }

    public function test_admin_can_create_customer_from_extracted_contact(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->withSession(['contact_extract.raw_text' => 'ABU BIN AHMAD | 011-2222 3333 | JALAN DAMAI, 43000 KAJANG'])
            ->post(route('admin.contacts.extract.add-user'), [
                'name' => 'ABU BIN AHMAD',
                'phone' => '011-2222 3333',
                'address' => 'JALAN DAMAI, 43000 KAJANG',
                'postcode' => '43000',
            ]);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Contacts/Extract')
            ->where('duplicateError', null)
            ->where('phoneConflict', null)
            ->where('createdUserId', fn (?int $id): bool => $id !== null)
            ->where('createdAddressId', fn (?int $id): bool => $id !== null)
            ->where('redirectTo', null)
        );

        $customer = User::query()->where('no_tel', '601122223333')->firstOrFail();

        $this->assertSame('Abu Ahmad', $customer->name);
        $this->assertNull($customer->email);
        $this->assertFalse($customer->is_admin);
        $this->assertTrue($customer->must_change_password);
        $this->assertTrue(Hash::check('123', $customer->password));
        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => $customer->id,
            'recipient_name' => 'Abu Ahmad',
            'address' => 'Jalan damai, 43000 kajang',
            'no_hp' => '601122223333',
            'is_default' => true,
        ]);
    }

    public function test_admin_can_create_customer_and_request_order_redirect(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->withSession(['contact_extract.raw_text' => 'ALI BIN ABU | 011-4444 5555 | JALAN ORDER'])
            ->post(route('admin.contacts.extract.add-user'), [
                'name' => 'ALI BIN ABU',
                'phone' => '011-4444 5555',
                'address' => 'JALAN ORDER',
                'redirect_to_order' => true,
            ])
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Contacts/Extract')
                ->where('successType', 'customer')
                ->where('redirectTo', 'order')
                ->where('createdUserId', fn (?int $id): bool => $id !== null)
                ->where('createdAddressId', fn (?int $id): bool => $id !== null)
            );
    }

    public function test_admin_can_add_an_extracted_address_to_a_searched_customer_and_redirect_with_ids(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create([
            'is_admin' => false,
            'name' => 'Pelanggan Dipilih',
            'no_tel' => '601122223333',
        ]);
        CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Penerima Lama',
            'address' => 'Jalan Lama',
            'no_hp' => '601122223333',
            'is_default' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.contacts.extract.customers.search', ['q' => '011-2222 3333']))
            ->assertOk()
            ->assertJsonPath('results.0.id', $customer->id)
            ->assertJsonPath('results.0.addresses.0.address', 'Jalan Lama')
            ->assertJsonPath('results.0.addresses.0.is_default', true);

        $response = $this->actingAs($admin)
            ->withSession(['contact_extract.raw_text' => 'ALAMAT BAHARU | 011-2222 3333 | JALAN BARU'])
            ->post(route('admin.contacts.extract.add-address'), [
                'user_id' => $customer->id,
                'name' => 'ALAMAT BAHARU',
                'phone' => '011-2222 3333',
                'address' => 'JALAN BARU',
                'postcode' => '43000',
                'redirect_to_project' => true,
            ]);

        $address = CustomerAddress::query()
            ->where('user_id', $customer->id)
            ->where('address', 'Jalan baru')
            ->firstOrFail();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Contacts/Extract')
            ->where('successType', 'address')
            ->where('createdUserId', $customer->id)
            ->where('createdAddressId', $address->id)
        );
    }

    public function test_new_extracted_address_can_become_the_default_address(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $oldAddress = CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Penerima Lama',
            'address' => 'Jalan lama',
            'no_hp' => '601122223333',
            'is_default' => true,
        ]);

        $this->actingAs($admin)
            ->withSession(['contact_extract.raw_text' => 'ALAMAT BAHARU | 011-3333 4444 | JALAN BARU'])
            ->post(route('admin.contacts.extract.add-address'), [
                'user_id' => $customer->id,
                'name' => 'ALAMAT BAHARU',
                'phone' => '011-3333 4444',
                'address' => 'JALAN BARU',
                'postcode' => '43000',
                'make_default' => true,
            ])
            ->assertInertia(fn (Assert $page) => $page
                ->where('duplicateError', null)
            );

        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => $customer->id,
            'address' => 'Jalan baru',
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('customer_addresses', [
            'id' => $oldAddress->id,
            'is_default' => false,
        ]);
    }

    public function test_existing_extracted_address_is_not_added_twice(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Penerima Lama',
            'address' => 'Jalan lama',
            'no_hp' => '601122223333',
            'is_default' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['contact_extract.raw_text' => 'ALAMAT SAMA | 011-3333 4444 | JALAN LAMA'])
            ->post(route('admin.contacts.extract.add-address'), [
                'user_id' => $customer->id,
                'name' => 'ALAMAT SAMA',
                'phone' => '011-3333 4444',
                'address' => 'JALAN LAMA',
                'postcode' => '43000',
            ]);

        $response->assertInertia(fn (Assert $page) => $page
            ->where('duplicateError', 'Alamat ini sudah wujud untuk user yang dipilih. Sila semak alamat sedia ada dalam popup.')
        );
        $this->assertDatabaseCount('customer_addresses', 1);
    }

    public function test_new_customer_is_added_to_google_with_sc_name_after_creation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        GoogleContactConnection::query()->create([
            'user_id' => $admin->id,
            'google_email' => 'admin-google@example.com',
            'access_token' => 'google-access-token',
        ]);

        Http::fake([
            'people.googleapis.com/v1/people:createContact*' => Http::response([
                'resourceName' => 'people/contact-extracted',
            ]),
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['contact_extract.raw_text' => 'ABU BIN AHMAD | 011-2222 3333 | JALAN DAMAI, 43000 KAJANG'])
            ->post(route('admin.contacts.extract.add-user'), [
                'name' => 'ABU BIN AHMAD',
                'phone' => '011-2222 3333',
                'address' => 'JALAN DAMAI, 43000 KAJANG',
                'postcode' => '43000',
            ]);

        $customer = User::query()->where('no_tel', '601122223333')->firstOrFail();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('success', fn (?string $message): bool => str_contains((string) $message, 'Contact Google berjaya ditambah.'))
            ->where('createdUserId', $customer->id)
        );

        $this->assertSame('Abu Ahmad', $customer->name);
        $this->assertNull($customer->email);
        $this->assertDatabaseHas('google_contacts', [
            'resource_name' => 'people/contact-extracted',
            'name' => 'Sc Abu Ahmad',
            'normalized_phone' => '601122223333',
            'email' => null,
        ]);

        Http::assertSent(fn (Request $request): bool => data_get($request->data(), 'names.0.unstructuredName') === 'Sc Abu Ahmad'
            && data_get($request->data(), 'phoneNumbers.0.value') === '01122223333'
            && ! array_key_exists('emailAddresses', $request->data()));
    }

    public function test_existing_phone_requires_confirmation_for_a_new_address(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create([
            'is_admin' => false,
            'name' => 'Sc Pelanggan Lama',
            'no_tel' => '601122223333',
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['contact_extract.raw_text' => 'ABU BIN AHMAD | 011-2222 3333 | JALAN BARU'])
            ->post(route('admin.contacts.extract.add-user'), [
                'name' => 'ABU BIN AHMAD',
                'phone' => '011-2222 3333',
                'address' => 'JALAN BARU',
            ]);

        $response->assertInertia(fn (Assert $page) => $page
            ->where('phoneConflict.user_id', $customer->id)
            ->where('phoneConflict.user_name', 'Sc Pelanggan Lama')
            ->where('phoneConflict.address', 'Jalan baru')
        );

        $this->assertDatabaseCount('customer_addresses', 0);

        $this->actingAs($admin)
            ->withSession(['contact_extract.raw_text' => 'ABU BIN AHMAD | 011-2222 3333 | JALAN BARU'])
            ->post(route('admin.contacts.extract.add-user'), [
                'name' => 'ABU BIN AHMAD',
                'phone' => '011-2222 3333',
                'address' => 'JALAN BARU',
                'force_address' => true,
            ]);

        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => $customer->id,
            'recipient_name' => 'Abu Ahmad',
            'address' => 'Jalan baru',
            'no_hp' => '601122223333',
        ]);
    }

    public function test_same_phone_and_address_is_reported_as_duplicate(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create([
            'is_admin' => false,
            'no_tel' => '601122223333',
        ]);
        CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Sc Abu Ahmad',
            'address' => 'Jalan Damai',
            'no_hp' => '601122223333',
            'is_default' => true,
        ]);
        $response = $this->actingAs($admin)
            ->withSession(['contact_extract.raw_text' => 'ABU BIN AHMAD | 011-2222 3333 | JALAN DAMAI'])
            ->post(route('admin.contacts.extract.add-user'), [
                'name' => 'ABU BIN AHMAD',
                'phone' => '011-2222 3333',
                'address' => 'JALAN DAMAI',
            ]);

        $response->assertInertia(fn (Assert $page) => $page
            ->where('duplicateError', 'Data sama dah wujud. Alamat tidak ditambah semula.')
        );

        $this->assertSame(1, User::query()->where('no_tel', '601122223333')->count());
        $this->assertDatabaseCount('customer_addresses', 1);
    }
}
