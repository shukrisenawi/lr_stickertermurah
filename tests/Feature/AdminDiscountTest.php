<?php

namespace Tests\Feature;

use App\Models\PriceSetting;
use App\Models\StickerSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_exact_final_price_for_a_size_and_quantity(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $size = StickerSize::query()->create([
            'name' => '4cm x 4cm',
            'width_cm' => 4,
            'height_cm' => 4,
            'price' => 0,
            'qty_per_a3' => 60,
            'is_active' => true,
            'show' => true,
        ]);
        PriceSetting::query()->create([
            'sticker_type' => 'Mirrorcote',
            'qty_from' => 1,
            'qty_to' => null,
            'price_per_a3' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.discounts.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Discounts/Create')
                ->where('stickerTypes.0', 'Mirrorcote')
                ->where('sizes.0.id', $size->id)
            );

        $this->actingAs($admin)
            ->post(route('admin.discounts.store'), [
                'name' => 'Mirrorcote 4cm 1000 pcs',
                'sticker_type' => 'Mirrorcote',
                'sticker_size_id' => $size->id,
                'min_qty' => 1000,
                'max_qty' => 1000,
                'type' => 'price',
                'value' => 100,
                'is_active' => true,
                'expired_at' => null,
            ])
            ->assertRedirect(route('admin.discounts.index'))
            ->assertSessionHas('success', 'Diskaun berjaya ditambah.');

        $this->assertDatabaseHas('discounts', [
            'name' => 'Mirrorcote 4cm 1000 pcs',
            'sticker_type' => 'Mirrorcote',
            'sticker_size_id' => $size->id,
            'min_qty' => 1000,
            'max_qty' => 1000,
            'type' => 'price',
            'value' => 100,
            'is_active' => true,
        ]);

        $this->get(route('price.checker'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('discounts.0.type', 'price')
                ->where('discounts.0.value', 100)
            );
    }

    public function test_admin_discount_rejects_an_unknown_discount_type(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.discounts.store'), [
                'name' => 'Diskaun Tidak Sah',
                'min_qty' => 1000,
                'type' => 'unknown',
                'value' => 100,
            ])
            ->assertSessionHasErrors('type');
    }
}
