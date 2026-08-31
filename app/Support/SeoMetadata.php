<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\Request;

final class SeoMetadata
{
    private const SITE_NAME = 'StickerTermurah';

    private const DEFAULT_DESCRIPTION = 'Tempah sticker mirrorcote berkualiti tinggi dengan harga berbaloi untuk jenama, produk dan perniagaan anda di seluruh Malaysia.';

    public const DEFAULT_KEYWORDS = 'sticker murah, cetak sticker murah, sticker mirrorcote, sticker custom, sticker label, printing sticker Malaysia, tempah sticker';

    /**
     * @return array<string, mixed>
     */
    public function for(Request $request, string $phone, string $email): array
    {
        $routeName = $request->route()?->getName();
        $page = match ($routeName) {
            'home' => [
                'title' => 'Cetak Sticker Mirrorcote Murah di Malaysia',
                'description' => 'Tempah sticker mirrorcote premium dengan design menarik, harga berbaloi dan penghantaran ke seluruh Malaysia.',
            ],
            'price.checker' => [
                'title' => 'Harga Sticker Mirrorcote Malaysia',
                'description' => 'Semak dan kira anggaran harga sticker mirrorcote mengikut saiz serta kuantiti dengan kalkulator harga StickerTermurah.',
            ],
            'testimonials.index' => [
                'title' => 'Testimoni Pelanggan StickerTermurah',
                'description' => 'Baca pengalaman pelanggan yang menggunakan perkhidmatan cetakan sticker StickerTermurah.',
            ],
            'privacy-policy' => [
                'title' => 'Polisi Privasi',
                'description' => 'Ketahui cara StickerTermurah mengumpul, menggunakan dan melindungi maklumat pelanggan.',
            ],
            'terms-of-service' => [
                'title' => 'Terma Perkhidmatan',
                'description' => 'Terma penggunaan laman web dan pembelian perkhidmatan cetakan sticker daripada StickerTermurah.',
            ],
            'orders.lookup-form' => [
                'title' => 'Semak Status Tempahan Sticker',
                'description' => 'Semak status tempahan sticker anda menggunakan nombor order.',
            ],
            'orders.create', 'member.orders.create', 'member.orders.repeat-form', 'admin.orders.create' => [
                'title' => 'Tempah Sticker',
                'description' => 'Buat tempahan sticker mirrorcote dengan memilih design, saiz dan kuantiti yang diperlukan.',
            ],
            default => [
                'title' => self::SITE_NAME,
                'description' => self::DEFAULT_DESCRIPTION,
            ],
        };

        $isIndexable = in_array($routeName, [
            'home',
            'price.checker',
            'testimonials.index',
            'privacy-policy',
            'terms-of-service',
        ], true) && ! $this->isUnderConstruction($routeName);

        $siteUrl = route('home');
        $structuredData = $routeName === 'home' && $isIndexable
            ? $this->structuredData($siteUrl, $page['description'], $phone, $email)
            : null;

        return [
            'title' => $page['title'],
            'description' => $page['description'],
            'keywords' => $this->keywords(),
            'robots' => $isIndexable ? 'index, follow' : 'noindex, nofollow',
            'canonical' => $this->canonicalUrl($routeName, $request),
            'site_name' => self::SITE_NAME,
            'og_type' => 'website',
            'og_image' => asset('images/logo-baru.webp'),
            'og_image_alt' => 'Logo StickerTermurah',
            'locale' => 'ms_MY',
            'structured_data' => $structuredData,
        ];
    }

    private function keywords(): string
    {
        try {
            return trim((string) Setting::getValue('seo_keywords', self::DEFAULT_KEYWORDS));
        } catch (\Throwable) {
            return self::DEFAULT_KEYWORDS;
        }
    }

    private function canonicalUrl(?string $routeName, Request $request): string
    {
        $canonicalRoute = match ($routeName) {
            'home' => 'home',
            'price.checker' => 'price.checker',
            'testimonials.index' => 'testimonials.index',
            'privacy-policy' => 'privacy-policy',
            'terms-of-service' => 'terms-of-service',
            default => null,
        };

        return $canonicalRoute ? route($canonicalRoute) : $request->url();
    }

    private function isUnderConstruction(?string $routeName): bool
    {
        if ($routeName !== 'home') {
            return false;
        }

        try {
            return Setting::getValue('under_construction', '0') === '1';
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function structuredData(string $siteUrl, string $description, string $phone, string $email): array
    {
        $organizationId = $siteUrl.'#organization';
        $contactPoint = [
            '@type' => 'ContactPoint',
            'contactType' => 'customer service',
            'areaServed' => 'MY',
            'availableLanguage' => ['ms', 'en'],
        ];

        if ($phone !== '') {
            $contactPoint['telephone'] = $this->internationalPhone($phone);
        }

        if ($email !== '') {
            $contactPoint['email'] = $email;
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => $organizationId,
                    'name' => self::SITE_NAME,
                    'url' => $siteUrl,
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('images/logo-baru.webp'),
                    ],
                    'description' => $description,
                    'areaServed' => [
                        '@type' => 'Country',
                        'name' => 'Malaysia',
                    ],
                    'contactPoint' => $contactPoint,
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $siteUrl.'#website',
                    'url' => $siteUrl,
                    'name' => self::SITE_NAME,
                    'inLanguage' => 'ms-MY',
                    'publisher' => ['@id' => $organizationId],
                ],
                [
                    '@type' => 'Service',
                    'name' => 'Cetakan sticker mirrorcote',
                    'serviceType' => 'Perkhidmatan cetakan sticker',
                    'provider' => ['@id' => $organizationId],
                    'areaServed' => [
                        '@type' => 'Country',
                        'name' => 'Malaysia',
                    ],
                ],
            ],
        ];
    }

    private function internationalPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        return str_starts_with($digits, '0') ? '+60'.substr($digits, 1) : '+'.$digits;
    }
}
