<!doctype html>
<html lang="ms" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sticker Mirrorcote') | StickerTermurah</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    
    <!-- Alpine.js for interactive elements -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        .glass-nav {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="h-full bg-slate-100 antialiased font-sans flex flex-col selection:bg-brand-100 selection:text-brand-900" x-data="{ mobileMenuOpen: false }">
    <!-- Navigation -->
    <header class="sticky top-0 z-50 w-full transition-all duration-300 glass-nav border-b border-slate-200/60">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center group">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-600 mr-2.5 transition-transform group-hover:scale-110 group-hover:rotate-3 shadow-lg shadow-brand-500/30">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 20l4-16m2 16l4-16" />
                            </svg>
                        </div>
                        <span class="text-xl font-black tracking-tight text-slate-900">Sticker<span class="text-brand-600">Termurah</span></span>
                    </a>
                </div>
                
                <nav class="hidden md:flex items-center gap-x-1">
                    <a href="{{ route('orders.create') }}" class="px-4 py-2 text-sm font-bold {{ request()->routeIs('orders.create') ? 'text-brand-600' : 'text-slate-600 hover:text-brand-600' }} transition-colors rounded-lg hover:bg-brand-50/50">Tempah Sticker</a>
                    <a href="{{ route('orders.lookup-form') }}" class="px-4 py-2 text-sm font-bold {{ request()->routeIs('orders.lookup-form') ? 'text-brand-600' : 'text-slate-600 hover:text-brand-600' }} transition-colors rounded-lg hover:bg-brand-50/50">Semak Order</a>
                    <div class="w-px h-4 bg-slate-200 mx-2"></div>
                    <a href="{{ route('admin.login') }}" class="px-4 py-2 text-sm font-bold text-slate-600 hover:text-slate-900 transition-colors rounded-lg hover:bg-slate-200">Admin</a>
                </nav>

                <div class="flex items-center md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-600 hover:bg-slate-200 transition-colors">
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
             class="md:hidden border-t border-slate-100 bg-white shadow-xl">
            <div class="space-y-1 px-4 pt-2 pb-6">
                <a href="{{ route('orders.create') }}" class="block rounded-xl px-4 py-3 text-base font-bold text-slate-900 hover:bg-brand-50 hover:text-brand-600 transition-all">Tempah Sticker</a>
                <a href="{{ route('orders.lookup-form') }}" class="block rounded-xl px-4 py-3 text-base font-bold text-slate-900 hover:bg-brand-50 hover:text-brand-600 transition-all">Semak Status</a>
                <a href="{{ route('admin.login') }}" class="block rounded-xl px-4 py-3 text-base font-bold text-slate-500 hover:bg-slate-50 transition-all">Portal Admin</a>
            </div>
        </div>
    </header>

    <main class="flex-1">
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
    </main>

    <footer class="bg-white border-t border-slate-300 py-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-6 h-6 rounded bg-slate-200 mr-2">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 20l4-16m2 16l4-16" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold tracking-tight text-slate-900 group">Sticker<span class="text-brand-600">Termurah</span></span>
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

    @stack('scripts')
</body>
</html>


