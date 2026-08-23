<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminInvoiceAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_invoice_uses_the_selected_customer_address_id(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $address = CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Penerima Tepat',
            'address' => 'Alamat Tepat',
            'no_hp' => '601122223333',
            'is_default' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.invoices.manual.create', ['user_id' => $customer->id, 'address_id' => $address->id]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Invoices/ManualCreate')
                ->where('initialUserId', $customer->id)
                ->where('initialAddressId', $address->id)
            );

        $this->actingAs($admin)
            ->post(route('admin.invoices.manual.store'), [
                'user_id' => $customer->id,
                'customer_address_id' => $address->id,
                'customer_name' => 'Nama Salah',
                'customer_phone' => '0100000000',
                'customer_address' => 'Alamat Salah',
                'issue_date' => now()->toDateString(),
                'amount' => '10.00',
                'items' => [[
                    'description' => 'Sticker',
                    'quantity' => 1,
                    'unit_price' => 10,
                ]],
            ])
            ->assertRedirect();

        $invoice = Invoice::query()->latest('id')->firstOrFail();
        $this->assertSame($address->id, $invoice->customer_address_id);
        $this->assertSame('Penerima Tepat', $invoice->customer_name);
        $this->assertSame('601122223333', $invoice->customer_phone);
        $this->assertSame('Alamat Tepat', $invoice->customer_address);
    }

    public function test_manual_invoice_rejects_an_address_belonging_to_another_customer(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        $otherCustomer = User::factory()->create(['is_admin' => false]);
        $address = CustomerAddress::query()->create([
            'user_id' => $otherCustomer->id,
            'recipient_name' => 'User Lain',
            'address' => 'Alamat User Lain',
            'no_hp' => '601111111111',
            'is_default' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.invoices.manual.store'), [
                'user_id' => $customer->id,
                'customer_address_id' => $address->id,
                'customer_name' => 'Customer',
                'customer_phone' => '0123456789',
                'customer_address' => 'Alamat',
                'issue_date' => now()->toDateString(),
                'amount' => '10.00',
                'items' => [[
                    'description' => 'Sticker',
                    'quantity' => 1,
                    'unit_price' => 10,
                ]],
            ])
            ->assertSessionHasErrors('customer_address_id');

        $this->assertDatabaseCount('invoices', 0);
    }
}
