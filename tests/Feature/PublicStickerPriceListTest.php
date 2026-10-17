<?php

namespace Tests\Feature;

use App\Models\Discount;
use App\Models\PriceSetting;
use App\Models\Setting;
use App\Models\StickerSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicStickerPriceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_view_live_prices_even_while_main_site_is_under_construction(): void
    {
        Setting::setValue('under_construction', '1');
        Setting::setValue('minimum_a3_sheets_without_design', '4');
        $size = StickerSize::create([
            'name' => '3cm', 'shape' => 'Bulat', 'width_cm' => 3, 'height_cm' => 3,
            'price' => 0, 'qty_per_a3' => 100, 'show' => true, 'is_active' => true,
        ]);
        StickerSize::create([
            'name' => 'Hidden', 'price' => 0, 'qty_per_a3' => 100,
            'show' => false, 'is_active' => true,
        ]);
        foreach ([['Petak', 'Segi Empat Sama', 3], ['Bulat besar', 'Bulat', 11], ['Bulat kecil', 'Bulat', 0.5]] as [$name, $shape, $width]) {
            StickerSize::create([
                'name' => $name, 'shape' => $shape, 'width_cm' => $width,
                'price' => 0, 'qty_per_a3' => 100, 'show' => true, 'is_active' => true,
            ]);
        }
        PriceSetting::create([
            'sticker_type' => 'Mirrorcote', 'qty_from' => 1, 'qty_to' => 9,
            'price_per_a3' => 10, 'is_active' => true,
        ]);
        PriceSetting::create([
            'sticker_type' => 'Mirrorcote', 'qty_from' => 10, 'qty_to' => null,
            'price_per_a3' => 8, 'is_active' => true,
        ]);
        Discount::create([
            'name' => 'Promosi', 'sticker_size_id' => $size->id,
            'type' => 'percentage', 'value' => 10, 'min_qty' => 1000, 'is_active' => true,
        ]);

        $this->get(route('sticker.price-list'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/StickerPriceList')
            ->where('minimumSheets', 4)
            ->where('adminPhone', '601169409606')
            ->where('shipping.peninsular', 7)
            ->where('shipping.sabahSarawak', 12)
            ->where('shipping.freeThreshold', 150)
            ->has('tables', 1)
            ->has('tables.0.rows', 1)
            ->where('tables.0.rows.0.name', '3cm')
            ->where('tables.0.rows.0.prices.0.original', 40)
            ->where('tables.0.rows.0.prices.0.discounted', 40)
            ->where('tables.0.rows.0.prices.5.original', 80)
            ->where('tables.0.rows.0.prices.5.discounted', 72)
        );
    }

    public function test_empty_price_settings_do_not_prevent_public_access(): void
    {
        $this->get(route('sticker.price-list'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/StickerPriceList')->has('tables', 0)
        );
    }

    public function test_missing_price_tier_returns_null_instead_of_a_misleading_price(): void
    {
        StickerSize::create([
            'name' => '5cm', 'shape' => 'Bulat', 'width_cm' => 5, 'price' => 0, 'qty_per_a3' => 20,
            'show' => true, 'is_active' => true,
        ]);
        PriceSetting::create([
            'sticker_type' => 'Mirrorcote', 'qty_from' => 1, 'qty_to' => 3,
            'price_per_a3' => 10, 'is_active' => true,
        ]);

        $this->get(route('sticker.price-list'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/StickerPriceList')->where('tables.0.rows.0.prices.0', null)
        );
    }
}
