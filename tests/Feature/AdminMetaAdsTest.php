<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminMetaAdsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_meta_campaigns_and_insights(): void
    {
        $this->configureMeta();
        Http::fake([
            'https://graph.facebook.com/v25.0/act_123/campaigns*' => Http::response([
                'data' => [[
                    'id' => '456',
                    'name' => 'Promo Sticker',
                    'objective' => 'OUTCOME_TRAFFIC',
                    'status' => 'PAUSED',
                    'effective_status' => 'PAUSED',
                    'created_time' => '2026-08-31T04:00:00+0000',
                ]],
            ]),
            'https://graph.facebook.com/v25.0/act_123/insights*' => Http::response([
                'data' => [[
                    'campaign_id' => '456',
                    'campaign_name' => 'Promo Sticker',
                    'impressions' => '1200',
                    'reach' => '900',
                    'clicks' => '48',
                    'spend' => '25.50',
                    'ctr' => '4.00',
                    'cpc' => '0.53',
                ]],
            ]),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.meta-ads.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/MetaAds/Index')
            ->where('configuration.configured', true)
            ->where('configuration.adAccountId', 'act_123')
            ->where('campaigns.0.id', '456')
            ->where('campaigns.0.insights.spend', 25.5)
            ->where('summary.campaigns', 1)
            ->where('summary.activeCampaigns', 0)
            ->where('summary.impressions', 1200)
            ->where('summary.clicks', 48)
            ->where('reportError', null)
        );

        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    public function test_missing_meta_configuration_is_shown_without_calling_api(): void
    {
        Config::set([
            'services.meta_ads.app_id' => null,
            'services.meta_ads.access_token' => null,
            'services.meta_ads.ad_account_id' => null,
        ]);
        Http::fake();

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.meta-ads.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/MetaAds/Index')
                ->where('configuration.configured', false)
                ->where('reportError', 'Tetapkan META_ACCESS_TOKEN dalam .env untuk menyambung Meta Ads.')
            );

        Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'graph.facebook.com'));
    }

    public function test_admin_can_create_a_paused_campaign(): void
    {
        $this->configureMeta();
        Http::fake([
            'https://graph.facebook.com/v25.0/act_123/campaigns' => Http::response(['id' => '789']),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.meta-ads.campaigns.store'), [
                'name' => 'Kempen Traffic September',
                'objective' => 'OUTCOME_TRAFFIC',
            ])
            ->assertRedirect(route('admin.meta-ads.index'))
            ->assertSessionHas('success', 'Kempen Meta berjaya dicipta sebagai PAUSED.');

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return $request->method() === 'POST'
                && $request->url() === 'https://graph.facebook.com/v25.0/act_123/campaigns'
                && $data['name'] === 'Kempen Traffic September'
                && $data['objective'] === 'OUTCOME_TRAFFIC'
                && $data['status'] === 'PAUSED'
                && $data['special_ad_categories'] === '[]';
        });
    }

    public function test_admin_can_update_campaign_status(): void
    {
        $this->configureMeta();
        Http::fake([
            'https://graph.facebook.com/v25.0/456' => Http::response(['success' => true]),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.meta-ads.campaigns.update', '456'), [
                'name' => 'Promo Sticker Live',
                'status' => 'PAUSED',
            ])
            ->assertRedirect(route('admin.meta-ads.index'))
            ->assertSessionHas('success', 'Kempen Meta dijeda.');

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return $request->method() === 'POST'
                && $request->url() === 'https://graph.facebook.com/v25.0/456'
                && $data['name'] === 'Promo Sticker Live'
                && $data['status'] === 'PAUSED';
        });
    }

    public function test_meta_ads_requires_admin_access(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($customer)
            ->get(route('admin.meta-ads.index'))
            ->assertRedirect(route('admin.login'));
    }

    private function configureMeta(): void
    {
        Config::set([
            'services.meta_ads.app_id' => 'app-123',
            'services.meta_ads.access_token' => 'test-token',
            'services.meta_ads.ad_account_id' => 'act_123',
            'services.meta_ads.api_version' => 'v25.0',
            'services.meta_ads.base_url' => 'https://graph.facebook.com',
            'services.meta_ads.currency' => 'MYR',
        ]);
    }
}
