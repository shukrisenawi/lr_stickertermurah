<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\StickerPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminOrderSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_current_minimum_order_setting(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Setting::setValue(StickerPricingService::MIN_A3_SHEETS_SETTING_KEY, 5);

        $this->actingAs($admin)
            ->get(route('admin.settings.order.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Settings/Order')
                ->where('minimumA3SheetsWithoutDesign', 5)
            );
    }

    public function test_admin_can_update_minimum_order_setting(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.settings.order.update'), [
                'minimum_a3_sheets_without_design' => 6,
            ])
            ->assertRedirect(route('admin.settings.order.edit'))
            ->assertSessionHas('success', 'Minimum kertas order berjaya dikemaskini.');

        $this->assertSame('6', (string) Setting::getValue(StickerPricingService::MIN_A3_SHEETS_SETTING_KEY));
    }

    public function test_minimum_order_setting_rejects_zero(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.settings.order.update'), [
                'minimum_a3_sheets_without_design' => 0,
            ])
            ->assertSessionHasErrors('minimum_a3_sheets_without_design');
    }
}
