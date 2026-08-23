<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\GoogleContactConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminGoogleContactTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.google.client_id', 'google-client-id');
        Config::set('services.google.client_secret', 'google-client-secret');
        Config::set('services.google.redirect', 'http://127.0.0.1:8000/auth/google/callback');
    }

    public function test_admin_can_view_contact_page_and_customer_options(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create([
            'is_admin' => false,
            'name' => 'Aisyah Customer',
            'no_tel' => '01122334455',
        ]);
        $this->connectGoogle($admin);

        CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Aisyah Penerima',
            'address' => 'Jalan Damai, Kuala Lumpur',
            'no_hp' => '01122334455',
            'is_default' => true,
        ]);

        Http::fake([
            'people.googleapis.com/v1/people/me/connections*' => Http::response([
                'connections' => [[
                    'resourceName' => 'people/contact-1',
                    'etag' => 'etag-1',
                    'names' => [['displayName' => 'Aisyah Contact']],
                    'phoneNumbers' => [['value' => '+601122334455']],
                    'emailAddresses' => [['value' => 'contact@example.com']],
                    'addresses' => [['formattedValue' => 'Jalan Contact, Selangor']],
                ]],
            ]),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.contacts.google.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Contacts/Google')
            ->where('isConfigured', true)
            ->where('callbackUrl', 'http://127.0.0.1:8000/auth/google/callback')
            ->where('connection.email', 'admin-google@example.com')
            ->has('customers', 1)
            ->where('customers.0.id', $customer->id)
            ->where('customers.0.addresses.0.recipient_name', 'Aisyah Penerima')
            ->has('contacts.data', 1)
            ->where('contacts.total', 1)
            ->where('contacts.data.0.resource_name', 'people/contact-1')
            ->where('contacts.data.0.name', 'Aisyah Contact')
            ->where('contacts.data.0.phone', '+601122334455')
            ->where('contacts.data.0.email', 'contact@example.com')
            ->where('contacts.data.0.address', 'Jalan Contact, Selangor')
        );
    }

    public function test_google_contacts_are_paginated_by_twenty_records(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->connectGoogle($admin);

        $connections = [];
        for ($index = 1; $index <= 25; $index++) {
            $connections[] = [
                'resourceName' => 'people/contact-'.$index,
                'names' => [['displayName' => sprintf('Contact %02d', $index)]],
            ];
        }

        Http::fake([
            'people.googleapis.com/v1/people/me/connections*' => Http::response([
                'connections' => $connections,
            ]),
        ]);

        $firstPage = $this->actingAs($admin)->get(route('admin.contacts.google.index'));

        $firstPage->assertInertia(fn (Assert $page) => $page
            ->where('contacts.per_page', 20)
            ->where('contacts.total', 25)
            ->where('contacts.current_page', 1)
            ->where('contacts.last_page', 2)
            ->has('contacts.data', 20)
            ->where('contacts.data.0.name', 'Contact 01')
            ->where('contacts.data.19.name', 'Contact 20')
        );

        $secondPage = $this->actingAs($admin)->get(route('admin.contacts.google.index', ['page' => 2]));

        $secondPage->assertInertia(fn (Assert $page) => $page
            ->where('contacts.current_page', 2)
            ->has('contacts.data', 5)
            ->where('contacts.data.0.name', 'Contact 21')
            ->where('contacts.data.4.name', 'Contact 25')
        );
    }

    public function test_google_connect_uses_the_configured_redirect_uri(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.contacts.google.connect'));

        $response->assertRedirectContains('accounts.google.com/o/oauth2/auth');
        $response->assertRedirectContains(urlencode('http://127.0.0.1:8000/auth/google/callback'));
        $this->assertSame('/auth/google/callback', parse_url(route('admin.contacts.google.callback'), PHP_URL_PATH));
    }

    public function test_manual_contact_is_not_created_when_phone_already_exists(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->connectGoogle($admin);

        Http::fake([
            'people.googleapis.com/v1/people/me/connections*' => Http::response([
                'connections' => [[
                    'names' => [['displayName' => 'Contact Lama']],
                    'phoneNumbers' => [['value' => '+60 11-2233 4455']],
                ]],
            ]),
            '*' => Http::response([], 500),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.contacts.google.manual.store'), [
            'name' => 'Contact Baharu',
            'phone' => '01122334455',
            'email' => 'baharu@example.com',
        ]);

        $response->assertSessionHas('error', fn (string $message): bool => str_contains($message, 'Contact Lama'));
        Http::assertNotSent(fn (Request $request): bool => $request->method() === 'POST');
    }

    public function test_duplicate_check_reads_every_google_contacts_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->connectGoogle($admin);

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && str_contains($request->url(), 'pageToken=second-page')) {
                return Http::response([
                    'connections' => [[
                        'names' => [['displayName' => 'Contact Halaman Kedua']],
                        'phoneNumbers' => [['canonicalForm' => '+60115556677']],
                    ]],
                ]);
            }

            return Http::response([
                'connections' => [],
                'nextPageToken' => 'second-page',
            ]);
        });

        $response = $this->actingAs($admin)->post(route('admin.contacts.google.manual.store'), [
            'name' => 'Contact Baharu',
            'phone' => '0115556677',
        ]);

        $response->assertSessionHas('error', fn (string $message): bool => str_contains($message, 'Contact Halaman Kedua'));
        Http::assertSentCount(2);
        Http::assertNotSent(fn (Request $request): bool => $request->method() === 'POST');
    }

    public function test_admin_can_create_manual_google_contact_after_duplicate_check(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->connectGoogle($admin);

        Http::fake([
            'people.googleapis.com/v1/people/me/connections*' => Http::response(['connections' => []]),
            'people.googleapis.com/v1/people:createContact*' => Http::response([
                'resourceName' => 'people/contact-1',
            ]),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.contacts.google.manual.store'), [
            'name' => 'Nur Aisyah',
            'phone' => '011-9988 7766',
            'email' => 'aisyah@example.com',
            'address' => 'Jalan Damai, Selangor',
        ]);

        $response->assertSessionHas('success');
        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && str_contains($request->url(), 'people:createContact')
            && data_get($request->data(), 'names.0.unstructuredName') === 'Nur Aisyah'
            && data_get($request->data(), 'phoneNumbers.0.value') === '+601199887766'
            && data_get($request->data(), 'emailAddresses.0.value') === 'aisyah@example.com'
            && data_get($request->data(), 'addresses.0.formattedValue') === 'Jalan Damai, Selangor');
    }

    public function test_admin_can_create_google_contact_from_customer_data(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create([
            'is_admin' => false,
            'name' => 'Nama Akaun',
            'email' => 'customer@example.com',
            'no_tel' => '0120000000',
        ]);
        $address = CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Nama Penerima',
            'address' => 'Alamat Penerima',
            'no_hp' => '0198765432',
            'is_default' => true,
        ]);
        $this->connectGoogle($admin);

        Http::fake([
            'people.googleapis.com/v1/people/me/connections*' => Http::response(['connections' => []]),
            'people.googleapis.com/v1/people:createContact*' => Http::response([
                'resourceName' => 'people/contact-2',
            ]),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.contacts.google.customer.store'), [
            'customer_id' => $customer->id,
            'address_id' => $address->id,
        ]);

        $response->assertSessionHas('success');
        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && data_get($request->data(), 'names.0.unstructuredName') === 'Nama Penerima'
            && data_get($request->data(), 'phoneNumbers.0.value') === '+60198765432'
            && data_get($request->data(), 'emailAddresses.0.value') === 'customer@example.com'
            && data_get($request->data(), 'addresses.0.formattedValue') === 'Alamat Penerima');
    }

    public function test_customer_cannot_use_an_address_owned_by_another_customer(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create([
            'is_admin' => false,
            'no_tel' => '0111111111',
        ]);
        $otherCustomer = User::factory()->create(['is_admin' => false]);
        $otherAddress = CustomerAddress::query()->create([
            'user_id' => $otherCustomer->id,
            'recipient_name' => 'Penerima Lain',
            'address' => 'Alamat Lain',
            'no_hp' => '0199999999',
            'is_default' => true,
        ]);
        $this->connectGoogle($admin);

        Http::fake();

        $response = $this->actingAs($admin)->post(route('admin.contacts.google.customer.store'), [
            'customer_id' => $customer->id,
            'address_id' => $otherAddress->id,
        ]);

        $response->assertSessionHasErrors('address_id');
        Http::assertNothingSent();
    }

    public function test_contact_cannot_be_created_without_google_connection(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Http::fake();

        $response = $this->actingAs($admin)->post(route('admin.contacts.google.manual.store'), [
            'name' => 'Nur Aisyah',
            'phone' => '01199887766',
        ]);

        $response->assertRedirect(route('admin.contacts.google.index'));
        $response->assertSessionHas('error');
        Http::assertNothingSent();
    }

    public function test_admin_can_update_google_contact(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->connectGoogle($admin);

        Http::fake([
            'people.googleapis.com/v1/people/me/connections*' => Http::response(['connections' => []]),
            'people.googleapis.com/v1/people/contact-1:updateContact*' => Http::response([
                'resourceName' => 'people/contact-1',
            ]),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.contacts.google.update'), [
            'resource_name' => 'people/contact-1',
            'etag' => 'etag-1',
            'name' => 'Contact Dikemaskini',
            'phone' => '011-9988 7766',
            'email' => 'dikemaskini@example.com',
            'address' => 'Alamat Baharu',
        ]);

        $response->assertSessionHas('success');
        Http::assertSent(fn (Request $request): bool => $request->method() === 'PATCH'
            && str_contains($request->url(), 'people/contact-1:updateContact')
            && str_contains($request->url(), 'updatePersonFields=names,phoneNumbers,emailAddresses,addresses')
            && data_get($request->data(), 'resourceName') === 'people/contact-1'
            && data_get($request->data(), 'etag') === 'etag-1'
            && data_get($request->data(), 'names.0.unstructuredName') === 'Contact Dikemaskini'
            && data_get($request->data(), 'phoneNumbers.0.value') === '+601199887766'
            && data_get($request->data(), 'emailAddresses.0.value') === 'dikemaskini@example.com'
            && data_get($request->data(), 'addresses.0.formattedValue') === 'Alamat Baharu');
    }

    public function test_admin_can_delete_google_contact(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->connectGoogle($admin);

        Http::fake([
            'people.googleapis.com/v1/people/contact-1:deleteContact' => Http::response([]),
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.contacts.google.destroy'), [
            'resource_name' => 'people/contact-1',
        ]);

        $response->assertSessionHas('success');
        Http::assertSent(fn (Request $request): bool => $request->method() === 'DELETE'
            && str_contains($request->url(), 'people/contact-1:deleteContact'));
    }

    private function connectGoogle(User $admin): GoogleContactConnection
    {
        return GoogleContactConnection::query()->create([
            'user_id' => $admin->id,
            'google_id' => 'google-user-id',
            'google_email' => 'admin-google@example.com',
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'expires_at' => now()->addHour(),
            'connected_at' => now(),
        ]);
    }
}
