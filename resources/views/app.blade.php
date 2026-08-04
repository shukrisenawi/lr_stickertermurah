<!DOCTYPE html>
<html lang="ms" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="StickerTermurah - Pakar cetakan sticker mirrorcote berkualiti tinggi untuk jenama, produk dan perniagaan anda di seluruh Malaysia.">
    <title inertia>StickerTermurah</title>
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
