<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\StickerDesign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDesignSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_by_design_name_including_inactive_designs(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Makanan', 'slug' => 'makanan']);
        foreach (['MK_001', 'MK_002', 'MKX001'] as $name) {
            StickerDesign::create(['category_id' => $category->id, 'name' => $name, 'is_active' => false, 'image_path' => 'designs/sample.webp']);
        }
        $this->actingAs($admin)->getJson(route('admin.designs.search', ['q' => 'MK_00']))
            ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('data.0.name', 'MK_001')
            ->assertJsonPath('data.0.category', 'Makanan')->assertJsonPath('data.0.is_active', false)
            ->assertJsonStructure(['data' => [['id', 'name', 'image']]]);
        $this->getJson(route('admin.designs.search', ['q' => 'missing']))->assertOk()->assertExactJson(['data' => []]);
        $this->getJson(route('admin.designs.search', ['q' => ' ']))->assertOk()->assertExactJson(['data' => []]);
        $this->getJson(route('admin.designs.search', ['q' => str_repeat('x', 101)]))->assertUnprocessable();
    }

    public function test_search_is_limited_to_twelve_results(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Makanan', 'slug' => 'makanan']);
        for ($index = 1; $index <= 15; $index++) {
            StickerDesign::create(['category_id' => $category->id, 'name' => "MK_{$index}"]);
        }
        $this->actingAs($admin)->getJson(route('admin.designs.search', ['q' => 'MK']))->assertOk()->assertJsonCount(12, 'data');
    }

    public function test_non_admin_cannot_search_designs(): void
    {
        $this->getJson(route('admin.designs.search', ['q' => 'MK']))->assertUnauthorized();
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->getJson(route('admin.designs.search', ['q' => 'MK']))->assertRedirect(route('admin.login'));
    }
}
