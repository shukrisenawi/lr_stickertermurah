<!doctype html>
<html lang="ms" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard') | StickerTermurah</title>

    <!-- Fonts: Outfit for Display, Inter for Body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        
        .flat-sidebar {
            background-color: #0f172a;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .nav-link-active {
            background-color: rgba(217, 28, 92, 0.1);
            color: #ff4d8d !important;
            border-left: 3px solid #d91c5c;
        }

        .nav-link-active .icon-box {
            color: white;
            background-color: #d91c5c;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 0px;
        }

        .sidebar-link:hover .icon-box {
            background-color: #d91c5c;
            color: white;
            transform: translateY(-2px);
        }

        :root {
            --admin-font-base: 13px;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: var(--admin-font-base);
            color: #1e293b;
            background-color: #f8fafc;
        }

        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="h-full antialiased flex overflow-hidden" x-data="{ mobileSidebarOpen: false }">
    <!-- Mobile Sidebar Overlay -->
    <div x-cloak x-show="mobileSidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition-opacity ease-linear duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm lg:hidden"
         @click="mobileSidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-64 flat-sidebar flex flex-col transition-transform duration-300 ease-in-out lg:static lg:inset-auto">
        
        <!-- Logo Section -->
        <div class="flex h-16 shrink-0 items-center px-6 border-b border-white/5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 group">
                <div class="flex items-center justify-center w-7 h-7 rounded bg-brand flex-shrink-0 transition-all group-hover:bg-brand-400">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-black text-white tracking-widest uppercase leading-none">STICKER<span class="text-brand">TM</span></span>
                    <span class="text-[8px] font-bold text-slate-500 tracking-[0.3em] uppercase mt-0.5">ADMIN NAVIGATOR</span>
                </div>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-6 overflow-y-auto custom-scrollbar space-y-6">
            <!-- Group Utama -->
            <div>
                <h3 class="text-[9px] font-bold uppercase tracking-[0.2em] text-slate-500 px-3 mb-3">MENU UTAMA</h3>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center gap-3 py-2.5 px-3 transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-sm' }}">
                        <div class="icon-box w-7 h-7 rounded flex items-center justify-center transition-all bg-slate-800/50 text-slate-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider">Dashboard</span>
                    </a>

                    <a href="{{ route('admin.invoices.create') }}" class="sidebar-link flex items-center gap-3 py-2.5 px-3 transition-all duration-200 {{ request()->routeIs('admin.invoices.*') ? 'nav-link-active' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-sm' }}">
                        <div class="icon-box w-7 h-7 rounded flex items-center justify-center transition-all bg-slate-800/50 text-slate-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12h6m-6 3h6m2.25-13.5V6a2.25 2.25 0 0 1-2.25 2.25h-3A2.25 2.25 0 0 1 10.5 6V3.75m9 0h-9A2.25 2.25 0 0 0 8.25 6v12A2.25 2.25 0 0 0 10.5 20.25h9A2.25 2.25 0 0 0 21.75 18V6A2.25 2.25 0 0 0 19.5 3.75Z" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider">Invoice</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="sidebar-link flex items-center gap-3 py-2.5 px-3 transition-all duration-200 {{ request()->routeIs('admin.orders.*') ? 'nav-link-active' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-sm' }}">
                        <div class="icon-box w-7 h-7 rounded flex items-center justify-center transition-all bg-slate-800/50 text-slate-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider">Tempahan</span>
                        @php $unprocessedCount = \App\Models\Order::where('status', 'pending')->count(); @endphp
                        @if($unprocessedCount > 0)
                            <span class="ml-auto inline-flex items-center justify-center h-4 min-w-[1rem] px-1 rounded-sm bg-brand text-[8px] font-black text-white">{{ $unprocessedCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('admin.jnt.index') }}" class="sidebar-link flex items-center gap-3 py-2.5 px-3 transition-all duration-200 {{ request()->routeIs('admin.jnt.*') ? 'nav-link-active' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-sm' }}">
                        <div class="icon-box w-7 h-7 rounded flex items-center justify-center transition-all bg-slate-800/50 text-slate-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.129-1.125V11.25c0-1.58-1.282-2.812-2.82-2.863l-2.008-.066a2.25 2.25 0 0 0-1.898 1.144l-1.642 2.736a2.25 2.25 0 0 1-1.928 1.091H10.5" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider">J&T Shipping</span>
                    </a>

                    <a href="{{ route('admin.customers.index') }}" class="sidebar-link flex items-center gap-3 py-2.5 px-3 transition-all duration-200 {{ request()->routeIs('admin.customers.*') ? 'nav-link-active' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-sm' }}">
                        <div class="icon-box w-7 h-7 rounded flex items-center justify-center transition-all bg-slate-800/50 text-slate-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.035.666A11.944 11.944 0 0 1 12 21c-2.34 0-4.5-.67-6.326-1.825a7.768 7.768 0 0 1-.035-.666l.001-.031m12.36 0a11.959 11.959 0 0 1-5.64 1.452c-2.06 0-4.008-.52-5.64-1.452m11.28 0a3 3 0 1 0-5.94 0m5.94 0a3 3 0 1 1-5.94 0m-5.94 0a3 3 0 1 0-5.94 0m5.94 0a3 3 0 1 1-5.94 0m9.751-10.659a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm6 2.25a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider">Pelanggan</span>
                    </a>

                    <a href="{{ route('admin.contacts.google.index') }}" class="sidebar-link flex items-center gap-3 py-2.5 px-3 transition-all duration-200 {{ request()->routeIs('admin.contacts.google.*') ? 'nav-link-active' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-sm' }}">
                        <div class="icon-box w-7 h-7 rounded flex items-center justify-center transition-all bg-slate-800/50 text-slate-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h9A2.25 2.25 0 0 1 18.75 6v12a2.25 2.25 0 0 1-2.25 2.25h-9A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Zm.75 3.75h7.5m-7.5 3h7.5m-7.5 3h4.5" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider">Google Contacts</span>
                    </a>
                </div>
            </div>

            <!-- Group Katalog -->
            <div>
                <h3 class="text-[9px] font-bold uppercase tracking-[0.2em] text-slate-500 px-3 mb-3">KATALOG</h3>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.categories.index') }}" class="sidebar-link flex items-center gap-3 py-2.5 px-3 transition-all duration-200 {{ request()->routeIs('admin.categories.*') ? 'nav-link-active' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-sm' }}">
                        <div class="icon-box w-7 h-7 rounded flex items-center justify-center transition-all bg-slate-800/50 text-slate-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.878.878 2.303.878 3.181 0l4.318-4.318c.878-.878.878-2.303 0-3.181l-9.581-9.581a2.25 2.25 0 00-1.591-.659z" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider">Kategori</span>
                    </a>

                    <a href="{{ route('admin.designs.index') }}" class="sidebar-link flex items-center gap-3 py-2.5 px-3 transition-all duration-200 {{ request()->routeIs('admin.designs.*') ? 'nav-link-active' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-sm' }}">
                        <div class="icon-box w-7 h-7 rounded flex items-center justify-center transition-all bg-slate-800/50 text-slate-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider">Katalog Grafik</span>
                    </a>

                    <a href="{{ route('admin.sizes.index') }}" class="sidebar-link flex items-center gap-3 py-2.5 px-3 transition-all duration-200 {{ request()->routeIs('admin.sizes.*') ? 'nav-link-active' : 'text-slate-400 hover:text-slate-200 hover:bg-white/5 rounded-sm' }}">
                        <div class="icon-box w-7 h-7 rounded flex items-center justify-center transition-all bg-slate-800/50 text-slate-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider">Saiz & Kos</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- User Logout -->
        <div class="p-4 border-t border-white/5">
            <form method="post" action="{{ route('admin.logout') }}">
                @csrf
                <button class="flex items-center justify-center gap-2.5 w-full py-2.5 px-4 bg-slate-800 text-slate-300 font-bold text-[10px] uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all active:scale-95">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    Keluar Sistem
                </button>
            </form>
        </div>
    </aside>

    <!-- Content Area -->
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden relative">
        <!-- Header -->
        <header class="h-16 shrink-0 flex items-center justify-between px-6 bg-white border-b border-slate-200 lg:px-8 relative z-40">
            <div class="flex items-center gap-4">
                <button @click="mobileSidebarOpen = true" type="button" class="p-2 text-slate-500 lg:hidden hover:bg-slate-100 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <div class="flex flex-col">
                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-[0.2em] leading-none mb-1">SYSTEM MODULE</span>
                    <h1 class="text-sm font-black text-slate-900 uppercase tracking-tight">@yield('title', 'Admin Overview')</h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2.5 p-1 pr-3 rounded bg-slate-50 border border-slate-200">
                    <div class="h-8 w-8 rounded bg-slate-900 flex items-center justify-center text-white text-[10px] font-black tracking-tighter">
                        {{ substr(auth()->user()->name ?? 'M', 0, 1) }}
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold text-slate-900 leading-none truncate max-w-[100px]">{{ auth()->user()->name ?? 'Administrator' }}</span>
                        <span class="text-[7px] font-bold text-brand uppercase tracking-tighter">Verified Admin</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main -->
        <main class="flex-1 overflow-y-auto bg-slate-50 custom-scrollbar relative z-0">
            <div class="py-8 px-6 lg:px-8 max-w-7xl mx-auto">
                <!-- Alerts -->
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-xs font-bold text-emerald-800 tracking-tight">{{ session('success') }}</p>
                        </div>
                        <button @click="show = false" class="text-emerald-400 hover:text-emerald-600">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-xs font-bold text-rose-800 tracking-tight">{{ session('error') }}</p>
                        </div>
                        <button @click="show = false" class="text-rose-400 hover:text-rose-600">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                @endif

                <div class="animate-in fade-in duration-500">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>





