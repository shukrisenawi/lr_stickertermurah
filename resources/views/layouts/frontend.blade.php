<!doctype html>
<html lang="ms" class="h-full scroll-smooth bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sticker Mirrorcote') | StickerTermurah</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-baru.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="frontend-shell min-h-full text-slate-900 antialiased" x-data="{ mobileMenuOpen: false }">
    <header class="frontend-glass-nav fixed inset-x-0 top-0 z-50">
        <div class="mx-auto flex h-[72px] max-w-[1440px] items-center justify-between gap-4 px-4 lg:px-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-white shadow-lg shadow-brand-600/20">
                        <img src="{{ asset('images/logo-baru.png') }}" alt="StickerTermurah" class="h-8 w-8 object-contain">
                    </div>
                    <div>
                        <p class="text-lg font-bold leading-none text-slate-900">StickerTermurah</p>
                        <p class="mt-1 text-[10px] uppercase tracking-[0.24em] text-slate-500">Customer Suite</p>
                    </div>
                </a>
            </div>

            <div class="hidden flex-1 items-center justify-center md:flex">
                <div class="relative w-full max-w-xs">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                        </svg>
                    </div>
                    <input type="search" placeholder="Cari halaman..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-brand-300 focus:ring-2 focus:ring-brand-100">
                </div>
            </div>

            <nav class="hidden items-center gap-8 md:flex">
                <a href="{{ route('home') }}" class="border-b-2 {{ request()->routeIs('home') ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-900' }} pb-1 text-sm font-semibold">Overview</a>
                <a href="{{ route('orders.lookup-form') }}" class="border-b-2 {{ request()->routeIs('orders.lookup-form') ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-900' }} pb-1 text-sm font-semibold">Semak Order</a>
                @auth
                    @if(!auth()->user()?->is_admin)
                        <a href="{{ route('member.dashboard') }}" class="border-b-2 {{ request()->routeIs('member.*') ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-900' }} pb-1 text-sm font-semibold">Akaun</a>
                    @endif
                @endauth
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                @auth
                    @if(!auth()->user()?->is_admin)
                        <a href="{{ route('orders.create') }}" class="frontend-btn-primary px-4 py-2.5">Tempah</a>
                        <div class="flex items-center gap-3 border-l border-slate-200 pl-4">
                            <div class="text-right">
                                <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name ?? auth()->user()->email }}</p>
                                <p class="text-xs text-slate-500">Member</p>
                            </div>
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white">
                                {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email ?? 'M', 0, 1)) }}
                            </div>
                        </div>
                        <form method="post" action="{{ route('member.logout') }}">
                            @csrf
                            <button type="submit" class="frontend-btn-secondary px-4 py-2.5">Logout</button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('member.login') }}" class="frontend-btn-primary px-4 py-2.5">Login Ahli</a>
                @endauth
                <a href="{{ route('admin.login') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-500 transition hover:bg-slate-50 hover:text-slate-900">Admin</a>
            </div>

            <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="rounded-xl border border-slate-200 p-2 text-slate-600 md:hidden">
                <svg class="h-5 w-5" x-show="!mobileMenuOpen" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg class="h-5 w-5" x-show="mobileMenuOpen" x-cloak fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div x-cloak x-show="mobileMenuOpen" x-transition class="border-t border-slate-200 bg-white px-4 py-4 md:hidden">
            <div class="space-y-2">
                <a href="{{ route('home') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">Overview</a>
                <a href="{{ route('orders.lookup-form') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">Semak Order</a>
                @auth
                    @if(!auth()->user()?->is_admin)
                        <a href="{{ route('member.dashboard') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">Akaun Saya</a>
                        <a href="{{ route('orders.create') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">Tempah Sticker</a>
                        <form method="post" action="{{ route('member.logout') }}">
                            @csrf
                            <button type="submit" class="block w-full rounded-xl px-4 py-3 text-left text-sm font-semibold text-slate-900 hover:bg-slate-50">Logout</button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('member.login') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">Login Ahli</a>
                @endauth
                <a href="{{ route('admin.login') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold text-slate-500 hover:bg-slate-50">Portal Admin</a>
            </div>
        </div>
    </header>

    <main class="min-h-screen pt-[72px]">
        @if(request()->routeIs('home'))
            <div class="py-0">
                @if(session('success'))
                    <div class="mx-auto max-w-[1440px] px-4 pt-8 lg:px-8">
                        <div class="frontend-flat-card border-emerald-200 bg-emerald-50 px-5 py-4">
                            <p class="text-sm text-emerald-900">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif
                @yield('content')
            </div>
        @else
            <div class="mx-auto max-w-[1440px] space-y-6 px-4 py-8 lg:px-8">
                @if(session('success'))
                    <div class="frontend-flat-card border-emerald-200 bg-emerald-50 px-5 py-4">
                        <p class="text-sm text-emerald-900">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="frontend-flat-card border-rose-200 bg-rose-50 px-5 py-4">
                        <p class="text-sm text-rose-900">{{ session('error') }}</p>
                    </div>
                @endif

                @yield('content')
            </div>
        @endif
    </main>

    @hasSection('page_footer')
        @yield('page_footer')
    @else
        <footer class="border-t border-slate-200 bg-white/90 py-10">
            <div class="mx-auto flex max-w-[1440px] flex-col items-center justify-between gap-6 px-4 text-sm text-slate-500 md:flex-row lg:px-8">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-baru.png') }}" alt="StickerTermurah" class="h-10 w-auto">
                    <div>
                        <p class="font-semibold text-slate-900">StickerTermurah</p>
                        <p class="text-xs">Printing Studio</p>
                    </div>
                </div>
                <p>&copy; {{ date('Y') }} StickerTermurah. Hak cipta terpelihara.</p>
                <div class="flex items-center gap-4">
                    <span>Privacy</span>
                    <span>Terms</span>
                </div>
            </div>
        </footer>
    @endif

    @stack('scripts')
</body>
</html>
