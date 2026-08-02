<?php

namespace Tests\Feature;

use App\Models\PaymentSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProfileWhatsappTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_the_frontpage_whatsapp_number_from_profile(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'email' => 'admin-profile@example.com',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'admin_phone' => '0112222333',
        ]);

        $response->assertRedirect(route('admin.profile.edit'));
        $this->assertDatabaseHas('payment_settings', [
            'admin_phone' => '0112222333',
        ]);
    }
}
