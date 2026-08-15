<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\StickerDesign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicDesignOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_designs_are_ordered_by_latest_upload(): void
    {
        [$older, $newer] = $this->createDesignsWithSameUploadTime();

        $this->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('designs.0.id', $newer->id)
                ->where('designs.1.id', $older->id)
            );
    }

    public function test_design_api_is_ordered_by_latest_upload(): void
    {
        [$older, $newer] = $this->createDesignsWithSameUploadTime();

        $this->getJson(route('api.designs.index'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $older->id);
    }

    /** @return array{0: StickerDesign, 1: StickerDesign} */
    private function createDesignsWithSameUploadTime(): array
    {
        $category = Category::query()->create([
            'name' => 'Makanan',
            'slug' => 'makanan',
            'prefix' => 'MK',
        ]);

        $older = StickerDesign::query()->create([
            'category_id' => $category->id,
            'name' => 'Design Lama',
            'slug' => 'design-lama',
            'tags' => [],
        ]);
        $newer = StickerDesign::query()->create([
            'category_id' => $category->id,
            'name' => 'Design Baru',
            'slug' => 'design-baru',
            'tags' => [],
        ]);

        $sameUploadTime = now()->startOfSecond();
        $older->forceFill(['created_at' => $sameUploadTime])->saveQuietly();
        $newer->forceFill(['created_at' => $sameUploadTime])->saveQuietly();

        return [$older, $newer];
    }
}
