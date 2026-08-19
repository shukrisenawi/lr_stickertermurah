<!DOCTYPE html>
@php
    $seo = data_get($page ?? [], 'props.seo', []);
    $seoSiteName = $seo['site_name'] ?? 'StickerTermurah';
    $seoPageTitle = $seo['title'] ?? $seoSiteName;
    $seoTitle = $seoPageTitle === $seoSiteName ? $seoSiteName : $seoPageTitle.' | '.$seoSiteName;
    $seoDescription = $seo['description'] ?? 'Tempah sticker mirrorcote berkualiti tinggi dengan harga berbaloi untuk jenama, produk dan perniagaan anda di seluruh Malaysia.';
    $seoRobots = $seo['robots'] ?? 'noindex, nofollow';
    $seoCanonical = $seo['canonical'] ?? url('/');
    $seoImage = $seo['og_image'] ?? asset('images/logo-baru.webp');
    $seoImageAlt = $seo['og_image_alt'] ?? 'Logo StickerTermurah';
    $seoStructuredData = $seo['structured_data'] ?? null;
    $seoJsonLd = $seoStructuredData
        ? json_encode($seoStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
        : null;
@endphp
<html lang="ms" class="scroll-smooth">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-GJYSPKNHJ2"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-GJYSPKNHJ2');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta inertia="description" name="description" content="{{ $seoDescription }}">
    <meta inertia="author" name="author" content="{{ $seoSiteName }}">
    <meta inertia="robots" name="robots" content="{{ $seoRobots }}">
    <link inertia="canonical" rel="canonical" href="{{ $seoCanonical }}">
    <meta inertia="og:title" property="og:title" content="{{ $seoTitle }}">
    <meta inertia="og:description" property="og:description" content="{{ $seoDescription }}">
    <meta inertia="og:url" property="og:url" content="{{ $seoCanonical }}">
    <meta inertia="og:type" property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">
    <meta inertia="og:site_name" property="og:site_name" content="{{ $seoSiteName }}">
    <meta inertia="og:locale" property="og:locale" content="{{ $seo['locale'] ?? 'ms_MY' }}">
    <meta inertia="og:image" property="og:image" content="{{ $seoImage }}">
    <meta inertia="og:image:alt" property="og:image:alt" content="{{ $seoImageAlt }}">
    <meta inertia="twitter:card" name="twitter:card" content="summary_large_image">
    <meta inertia="twitter:title" name="twitter:title" content="{{ $seoTitle }}">
    <meta inertia="twitter:description" name="twitter:description" content="{{ $seoDescription }}">
    <meta inertia="twitter:image" name="twitter:image" content="{{ $seoImage }}">
    <meta name="theme-color" content="#d91c5c">
    <title inertia>{{ $seoTitle }}</title>
    @if ($seoJsonLd)
        <script inertia="structured-data" type="application/ld+json">{!! $seoJsonLd !!}</script>
    @endif
    <link rel="icon" type="image/webp" href="{{ asset('images/logo-baru.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @routes
    @viteReactRefresh
    @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
    @inertiaHead
</head>
<body class="min-h-full bg-white text-slate-900 antialiased">
    @inertia
</body>
</html>
