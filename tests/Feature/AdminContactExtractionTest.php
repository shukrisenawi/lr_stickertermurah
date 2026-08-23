<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
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
            && $request->header('Authorization') === ['Bearer test-sumopod-key']);
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
        );

        $customer = User::query()->where('no_tel', '601122223333')->firstOrFail();

        $this->assertSame('Sc Abu Ahmad', $customer->name);
        $this->assertFalse($customer->is_admin);
        $this->assertTrue($customer->must_change_password);
        $this->assertTrue(Hash::check('123', $customer->password));
        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => $customer->id,
            'recipient_name' => 'Sc Abu Ahmad',
            'address' => 'Jalan damai, 43000 kajang',
            'no_hp' => '601122223333',
            'is_default' => true,
        ]);
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
            'recipient_name' => 'Sc Abu Ahmad',
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
