<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
