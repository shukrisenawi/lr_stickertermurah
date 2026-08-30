<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GoogleAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use Tests\TestCase;

class AdminGoogleAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_google_analytics_hub(): void
    {
        Config::set([
            'services.google_analytics.measurement_id' => 'G-TEST123',
            'services.google_analytics.property_id' => '123456789',
            'services.google_analytics.project_id' => 'stickertermurah',
            'services.google_analytics.credentials' => __FILE__,
        ]);

        $report = [
            'generatedAt' => '2026-08-30T12:00:00+08:00',
            'dateRange' => ['start' => '2026-08-01', 'end' => '2026-08-30'],
            'summary' => [
                'activeUsers' => 120,
                'newUsers' => 80,
                'sessions' => 150,
                'engagedSessions' => 90,
                'eventCount' => 640,
                'screenPageViews' => 300,
            ],
            'trend' => [],
            'topPages' => [],
            'topSources' => [],
            'regions' => [],
            'ageBrackets' => [],
            'realtimeActiveUsers' => 4,
        ];
        $googleAnalytics = Mockery::mock(GoogleAnalyticsService::class);
        $googleAnalytics->shouldReceive('report')->once()->with(false)->andReturn($report);
        $this->app->instance(GoogleAnalyticsService::class, $googleAnalytics);

        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.google-analytics.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/GoogleAnalytics/Index')
            ->where('configuration.measurementId', 'G-TEST123')
            ->where('configuration.propertyId', '123456789')
            ->where('configuration.projectConfigured', true)
            ->where('configuration.credentialsConfigured', true)
            ->where('report.summary.activeUsers', 120)
            ->where('report.realtimeActiveUsers', 4)
            ->where('reportError', null)
        );
    }

    public function test_missing_google_credentials_show_report_error(): void
    {
        Config::set([
            'services.google_analytics.measurement_id' => 'G-TEST123',
            'services.google_analytics.property_id' => '123456789',
            'services.google_analytics.project_id' => 'stickertermurah',
            'services.google_analytics.credentials' => base_path('missing-analytics-credentials.json'),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.google-analytics.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('configuration.credentialsConfigured', false)
                ->where('report', null)
                ->where('reportError', 'Fail credential Google tidak ditemui atau tidak boleh dibaca oleh server.')
            );
    }

    public function test_google_analytics_hub_requires_admin_access(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($customer)
            ->get(route('admin.google-analytics.index'))
            ->assertRedirect(route('admin.login'));
    }
}
