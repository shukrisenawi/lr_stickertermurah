<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminCustomerProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_create_receives_the_requested_customer_id(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false, 'email' => null]);

        $this->actingAs($admin)
            ->get(route('admin.projects.create', ['user_id' => $customer->id]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Create')
                ->where('initialUserId', $customer->id)
                ->where('customers.0.id', $customer->id)
            );
    }

    public function test_project_keeps_the_requested_customer_address_id(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false, 'email' => null]);
        $address = CustomerAddress::query()->create([
            'user_id' => $customer->id,
            'recipient_name' => 'Penerima Test',
            'address' => 'Jalan Project',
            'no_hp' => '601122223333',
            'is_default' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.projects.create', ['user_id' => $customer->id, 'address_id' => $address->id]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('initialUserId', $customer->id)
                ->where('initialAddressId', $address->id)
                ->where('customers.0.addresses.0.id', $address->id)
            );

        $this->actingAs($admin)
            ->post(route('admin.projects.store'), [
                'user_id' => $customer->id,
                'customer_address_id' => $address->id,
                'title' => 'Project Dengan Alamat',
                'files' => [UploadedFile::fake()->create('design.pdf', 10, 'application/pdf')],
            ])
            ->assertRedirect(route('admin.projects.index'));

        $this->assertDatabaseHas('customer_projects', [
            'user_id' => $customer->id,
            'customer_address_id' => $address->id,
            'title' => 'Project Dengan Alamat',
        ]);
    }
}
