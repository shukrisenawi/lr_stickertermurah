<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_exposes_indexable_seo_metadata(): void
    {
        $this->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('seo.title', 'Cetak Sticker Mirrorcote Murah di Malaysia')
                ->where('seo.keywords', 'sticker murah, cetak sticker murah, sticker mirrorcote, sticker custom, sticker label, printing sticker Malaysia, tempah sticker')
                ->where('seo.robots', 'index, follow')
                ->where('seo.canonical', route('home'))
                ->has('seo.structured_data.@graph', 3));
    }

    public function test_homepage_renders_seo_metadata_in_initial_html(): void
    {
        $this->get(route('home'))
            ->assertSee('<title inertia>Cetak Sticker Mirrorcote Murah di Malaysia | StickerTermurah</title>', false)
            ->assertSee('<meta inertia="keywords" name="keywords" content="sticker murah, cetak sticker murah, sticker mirrorcote, sticker custom, sticker label, printing sticker Malaysia, tempah sticker">', false)
            ->assertSee('<meta inertia="robots" name="robots" content="index, follow">', false)
            ->assertSee('<link inertia="canonical" rel="canonical" href="'.e(route('home')).'">', false)
            ->assertSee('application/ld+json', false);
    }

    public function test_robots_txt_points_to_sitemap_and_blocks_private_areas(): void
    {
        $this->get(route('seo.robots'))
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /ahli')
            ->assertSee('Sitemap: '.route('seo.sitemap'));
    }

    public function test_sitemap_contains_public_pages_only(): void
    {
        $response = $this->get(route('seo.sitemap'));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>'.e(route('home')).'</loc>', false)
            ->assertSee('<loc>'.e(route('price.checker')).'</loc>', false)
            ->assertDontSee('/admin');

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $response->getContent());
        $this->assertStringNotContainsString('\\n', $response->getContent());
    }
}
