<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_register_from_a_matching_phone_and_link_all_addresses(): void
    {
        $first = CustomerAddress::query()->create([
            'user_id' => null,
            'recipient_name' => 'Ali Ahmad',
            'address' => 'Alamat pertama',
            'no_hp' => '60195168839',
            'is_default' => false,
        ]);

        $second = CustomerAddress::query()->create([
            'user_id' => null,
            'recipient_name' => 'Ali Ahmad',
            'address' => 'Alamat kedua',
            'no_hp' => '0195168839',
            'is_default' => false,
        ]);

        $response = $this->post(route('member.register.store'), [
            'no_tel' => '0195168839',
            'mode' => 'matched',
            'address_id' => $second->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::query()->where('no_tel', '60195168839')->firstOrFail();

        $response->assertRedirect(route('member.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('Ali Ahmad', $user->name);
        $this->assertTrue($second->fresh()->is_default);
        $this->assertFalse($first->fresh()->is_default);
        $this->assertSame($user->id, $first->fresh()->user_id);
        $this->assertSame($user->id, $second->fresh()->user_id);
    }

    public function test_member_can_register_with_a_new_address_when_phone_is_not_found(): void
    {
        $response = $this->post(route('member.register.store'), [
            'no_tel' => '0123456789',
            'mode' => 'new',
            'recipient_name' => 'Siti Aminah',
            'address' => 'No. 10, Jalan Sticker, 43000 Kajang',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::query()->where('no_tel', '60123456789')->firstOrFail();

        $response->assertRedirect(route('member.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->email);
        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => $user->id,
            'recipient_name' => 'Siti Aminah',
            'is_default' => true,
        ]);
    }

    public function test_member_can_login_with_phone_number(): void
    {
        $user = User::factory()->create([
            'no_tel' => '60195168839',
            'email' => null,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('member.login.attempt'), [
            'login' => '0195168839',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('member.dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
