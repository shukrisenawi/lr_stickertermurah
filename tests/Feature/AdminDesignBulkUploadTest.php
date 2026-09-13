<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\StickerDesign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDesignBulkUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_bulk_design_upload_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.designs.bulk.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Designs/BulkCreate')
                ->where('maxFiles', 40)
            );
    }

    public function test_admin_can_upload_multiple_design_images(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::query()->create([
            'name' => 'Makanan',
            'slug' => 'makanan',
            'prefix' => 'MK',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.designs.bulk.store'), [
            'category_id' => $category->id,
            'images' => [
                UploadedFile::fake()->image('satu.jpg', 120, 80),
                UploadedFile::fake()->image('dua.png', 80, 120),
            ],
        ]);

        $response->assertRedirect(route('admin.designs.index'))
            ->assertSessionHas('success', '2 design berjaya ditambah secara pukal.');

        $designs = StickerDesign::query()->orderBy('name')->get();

        $this->assertCount(2, $designs);
        $this->assertSame(['MK_001', 'MK_002'], $designs->pluck('name')->all());

        foreach ($designs as $design) {
            $this->assertNotNull($design->image_path);
            $this->assertNotNull($design->mobile_image_path);
            $this->assertTrue(Storage::disk('public')->exists($design->image_path));
            $this->assertTrue(Storage::disk('public')->exists($design->mobile_image_path));
            $this->assertTrue(Storage::disk('local')->exists('Ori/'.$design->name.'.'.($design->name === 'MK_001' ? 'jpg' : 'png')));
        }
    }

    public function test_bulk_upload_rejects_more_than_forty_images(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::query()->create([
            'name' => 'Makanan',
            'slug' => 'makanan',
            'prefix' => 'MK',
        ]);
        $images = [];

        for ($index = 1; $index <= 41; $index++) {
            $images[] = UploadedFile::fake()->image("design-{$index}.jpg");
        }

        $this->actingAs($admin)
            ->post(route('admin.designs.bulk.store'), [
                'category_id' => $category->id,
                'images' => $images,
            ])
            ->assertSessionHasErrors('images');

        $this->assertDatabaseCount('sticker_designs', 0);
    }
}
