<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia as Assert;
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
            'services.google_analytics.credentials' => 'C:\\credentials\\analytics.json',
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.google-analytics.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/GoogleAnalytics/Index')
            ->where('configuration.measurementId', 'G-TEST123')
            ->where('configuration.propertyId', '123456789')
            ->where('configuration.projectConfigured', true)
            ->where('configuration.credentialsConfigured', true)
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
