<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\StickerDesign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDesignBulkTagTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_a_hashtag_to_multiple_designs(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::query()->create([
            'name' => 'Makanan',
            'slug' => 'makanan',
            'prefix' => 'MK',
        ]);
        $first = StickerDesign::query()->create([
            'category_id' => $category->id,
            'name' => 'MK_001',
            'slug' => 'mk-001',
            'tags' => ['lama'],
        ]);
        $second = StickerDesign::query()->create([
            'category_id' => $category->id,
            'name' => 'MK_002',
            'slug' => 'mk-002',
            'tags' => [],
        ]);

        $response = $this->actingAs($admin)->post(route('admin.designs.bulk.tag'), [
            'design_ids' => [$first->id, $second->id],
            'hashtag' => '#Makanan',
        ]);

        $response->assertRedirect(route('admin.designs.index'));
        $this->assertSame(['lama', 'makanan'], StickerDesign::query()->findOrFail($first->id)->tags);
        $this->assertSame(['makanan'], StickerDesign::query()->findOrFail($second->id)->tags);
    }
}
