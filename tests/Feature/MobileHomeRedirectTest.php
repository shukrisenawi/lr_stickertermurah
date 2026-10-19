<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MobileHomeRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_visitors_are_redirected_to_sticker_prices(): void
    {
        Setting::setValue('under_construction', '0');

        foreach (['Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) Mobile', 'Mozilla/5.0 (Linux; Android 15) Mobile', 'Mozilla/5.0 (iPad; CPU OS 18_0 like Mac OS X)'] as $userAgent) {
            $this->withHeader('User-Agent', $userAgent)->get('/')
                ->assertRedirect(route('sticker.price-list'));
        }
    }

    public function test_mobile_client_hint_redirects_without_a_mobile_user_agent(): void
    {
        Setting::setValue('under_construction', '0');

        $this->withHeader('Sec-CH-UA-Mobile', '?1')->get('/')
            ->assertRedirect(route('sticker.price-list'));
    }

    public function test_mobile_redirect_works_while_under_construction(): void
    {
        Setting::setValue('under_construction', '1');

        $this->withHeader('User-Agent', 'Mozilla/5.0 (iPhone) Mobile')->get('/')
            ->assertRedirect(route('sticker.price-list'));
    }

    public function test_desktop_visitors_keep_the_home_page(): void
    {
        Setting::setValue('under_construction', '0');

        $this->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/130.0.0.0')
            ->get('/')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Public/Home'));
    }

    public function test_mobile_price_page_does_not_redirect_again(): void
    {
        $this->withHeader('User-Agent', 'Mozilla/5.0 (iPhone) Mobile')
            ->get(route('sticker.price-list'))->assertOk();
    }
}
