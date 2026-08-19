<?php

namespace Tests\Feature;

use App\Models\PaymentSetting;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_policy_and_terms_pages_are_public_during_under_construction(): void
    {
        Setting::setValue('under_construction', '1');
        PaymentSetting::query()->create(['admin_email' => 'contact@sticker.test']);

        $this->get(route('privacy-policy'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/PrivacyPolicy')
                ->where('app.admin_email', 'contact@sticker.test'));

        $this->get(route('terms-of-service'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/TermsOfService')
                ->where('app.admin_email', 'contact@sticker.test'));
    }
}
