<!doctype html>
<html lang="ms" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sticker Mirrorcote') | StickerTermurah</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        
        .flat-header {
            background-color: #ffffff;
            border-bottom: 2px solid #f1f5f9;
        }

        .nav-link {
            @apply px-4 py-2 text-sm font-bold tracking-tight text-slate-600 transition-all rounded-sm;
        }

        .nav-link:hover {
            @apply text-brand bg-brand-50;
        }

        .nav-link-active {
            @apply text-brand bg-brand-50 border-b-2 border-brand;
        }
    </style>
</head>
<body class="h-full bg-slate-50 antialiased font-sans flex flex-col selection:bg-brand selection:text-white" x-data="{ mobileMenuOpen: false }">
    <!-- Navigation -->
    <header class="sticky top-0 z-50 w-full flat-header">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center group">
                        <div class="flex items-center justify-center w-8 h-8 rounded bg-brand mr-2.5 transition-transform group-hover:scale-105 active:scale-95">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M7 20l4-16m2 16l4-16" />
                            </svg>
                        </div>
                        <span class="text-xl font-black tracking-tighter text-slate-900 uppercase">Sticker<span class="text-brand">TM</span></span>
                    </a>
                </div>
                
                <nav class="hidden md:flex items-center gap-x-1">
                    <a href="{{ auth()->check() ? route('orders.create') : route('member.register') }}" class="nav-link {{ request()->routeIs('orders.create') ? 'nav-link-active' : '' }}">Tempah Sticker</a>
                    <a href="{{ route('orders.lookup-form') }}" class="nav-link {{ request()->routeIs('orders.lookup-form') ? 'nav-link-active' : '' }}">Semak Order</a>
                    @auth
                        @if(!auth()->user()?->is_admin)
                            <a href="{{ route('member.dashboard') }}" class="nav-link {{ request()->routeIs('member.*') ? 'nav-link-active' : '' }}">Profil Ahli</a>
                        @endif
                    @else
                        <a href="{{ route('member.login') }}" class="nav-link {{ request()->routeIs('member.login') ? 'nav-link-active' : '' }}">Login</a>
                    @endauth
                    
                    <div class="w-px h-4 bg-slate-200 mx-2"></div>
                    
                    @auth
                        @if(!auth()->user()?->is_admin)
                            <form method="post" action="{{ route('member.logout') }}">
                                @csrf
                                <button type="submit" class="nav-link text-rose-600 hover:bg-rose-50">Logout</button>
                            </form>
                        @endif
                    @endauth
                    <a href="{{ route('admin.login') }}" class="px-4 py-1.5 text-xs font-black uppercase tracking-widest text-white bg-slate-900 hover:bg-slate-800 transition-colors rounded-sm ml-2">Portal Admin</a>
                </nav>

                <div class="flex items-center md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="p-2 text-slate-600 hover:bg-slate-100 transition-colors">
                        <svg class="h-6 w-6" x-show="!mobileMenuOpen" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="h-6 w-6" x-show="mobileMenuOpen" x-cloak fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="mobileMenuOpen" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="md:hidden border-t border-slate-100 bg-white">
            <div class="space-y-0.5 px-4 pt-2 pb-6">
                <a href="{{ auth()->check() ? route('orders.create') : route('member.register') }}" class="block px-4 py-3 text-sm font-bold text-slate-900 hover:bg-brand-50 rounded-sm transition-all">Tempah Sticker</a>
                <a href="{{ route('orders.lookup-form') }}" class="block px-4 py-3 text-sm font-bold text-slate-900 hover:bg-brand-50 rounded-sm transition-all">Semak Status</a>
                @auth
                    @if(!auth()->user()?->is_admin)
                        <a href="{{ route('member.dashboard') }}" class="block px-4 py-3 text-sm font-bold text-slate-900 hover:bg-brand-50 rounded-sm transition-all">Profil Ahli</a>
                        <form method="post" action="{{ route('member.logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm font-bold text-rose-600 hover:bg-rose-50 rounded-sm transition-all">Logout</button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('member.login') }}" class="block px-4 py-3 text-sm font-bold text-slate-900 hover:bg-brand-50 rounded-sm transition-all">Login Ahli</a>
                @endauth
                <a href="{{ route('admin.login') }}" class="block px-4 py-3 text-xs font-black uppercase tracking-widest text-slate-400 hover:bg-slate-50 rounded-sm transition-all">Portal Admin</a>
            </div>
        </div>
    </header>

    <main class="flex-1">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-16">
            @if(session('success'))
                <div class="mb-10 p-5 bg-emerald-50 border-l-4 border-emerald-500 animate-in fade-in slide-in-from-top-2">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-bold text-emerald-900 tracking-tight">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-10 p-5 bg-rose-50 border-l-4 border-rose-500 animate-in fade-in slide-in-from-top-2">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-sm font-bold text-rose-900 tracking-tight">{{ session('error') }}</p>
                    </div>
                </div>
            @endif
            
            <div class="animate-in fade-in duration-700">
                @yield('content')
            </div>
        </div>
    </main>

    <footer class="bg-white border-t-2 border-slate-100 py-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="flex items-center">
                    <div class="w-6 h-6 rounded-sm bg-slate-900 flex items-center justify-center mr-2">
                         <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M7 20l4-16m2 16l4-16" />
                        </svg>
                    </div>
                    <span class="text-sm font-black tracking-tighter text-slate-900 uppercase">STICKER<span class="text-brand">TM</span></span>
                </div>
                <p class="text-slate-400 text-[11px] font-bold uppercase tracking-widest">
                    &copy; {{ date('Y') }} StickerTermurah. Hak Cipta Terpelihara.
                </p>
                <div class="flex items-center gap-8">
                    <a href="#" class="text-slate-400 hover:text-brand transition-colors text-[10px] font-black uppercase tracking-widest">Syarat</a>
                    <a href="#" class="text-slate-400 hover:text-brand transition-colors text-[10px] font-black uppercase tracking-widest">Privasi</a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>



