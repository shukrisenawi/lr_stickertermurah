<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_sorted_by_latest_login(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $older = User::factory()->create(['is_admin' => false]);
        $newer = User::factory()->create(['is_admin' => false]);
        $neverLoggedIn = User::factory()->create(['is_admin' => false]);

        $older->forceFill([
            'last_login_at' => Carbon::parse('2026-08-29 10:00:00'),
            'last_seen_at' => Carbon::parse('2026-08-29 10:30:00'),
        ])->saveQuietly();
        $newer->forceFill([
            'last_login_at' => Carbon::parse('2026-08-30 10:00:00'),
            'last_seen_at' => Carbon::parse('2026-08-30 10:30:00'),
        ])->saveQuietly();

        $this->actingAs($admin)
            ->get(route('admin.user-login.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/UserLogin/Index')
                ->has('users.data', 2)
                ->where('users.data.0.id', $newer->id)
                ->where('users.data.1.id', $older->id)
                ->where('users.data.0.is_online', false)
                ->where('summary.total', 3)
                ->where('summary.loggedIn', 2)
                ->where('summary.online', 0));
    }

    public function test_successful_member_login_records_login_and_online_times(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'password' => Hash::make('secret-password'),
            'is_admin' => false,
        ]);

        $this->post(route('member.login.attempt'), [
            'login' => 'member@example.com',
            'password' => 'secret-password',
        ])->assertRedirect();

        $user->refresh();

        $this->assertNotNull($user->last_login_at);
        $this->assertNotNull($user->last_seen_at);
        $this->assertEquals($user->last_login_at?->getTimestamp(), $user->last_seen_at?->getTimestamp());
    }

    public function test_successful_admin_login_records_login_and_online_times(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin-login@example.com',
            'password' => Hash::make('secret-password'),
            'is_admin' => true,
        ]);

        $this->post(route('admin.login.attempt'), [
            'email' => 'admin-login@example.com',
            'password' => 'secret-password',
        ])->assertRedirect(route('admin.dashboard'));

        $admin->refresh();

        $this->assertNotNull($admin->last_login_at);
        $this->assertNotNull($admin->last_seen_at);
        $this->assertEquals($admin->last_login_at?->getTimestamp(), $admin->last_seen_at?->getTimestamp());
    }

    public function test_non_admin_cannot_access_user_login_page(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($customer)
            ->get(route('admin.user-login.index'))
            ->assertRedirect(route('admin.login'));
    }
}
