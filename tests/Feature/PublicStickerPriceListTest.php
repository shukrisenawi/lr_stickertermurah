<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Discount;
use App\Models\PriceSetting;
use App\Models\Setting;
use App\Models\StickerDesign;
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
        foreach ([['Petak', 'Segi Empat Sama', 3], ['Bulat besar', 'Bulat', 11], ['Bulat kecil', 'Bulat', 2], ['Bulat perpuluhan', 'Bulat', 3.5]] as [$name, $shape, $width]) {
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
            ->where('tables.0.rows.0.prices.0', null)
            ->where('tables.0.rows.0.prices.1', null)
            ->where('tables.0.rows.0.prices.2', null)
            ->where('tables.0.rows.0.prices.3.original', 50)
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

    public function test_small_round_sizes_only_show_prices_when_quantity_meets_minimum_sheets(): void
    {
        Setting::setValue('minimum_a3_sheets_without_design', '3');
        foreach ([3 => 100, 4 => 60] as $width => $quantityPerSheet) {
            StickerSize::create([
                'name' => "{$width}cm", 'shape' => 'Bulat', 'width_cm' => $width,
                'height_cm' => $width, 'price' => 0, 'qty_per_a3' => $quantityPerSheet,
                'show' => true, 'is_active' => true,
            ]);
        }
        PriceSetting::create([
            'sticker_type' => 'Mirrorcote', 'qty_from' => 1, 'qty_to' => null,
            'price_per_a3' => 10, 'is_active' => true,
        ]);

        $this->get(route('sticker.price-list'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('tables.0.rows.0.prices.0', null)
            ->where('tables.0.rows.0.prices.1', null)
            ->where('tables.0.rows.0.prices.2.original', 30)
            ->where('tables.0.rows.1.prices.0', null)
            ->where('tables.0.rows.1.prices.1.original', 40)
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

    public function test_public_calculator_uses_pricing_service_and_matches_shape(): void
    {
        Setting::setValue('under_construction', '1');
        Setting::setValue('minimum_a3_sheets_without_design', '3');
        StickerSize::create([
            'name' => '3cm', 'shape' => 'Bulat', 'width_cm' => 3, 'height_cm' => 3,
            'price' => 0, 'qty_per_a3' => 100, 'show' => true, 'is_active' => true,
        ]);
        PriceSetting::create([
            'sticker_type' => 'Mirrorcote', 'qty_from' => 1, 'qty_to' => null,
            'price_per_a3' => 10, 'is_active' => true,
        ]);
        $input = ['shape' => 'Bulat', 'width' => 3, 'height' => 3, 'quantity' => 100, 'type' => 'Mirrorcote', 'has_design' => 0];
        $this->getJson(route('sticker.quote', $input))->assertOk()
            ->assertJsonPath('price.a3_sheets', 3)->assertJsonPath('price.line_total', 30);
        $this->getJson(route('sticker.quote', [...$input, 'has_design' => 1]))->assertOk()
            ->assertJsonPath('price.a3_sheets', 1)->assertJsonPath('price.line_total', 10);
        $this->getJson(route('sticker.quote', [...$input, 'shape' => 'Petak']))->assertOk()->assertJsonPath('price', null);
        $this->getJson(route('sticker.quote', [...$input, 'width' => 4]))->assertOk()->assertJsonPath('price', null);
        $this->getJson(route('sticker.quote', [...$input, 'quantity' => -1]))->assertUnprocessable();
    }

    public function test_catalog_is_public_and_loads_twenty_designs_per_request(): void
    {
        Setting::setValue('under_construction', '1');
        $category = Category::create(['name' => 'Katalog', 'slug' => 'katalog', 'is_active' => true]);
        for ($index = 1; $index <= 21; $index++) {
            StickerDesign::create(['name' => "Design {$index}", 'category_id' => $category->id, 'is_active' => true]);
        }
        StickerDesign::create(['name' => 'Hidden', 'category_id' => $category->id, 'is_active' => false]);
        $first = $this->getJson(route('sticker.catalog', ['limit' => 20]))->assertOk()
            ->assertJsonCount(20, 'data')->assertJsonPath('meta.total', 21)->assertJsonPath('meta.has_more', true);
        $last = $this->getJson(route('sticker.catalog', ['limit' => 20, 'offset' => 20]))->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('meta.has_more', false);
        $this->assertNotContains($last->json('data.0.id'), array_column($first->json('data'), 'id'));
        $this->getJson(route('sticker.catalog', ['category' => 'Katalog', 'random' => 1, 'limit' => 20]))
            ->assertOk()->assertJsonCount(20, 'data')->assertJsonPath('categories.0', 'Katalog')
            ->assertJsonPath('meta.total', 21)->assertJsonPath('data.0.category', 'Katalog');
        $this->getJson(route('sticker.catalog', ['category' => 'Tiada', 'random' => 1]))
            ->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('meta.total', 0);
    }
}
