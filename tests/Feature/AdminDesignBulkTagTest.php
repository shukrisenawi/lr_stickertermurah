<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\StickerDesign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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

    public function test_admin_can_filter_designs_by_hashtag(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::query()->create([
            'name' => 'Makanan',
            'slug' => 'makanan',
            'prefix' => 'MK',
        ]);
        $matching = StickerDesign::query()->create([
            'category_id' => $category->id,
            'name' => 'MK_001',
            'slug' => 'mk-001',
            'tags' => ['makanan'],
        ]);
        StickerDesign::query()->create([
            'category_id' => $category->id,
            'name' => 'MK_002',
            'slug' => 'mk-002',
            'tags' => ['dessert'],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.designs.index', ['tag' => '#makanan']));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('activeTag', 'makanan')
            ->has('designs.data', 1)
            ->where('designs.data.0.id', $matching->id)
            ->where('availableTags.0', 'dessert')
        );
    }

    public function test_admin_can_rename_a_design_hashtag(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::query()->create([
            'name' => 'Makanan',
            'slug' => 'makanan',
            'prefix' => 'MK',
        ]);
        $design = StickerDesign::query()->create([
            'category_id' => $category->id,
            'name' => 'MK_001',
            'slug' => 'mk-001',
            'tags' => ['lama', 'lain'],
        ]);

        $response = $this->actingAs($admin)->put(route('admin.designs.tags.update', $design), [
            'tags' => ['#Baharu', 'lain'],
        ]);

        $response->assertRedirect(route('admin.designs.index'));
        $this->assertSame(['baharu', 'lain'], $design->refresh()->tags);
    }
}
