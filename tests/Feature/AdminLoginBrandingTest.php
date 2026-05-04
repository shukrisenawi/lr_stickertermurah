<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdminLoginBrandingTest extends TestCase
{
    public function test_admin_login_page_displays_stickertermurah_branding(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertOk();
        $response->assertSee('StickerTermurah');
        $response->assertDontSee('Precision Admin');
    }

    public function test_admin_login_page_prefills_seed_credentials_for_local_root_database(): void
    {
        Config::set('app.env', 'local');
        Config::set('database.connections.mysql.username', 'root');

        $response = $this->get(route('admin.login'));

        $response->assertOk();
        $response->assertSee('value="admin@sticker.com"', false);
        $response->assertSee('value="password"', false);
    }

    public function test_admin_login_page_does_not_prefill_seed_credentials_outside_local_root_database(): void
    {
        Config::set('app.env', 'production');
        Config::set('database.connections.mysql.username', 'forge');

        $response = $this->get(route('admin.login'));

        $response->assertOk();
        $response->assertDontSee('value="admin@sticker.com"', false);
        $response->assertDontSee('value="password"', false);
    }
}
