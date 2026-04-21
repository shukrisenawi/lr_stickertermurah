<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class GoogleOAuthConfigurationTest extends TestCase
{
    public function test_admin_google_contacts_connect_redirects_back_with_error_when_google_oauth_config_is_missing(): void
    {
        config([
            'services.google.client_id' => '',
            'services.google.client_secret' => '',
        ]);

        $admin = new User([
            'name' => 'Admin Test',
            'email' => 'admin-google@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);
        $admin->id = 1;

        $response = $this->actingAs($admin)->get(route('admin.contacts.google.connect'));

        $response->assertRedirect(route('admin.contacts.google.index'));
        $response->assertSessionHas('error');
    }

    public function test_member_google_login_redirects_back_with_error_when_google_oauth_config_is_missing(): void
    {
        config([
            'services.google.client_id' => '',
            'services.google.client_secret' => '',
        ]);

        $response = $this->get(route('member.google.redirect'));

        $response->assertRedirect(route('member.login'));
        $response->assertSessionHas('error');
    }
}
