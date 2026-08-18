<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminCustomerPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reset_customer_password_to_temporary_password(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create([
            'is_admin' => false,
            'password' => Hash::make('password-lama'),
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.customers.reset-password', $customer));

        $response->assertRedirect(route('admin.customers.index'));

        $customer->refresh();
        $this->assertTrue(Hash::check('123', $customer->password));
        $this->assertTrue($customer->must_change_password);
    }

    public function test_admin_account_cannot_be_reset_from_customer_endpoint(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'password' => Hash::make('admin-secret'),
        ]);
        $originalPassword = $admin->password;

        $this->actingAs($admin)
            ->post(route('admin.customers.reset-password', $admin))
            ->assertSessionHas('error');

        $this->assertSame($originalPassword, $admin->fresh()->password);
    }

    public function test_customer_with_temporary_password_must_change_it_before_accessing_member_pages(): void
    {
        $customer = User::factory()->create([
            'is_admin' => false,
            'password' => Hash::make('123'),
            'must_change_password' => true,
        ]);

        $this->post(route('member.login.attempt'), [
            'login' => $customer->email,
            'password' => '123',
        ])->assertRedirect(route('member.profile.password'));

        $this->get(route('member.dashboard'))
            ->assertRedirect(route('member.profile.password'));

        $this->get(route('member.profile.password'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Member/Profile/Password')
                ->where('auth.user.must_change_password', true)
            );
    }

    public function test_customer_can_continue_after_changing_temporary_password(): void
    {
        $customer = User::factory()->create([
            'is_admin' => false,
            'password' => Hash::make('123'),
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($customer)->put(route('member.profile.password.update'), [
            'current_password' => '123',
            'password' => 'abc123',
            'password_confirmation' => 'abc123',
        ]);

        $response->assertRedirect(route('member.dashboard'));

        $customer->refresh();
        $this->assertTrue(Hash::check('abc123', $customer->password));
        $this->assertFalse($customer->must_change_password);

        $this->get(route('member.dashboard'))->assertOk();
    }

    public function test_customer_with_temporary_password_can_still_logout(): void
    {
        $customer = User::factory()->create([
            'is_admin' => false,
            'must_change_password' => true,
        ]);

        $this->actingAs($customer)
            ->post(route('member.logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
