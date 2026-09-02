<?php

namespace Tests\Feature;

use App\Models\StickerSize;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStickerSizeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_multiple_sizes_with_shared_a3_quantity(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.sizes.store'), [
            'sizes' => [
                [
                    'name' => 'Label 3cm x 3cm',
                    'width_cm' => 3,
                    'height_cm' => 3,
                ],
                [
                    'name' => 'Label 4cm x 4cm',
                    'width_cm' => 4,
                    'height_cm' => 4,
                ],
            ],
            'shape' => 'Segi Empat',
            'qty_per_a3' => 100,
            'is_active' => true,
            'is_default' => false,
        ]);

        $response
            ->assertRedirect(route('admin.sizes.index'))
            ->assertSessionHas('success', '2 saiz berjaya ditambah.');

        $this->assertDatabaseHas('sticker_sizes', [
            'name' => 'Label 3cm x 3cm',
            'width_cm' => 3,
            'height_cm' => 3,
            'shape' => 'Segi Empat',
            'qty_per_a3' => 100,
            'is_active' => true,
            'show' => true,
        ]);
        $this->assertDatabaseHas('sticker_sizes', [
            'name' => 'Label 4cm x 4cm',
            'width_cm' => 4,
            'height_cm' => 4,
            'shape' => 'Segi Empat',
            'qty_per_a3' => 100,
            'is_active' => true,
            'show' => true,
        ]);
        $this->assertSame(2, StickerSize::query()->count());
    }

    public function test_admin_can_hide_a_size_from_the_price_comparison(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $size = StickerSize::query()->create([
            'name' => 'Label 5cm x 5cm',
            'width_cm' => 5,
            'height_cm' => 5,
            'shape' => 'Bulat',
            'qty_per_a3' => 40,
            'price' => 0,
            'is_active' => true,
            'is_default' => false,
            'show' => true,
        ]);

        $this->actingAs($admin)->put(route('admin.sizes.update', $size), [
            'name' => $size->name,
            'width_cm' => $size->width_cm,
            'height_cm' => $size->height_cm,
            'shape' => $size->shape,
            'qty_per_a3' => $size->qty_per_a3,
            'price' => $size->price,
            'is_active' => true,
            'is_default' => false,
            'show' => false,
        ])->assertRedirect(route('admin.sizes.index'));

        $this->assertDatabaseHas('sticker_sizes', [
            'id' => $size->id,
            'show' => false,
        ]);
    }
}
