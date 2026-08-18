<?php

namespace Tests\Feature;

use App\Models\PaymentSetting;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class MemberPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_link_is_sent_to_member_email_using_phone_number(): void
    {
        Notification::fake();

        $member = User::factory()->create([
            'email' => 'member@example.com',
            'no_tel' => '60112222333',
            'is_admin' => false,
        ]);

        $response = $this->from(route('password.request'))->post(route('password.email'), [
            'no_tel' => '0112222333',
        ]);

        $response->assertRedirect(route('password.request'))
            ->assertSessionHas('success', 'Pautan reset kata laluan telah dihantar ke email anda.');

        Notification::assertSentTo($member, ResetPassword::class);
    }

    public function test_member_without_email_is_redirected_to_company_whatsapp(): void
    {
        PaymentSetting::query()->create(['admin_phone' => '0112222333']);

        User::factory()->create([
            'email' => null,
            'no_tel' => '60113333444',
            'is_admin' => false,
        ]);

        $response = $this->withHeader('X-Inertia', 'true')->post(route('password.email'), [
            'no_tel' => '0113333444',
        ]);

        $response->assertStatus(409);
        $this->assertStringStartsWith(
            'https://wa.me/60112222333?text=',
            $response->headers->get('X-Inertia-Location') ?? '',
        );
    }

    public function test_member_can_reset_password_with_valid_email_token(): void
    {
        $member = User::factory()->create([
            'email' => 'reset@example.com',
            'no_tel' => '60114444555',
            'password' => Hash::make('password-lama'),
            'must_change_password' => true,
            'is_admin' => false,
        ]);
        $token = Password::createToken($member);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $member->email,
            'password' => 'abc123',
            'password_confirmation' => 'abc123',
        ]);

        $response->assertRedirect(route('member.login'))
            ->assertSessionHas('success', 'Kata laluan berjaya ditetapkan semula. Sila login.');

        $member->refresh();
        $this->assertTrue(Hash::check('abc123', $member->password));
        $this->assertFalse($member->must_change_password);
    }

    public function test_unknown_phone_does_not_send_a_reset_notification(): void
    {
        Notification::fake();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['no_tel' => '0119999888'])
            ->assertSessionHasErrors(['no_tel']);

        Notification::assertNothingSent();
    }
}
