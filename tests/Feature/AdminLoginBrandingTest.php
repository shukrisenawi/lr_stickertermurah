<?php

namespace Tests\Feature;

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
}
