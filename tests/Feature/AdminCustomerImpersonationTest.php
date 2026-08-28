<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminCustomerImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_as_customer_and_return_to_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        $loginResponse = $this->actingAs($admin)->post(route('admin.customers.login-as', $customer));

        $loginResponse->assertRedirect(route('member.dashboard'));
        $this->assertAuthenticatedAs($customer);
        $this->assertSame($admin->id, session('impersonate_admin_id'));

        $memberResponse = $this->get(route('member.dashboard'));

        $memberResponse->assertInertia(fn (Assert $page) => $page
            ->component('Member/Dashboard')
            ->where('auth.user.id', $customer->id)
            ->where('auth.impersonating', true)
        );

        $returnResponse = $this->post(route('admin.return'));

        $returnResponse->assertRedirect(route('admin.customers.index'));
        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session('impersonate_admin_id'));
    }

    public function test_admin_can_view_customer_area_without_changing_temporary_password(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create([
            'is_admin' => false,
            'password' => Hash::make('123'),
            'must_change_password' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.customers.login-as', $customer))
            ->assertRedirect(route('member.dashboard'));

        $this->get(route('member.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Member/Dashboard')
                ->where('auth.user.id', $customer->id)
                ->where('auth.user.must_change_password', true)
                ->where('auth.impersonating', true)
            );

        $this->assertTrue($customer->fresh()->must_change_password);
    }

    public function test_admin_can_view_customer_area_during_under_construction(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);
        Setting::setValue('under_construction', '1');

        $this->actingAs($admin)
            ->post(route('admin.customers.login-as', $customer))
            ->assertRedirect(route('member.dashboard'));

        $response = $this->get(route('member.dashboard'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Member/Dashboard')
            ->where('auth.user.id', $customer->id)
            ->where('auth.impersonating', true)
        );
    }

    public function test_regular_customer_still_sees_under_construction_page(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        Setting::setValue('under_construction', '1');

        $response = $this->actingAs($customer)->get(route('member.dashboard'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/UnderConstruction')
        );
    }

    public function test_browser_back_to_admin_route_restores_original_admin_session(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin)->post(route('admin.customers.login-as', $customer));
        $this->assertAuthenticatedAs($customer);
        $this->assertSame($admin->id, session('impersonate_admin_id'));

        $response = $this->get(route('admin.customers.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Customers/Index')
            ->where('auth.user.id', $admin->id)
            ->where('auth.user.is_admin', true)
            ->where('auth.impersonating', false)
        );
        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session('impersonate_admin_id'));
    }

    public function test_customer_route_redirects_to_admin_after_impersonation_has_ended(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin)->post(route('admin.customers.login-as', $customer));
        $this->post(route('admin.return'));

        $response = $this->get(route('member.dashboard'));

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }
}
