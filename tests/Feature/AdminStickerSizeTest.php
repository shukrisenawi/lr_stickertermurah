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
        ]);
        $this->assertDatabaseHas('sticker_sizes', [
            'name' => 'Label 4cm x 4cm',
            'width_cm' => 4,
            'height_cm' => 4,
            'shape' => 'Segi Empat',
            'qty_per_a3' => 100,
            'is_active' => true,
        ]);
        $this->assertSame(2, StickerSize::query()->count());
    }
}
