<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = [
            ['loc' => route('home'), 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['loc' => route('price.checker'), 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => route('testimonials.index'), 'changefreq' => 'weekly', 'priority' => '0.7'],
            ['loc' => route('privacy-policy'), 'changefreq' => 'yearly', 'priority' => '0.2'],
            ['loc' => route('terms-of-service'), 'changefreq' => 'yearly', 'priority' => '0.2'],
        ];

        $urlNodes = collect($urls)->map(function (array $url): string {
            $loc = htmlspecialchars($url['loc'], ENT_XML1 | ENT_COMPAT, 'UTF-8');

            return "\n    <url>\n        <loc>{$loc}</loc>\n        <changefreq>{$url['changefreq']}</changefreq>\n        <priority>{$url['priority']}</priority>\n    </url>";
        })->implode('');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
            "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">".
            $urlNodes.
            "\n</urlset>\n";

        return response($xml)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $content = implode(PHP_EOL, [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /ahli',
            'Disallow: /api',
            'Disallow: /auth/',
            'Disallow: /invoice/',
            'Disallow: /login',
            'Disallow: /order',
            'Disallow: /orders',
            'Disallow: /semak-order',
            'Sitemap: '.route('seo.sitemap'),
            '',
        ]);

        return response($content)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
