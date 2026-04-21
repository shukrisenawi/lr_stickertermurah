<!doctype html>
<html lang="ms" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sticker Mirrorcote') | StickerTermurah</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-baru.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <!-- Alpine.js for interactive elements -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }
        .glass-nav {
            background: rgba(246, 246, 249, 0.82);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }
    </style>
</head>
<body class="h-full bg-[#f6f6f9] antialiased font-sans flex flex-col selection:bg-brand-100 selection:text-brand-900 text-slate-900" x-data="{ mobileMenuOpen: false }">
    <!-- Navigation -->
    <header class="sticky top-0 z-50 w-full transition-all duration-300 glass-nav border-b border-white/60 shadow-[0_24px_60px_-36px_rgba(45,47,49,0.28)]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between gap-4">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <img src="{{ asset('images/logo-baru.png') }}" alt="StickerTermurah" class="h-11 w-auto drop-shadow-sm transition-transform duration-300 group-hover:scale-[1.03]">
                        <div class="hidden sm:block">
                            <p class="text-lg font-extrabold uppercase tracking-[0.22em] text-brand-700">Sticker Termurah</p>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Printing Studio</p>
                        </div>
                    </a>
                </div>
                
                <nav class="hidden md:flex items-center gap-x-1">
                    <a href="{{ route('home') }}#kategori" class="px-4 py-2 text-sm font-bold text-slate-600 hover:text-brand-600 transition-colors rounded-xl hover:bg-white/70">Kategori</a>
                    <a href="{{ route('home') }}#custom-order" class="px-4 py-2 text-sm font-bold text-slate-600 hover:text-brand-600 transition-colors rounded-xl hover:bg-white/70">Custom Order</a>
                    <a href="{{ route('home') }}#testimoni" class="px-4 py-2 text-sm font-bold text-slate-600 hover:text-brand-600 transition-colors rounded-xl hover:bg-white/70">Testimoni</a>
                    <a href="{{ route('home') }}#hubungi-kami" class="px-4 py-2 text-sm font-bold text-slate-600 hover:text-brand-600 transition-colors rounded-xl hover:bg-white/70">Hubungi Kami</a>
                    <a href="{{ route('orders.lookup-form') }}" class="px-4 py-2 text-sm font-bold {{ request()->routeIs('orders.lookup-form') ? 'text-brand-600' : 'text-slate-600 hover:text-brand-600' }} transition-colors rounded-xl hover:bg-white/70">Semak Order</a>
                    @auth
                        @if(!auth()->user()?->is_admin)
                            <a href="{{ route('member.dashboard') }}" class="px-4 py-2 text-sm font-bold {{ request()->routeIs('member.*') ? 'text-brand-600' : 'text-slate-600 hover:text-brand-600' }} transition-colors rounded-xl hover:bg-white/70">Laman Ahli</a>
                        @endif
                    @else
                        <a href="{{ route('member.login') }}" class="inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-brand-600/20 transition-all hover:bg-brand-700 active:scale-95">
                            <span class="material-symbols-outlined text-lg">person</span>
                            Login Ahli
                        </a>
                    @endauth
                    @auth
                        @if(!auth()->user()?->is_admin)
                            <form method="post" action="{{ route('member.logout') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-sm font-bold text-slate-600 hover:text-slate-900 transition-colors rounded-xl hover:bg-white/70">Logout</button>
                            </form>
                        @endif
                    @endauth
                    <a href="{{ route('admin.login') }}" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors rounded-xl hover:bg-white/70">Admin</a>
                </nav>

                <div class="flex items-center md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="inline-flex items-center justify-center p-2.5 rounded-2xl text-slate-700 hover:bg-white/80 transition-colors">
                        <span class="sr-only">Buka Menu</span>
                        <svg class="h-6 w-6" x-show="!mobileMenuOpen" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg class="h-6 w-6" x-show="mobileMenuOpen" x-cloak fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="mobileMenuOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-t border-white/60 bg-[#f9f9fc] shadow-xl">
            <div class="space-y-1 px-4 pt-2 pb-6">
                <a href="{{ route('home') }}#kategori" class="block rounded-xl px-4 py-3 text-base font-bold text-slate-900 hover:bg-white hover:text-brand-600 transition-all">Kategori</a>
                <a href="{{ route('home') }}#custom-order" class="block rounded-xl px-4 py-3 text-base font-bold text-slate-900 hover:bg-white hover:text-brand-600 transition-all">Custom Order</a>
                <a href="{{ route('home') }}#testimoni" class="block rounded-xl px-4 py-3 text-base font-bold text-slate-900 hover:bg-white hover:text-brand-600 transition-all">Testimoni</a>
                <a href="{{ route('home') }}#hubungi-kami" class="block rounded-xl px-4 py-3 text-base font-bold text-slate-900 hover:bg-white hover:text-brand-600 transition-all">Hubungi Kami</a>
                <a href="{{ auth()->check() ? route('orders.create') : route('member.register') }}" class="block rounded-xl px-4 py-3 text-base font-bold text-slate-900 hover:bg-white hover:text-brand-600 transition-all">Tempah Sticker</a>
                <a href="{{ route('orders.lookup-form') }}" class="block rounded-xl px-4 py-3 text-base font-bold text-slate-900 hover:bg-brand-50 hover:text-brand-600 transition-all">Semak Status</a>
                @auth
                    @if(!auth()->user()?->is_admin)
                        <a href="{{ route('member.dashboard') }}" class="block rounded-xl px-4 py-3 text-base font-bold text-slate-900 hover:bg-brand-50 hover:text-brand-600 transition-all">Laman Ahli</a>
                        <form method="post" action="{{ route('member.logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left rounded-xl px-4 py-3 text-base font-bold text-slate-900 hover:bg-brand-50 hover:text-brand-600 transition-all">Logout</button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('member.login') }}" class="block rounded-xl px-4 py-3 text-base font-bold text-slate-900 hover:bg-brand-50 hover:text-brand-600 transition-all">Login Ahli</a>
                @endauth
                <a href="{{ route('admin.login') }}" class="block rounded-xl px-4 py-3 text-base font-bold text-slate-500 hover:bg-slate-50 transition-all">Portal Admin</a>
            </div>
        </div>
    </header>

    <main class="flex-1">
        @if(request()->routeIs('home'))
            <div class="py-0">
                @if(session('success'))
                    <div class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
                        <div class="rounded-2xl bg-emerald-50 p-5 border border-emerald-200 shadow-sm shadow-emerald-500/5">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-emerald-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                @yield('content')
            </div>
        @else
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            @if(session('success'))
                <div class="mb-8 rounded-2xl bg-emerald-50 p-5 border border-emerald-200 shadow-sm shadow-emerald-500/5 transition-all hover:shadow-md animate-in fade-in slide-in-from-top-1">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                            <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-emerald-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-8 rounded-2xl bg-rose-50 p-5 border border-rose-200 shadow-sm shadow-rose-500/5 transition-all hover:shadow-md animate-in fade-in slide-in-from-top-1">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center">
                            <svg class="h-4 w-4 text-rose-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-rose-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif
            
            @yield('content')
        </div>
        @endif
    </main>

    @hasSection('page_footer')
        @yield('page_footer')
    @else
        <footer class="bg-white border-t border-slate-300 py-12">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center">
                        <img src="{{ asset('images/logo-baru.png') }}" alt="StickerTermurah" class="h-12 w-auto">
                    </div>
                    <p class="text-slate-500 text-xs font-medium">
                        &copy; {{ date('Y') }} StickerTermurah. Hak Cipta Terpelihara.
                    </p>
                    <div class="flex items-center gap-6">
                        <a href="#" class="text-slate-500 hover:text-brand-600 transition-colors text-xs font-bold uppercase tracking-widest">Syarat</a>
                        <a href="#" class="text-slate-500 hover:text-brand-600 transition-colors text-xs font-bold uppercase tracking-widest">Privasi</a>
                    </div>
                </div>
            </div>
        </footer>
    @endif

    @stack('scripts')
</body>
</html>

