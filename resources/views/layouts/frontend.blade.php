<!doctype html>
<html lang="ms" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sticker Mirrorcote') | StickerTermurah</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-baru.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-full bg-white text-slate-900 antialiased" x-data="{ mobileMenuOpen: false }">

    {{-- Header --}}
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100">
        <div class="mx-auto flex h-[72px] max-w-[1280px] items-center justify-between px-4 lg:px-8">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                <img src="{{ asset('images/logo-baru.png') }}" alt="StickerTermurah" class="h-12 w-auto">
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden lg:flex items-center gap-8">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-brand-600">Home</a>
                <a href="#pilih-design" class="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Pilih Design</a>
                <a href="#harga" class="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Harga</a>
                <a href="#testimoni" class="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Testimoni</a>
                <a href="#cara-tempah" class="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Tentang Kami</a>
                <a href="#hubungi-kami" class="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Hubungi Kami</a>
            </nav>

            {{-- CTA & Mobile Toggle --}}
            <div class="flex items-center gap-3">
                <a href="https://wa.me/601169409606" target="_blank" class="hidden sm:inline-flex items-center gap-2 rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition shadow-lg shadow-brand-600/20">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.82.49 3.53 1.35 5.01L2 22l5.09-1.33A9.961 9.961 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm0 18c-1.64 0-3.17-.49-4.46-1.33l-.32-.19-2.98.78.8-2.9-.21-.33A7.96 7.96 0 014 12c0-4.41 3.59-8 8-8s8 3.59 8 8-3.59 8-8 8zm4.41-5.89c-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18-.71-.63-1.19-1.41-1.33-1.65-.14-.24-.02-.37.1-.49.1-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.2-.46-.4-.4-.54-.41-.14 0-.3-.02-.46-.02-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.59 4.12 3.63.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.46-.07 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28z"/></svg>
                    011-69409606
                </a>
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="lg:hidden rounded-xl border border-slate-200 p-2 text-slate-600">
                    <svg class="h-5 w-5" x-show="!mobileMenuOpen" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg class="h-5 w-5" x-show="mobileMenuOpen" x-cloak fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-cloak x-show="mobileMenuOpen" x-transition class="border-t border-slate-100 bg-white px-4 py-4 lg:hidden">
            <div class="space-y-1">
                <a href="{{ route('home') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold text-brand-600 bg-brand-50">Home</a>
                <a href="#pilih-design" @click="mobileMenuOpen = false" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Pilih Design</a>
                <a href="#harga" @click="mobileMenuOpen = false" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Harga</a>
                <a href="#testimoni" @click="mobileMenuOpen = false" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Testimoni</a>
                <a href="#cara-tempah" @click="mobileMenuOpen = false" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Tentang Kami</a>
                <a href="#hubungi-kami" @click="mobileMenuOpen = false" class="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Hubungi Kami</a>
                <a href="https://wa.me/601169409606" target="_blank" class="mt-2 flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.82.49 3.53 1.35 5.01L2 22l5.09-1.33A9.961 9.961 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm0 18c-1.64 0-3.17-.49-4.46-1.33l-.32-.19-2.98.78.8-2.9-.21-.33A7.96 7.96 0 014 12c0-4.41 3.59-8 8-8s8 3.59 8 8-3.59 8-8 8z"/></svg>
                    WhatsApp: 011-69409606
                </a>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main>
        @if(session('success'))
            <div class="mx-auto max-w-[1280px] px-4 pt-6 lg:px-8">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="mx-auto max-w-[1280px] px-4 pt-6 lg:px-8">
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4">
                    <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-[#0f172a] text-white">
        <div class="mx-auto max-w-[1280px] px-4 py-12 lg:px-8">
            <div class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-4">
                {{-- Brand --}}
                <div>
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo-baru.png') }}" alt="StickerTermurah" class="h-14 w-auto">
                        <div>
                            <p class="text-lg font-bold text-white">StickerTermurah</p>
                            <p class="text-xs text-slate-400">Printing Studio</p>
                        </div>
                    </div>
                    <p class="mt-4 text-sm leading-relaxed text-slate-400">
                        Kami hanya pakar dalam cetakan sticker mirrorcote berkualiti tinggi untuk jenama, produk & perniagaan anda.
                    </p>
                    <div class="mt-5 flex items-center gap-3">
                        <a href="#" class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-800 text-slate-300 hover:bg-brand-600 hover:text-white transition">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                        </a>
                        <a href="#" class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-800 text-slate-300 hover:bg-brand-600 hover:text-white transition">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a href="#" class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-800 text-slate-300 hover:bg-brand-600 hover:text-white transition">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.88-2.5c0-1.55 1.29-2.82 2.88-2.82.38 0 .74.08 1.08.22v-3.5a6.37 6.37 0 00-1.08-.1A6.34 6.34 0 005 15.92a6.34 6.34 0 006.33 6.33 6.34 6.34 0 006.33-6.33V8.83a8.26 8.26 0 004.83 1.55V6.87a4.85 4.85 0 01-2.9-.18z"/></svg>
                        </a>
                        <a href="#" class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-800 text-slate-300 hover:bg-brand-600 hover:text-white transition">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.234-.548.234l.188-2.623 5.18-4.688c.225-.2-.05-.314-.348-.114l-6.4 4.033-2.76-.862c-.598-.187-.608-.596.126-.883l10.79-4.176c.498-.187.932.114.77.907z"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h5 class="mb-4 text-sm font-bold text-white">Pautan Pantas</h5>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li><a href="{{ route('home') }}" class="hover:text-brand-400 transition">Home</a></li>
                        <li><a href="#pilih-design" class="hover:text-brand-400 transition">Pilih Design</a></li>
                        <li><a href="#harga" class="hover:text-brand-400 transition">Harga</a></li>
                        <li><a href="#testimoni" class="hover:text-brand-400 transition">Testimoni</a></li>
                    </ul>
                </div>

                {{-- Info --}}
                <div>
                    <h5 class="mb-4 text-sm font-bold text-white">Maklumat</h5>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li><a href="#cara-tempah" class="hover:text-brand-400 transition">Tentang Kami</a></li>
                        <li><a href="#testimoni" class="hover:text-brand-400 transition">Testimoni</a></li>
                        <li><a href="#cara-tempah" class="hover:text-brand-400 transition">Cara Tempah</a></li>
                        <li><a href="#" class="hover:text-brand-400 transition">Soalan Lazim</a></li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h5 class="mb-4 text-sm font-bold text-white">Hubungi Kami</h5>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            011-69409606
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            stickertermurah@gmail.com
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Seluruh Malaysia
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Pos laju / Courier
                        </li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 border-t border-slate-800 pt-6 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} StickerTermurah. All rights reserved.
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
