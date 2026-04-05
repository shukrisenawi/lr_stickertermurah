<!doctype html>
<html lang="ms" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard') | StickerTermurah</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Poppins', sans-serif; }
        h1, h2, h3, h4, h5, h6, .font-heading { font-family: 'Poppins', sans-serif; }
        
        .sidebar-premium {
            background: radial-gradient(circle at top left, #1e293b 0%, #0f172a 100%);
        }
        .nav-link-active {
            background: linear-gradient(90deg, rgba(217, 28, 92, 0.15) 0%, rgba(217, 28, 92, 0) 100%);
            border-right: 4px solid #d91c5c;
            color: white !important;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
        .sidebar-link:hover .icon-box {
            transform: scale(1.1) rotate(5deg);
            background: #d91c5c;
            color: white;
        }
    </style>
</head>
<body class="h-full antialiased flex overflow-hidden bg-[#f8fafc]" x-data="{ mobileSidebarOpen: false }">
    <!-- Mobile Sidebar Overlay -->
    <div x-cloak x-show="mobileSidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition-opacity ease-linear duration-300" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-md lg:hidden"
         @click="mobileSidebarOpen = false"></div>

    <aside :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-72 sidebar-premium flex flex-col transition-transform duration-500 ease-out lg:static lg:inset-auto shadow-2xl">
        <div class="flex h-20 shrink-0 items-center px-8 border-b border-white/5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-gradient-to-tr from-brand-600 to-brand-400 shadow-xl shadow-brand-500/20 group-hover:rotate-12 transition-transform duration-500">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-lg font-black text-white tracking-tight uppercase leading-none">STICKER<span class="text-accent">TM</span></span>
                    <span class="text-[9px] font-black text-slate-500 tracking-[0.2em] uppercase mt-1">Admin Centre</span>
                </div>
            </a>
        </div>
        
        <nav class="flex-1 px-4 py-8 overflow-y-auto custom-scrollbar space-y-8">
            <!-- Group Utama -->
            <div>
                <h3 class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 px-3 mb-4">Navigasi Utama</h3>
                <div class="space-y-1.5">
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link flex items-center gap-3 py-3 px-3 rounded-xl transition-all duration-300 {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5 group' }}">
                        <div class="icon-box w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300 {{ request()->routeIs('admin.dashboard') ? 'bg-brand text-white shadow-lg shadow-brand-500/20' : 'bg-slate-800/50 text-slate-500 group-hover:bg-slate-700 group-hover:text-slate-300' }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                            </svg>
                        </div>
                        <span class="text-xs font-black uppercase tracking-widest {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}">Dashboard</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="sidebar-link flex items-center gap-3 py-3 px-3 rounded-xl transition-all duration-300 {{ request()->routeIs('admin.orders.*') ? 'nav-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5 group' }}">
                        <div class="icon-box w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300 {{ request()->routeIs('admin.orders.*') ? 'bg-brand text-white shadow-lg shadow-brand-500/20' : 'bg-slate-800/50 text-slate-500 group-hover:bg-slate-700 group-hover:text-slate-300' }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </div>
                        <span class="text-xs font-black uppercase tracking-widest {{ request()->routeIs('admin.orders.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}">Tempahan</span>
                        @php $unprocessedCount = \App\Models\Order::where('status', 'pending')->count(); @endphp
                        @if($unprocessedCount > 0)
                            <span class="ml-auto inline-flex items-center justify-center h-5 min-w-[1.25rem] px-1 rounded-md bg-rose-600 text-[9px] font-black text-white ring-2 ring-rose-600/20">{{ $unprocessedCount }}</span>
                        @endif
                    </a>
                </div>
            </div>

            <!-- Group Katalog -->
            <div>
                <h3 class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500 px-3 mb-4">Katalog Produk</h3>
                <div class="space-y-1.5">
                    <a href="{{ route('admin.categories.index') }}" class="sidebar-link flex items-center gap-3 py-3 px-3 rounded-xl transition-all duration-300 {{ request()->routeIs('admin.categories.*') ? 'nav-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5 group' }}">
                        <div class="icon-box w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300 {{ request()->routeIs('admin.categories.*') ? 'bg-brand text-white shadow-lg shadow-brand-500/20' : 'bg-slate-800/50 text-slate-500 group-hover:bg-slate-700 group-hover:text-slate-300' }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.878.878 2.303.878 3.181 0l4.318-4.318c.878-.878.878-2.303 0-3.181l-9.581-9.581a2.25 2.25 0 00-1.591-.659z" />
                            </svg>
                        </div>
                        <span class="text-xs font-black uppercase tracking-widest {{ request()->routeIs('admin.categories.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}">Kategori</span>
                    </a>

                    <a href="{{ route('admin.designs.index') }}" class="sidebar-link flex items-center gap-3 py-3 px-3 rounded-xl transition-all duration-300 {{ request()->routeIs('admin.designs.*') ? 'nav-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5 group' }}">
                        <div class="icon-box w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300 {{ request()->routeIs('admin.designs.*') ? 'bg-brand text-white shadow-lg shadow-brand-500/20' : 'bg-slate-800/50 text-slate-500 group-hover:bg-slate-700 group-hover:text-slate-300' }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </div>
                        <span class="text-xs font-black uppercase tracking-widest {{ request()->routeIs('admin.designs.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}">Katalog Grafik</span>
                    </a>

                    <a href="{{ route('admin.sizes.index') }}" class="sidebar-link flex items-center gap-3 py-3 px-3 rounded-xl transition-all duration-300 {{ request()->routeIs('admin.sizes.*') ? 'nav-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5 group' }}">
                        <div class="icon-box w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300 {{ request()->routeIs('admin.sizes.*') ? 'bg-brand text-white shadow-lg shadow-brand-500/20' : 'bg-slate-800/50 text-slate-500 group-hover:bg-slate-700 group-hover:text-slate-300' }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-xs font-black uppercase tracking-widest {{ request()->routeIs('admin.sizes.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}">Saiz & Kos</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- User Logout Section -->
        <div class="p-4 border-t border-white/5">
            <form method="post" action="{{ route('admin.logout') }}">
                @csrf
                <button class="flex items-center gap-3 w-full py-3 px-4 rounded-xl bg-rose-600/10 text-rose-400 hover:bg-rose-600 hover:text-white hover:shadow-xl hover:shadow-rose-500/20 transition-all duration-500 active:scale-95">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em]">Keluar Sistem</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden relative">
        <!-- Top Bar -->
        <header class="h-16 shrink-0 flex items-center justify-between px-6 bg-white border-b border-slate-200 lg:px-10 relative z-40">
            <div class="flex items-center gap-4">
                <!-- Mobile Toggle -->
                <button @click="mobileSidebarOpen = true" type="button" class="p-2 text-slate-600 lg:hidden rounded-xl bg-slate-100 hover:bg-slate-200 transition-colors shadow-sm">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                
                <div class="flex flex-col">
                    <span class="text-[8px] font-black text-slate-500 uppercase tracking-[0.2em] leading-none mb-1.5">Modul Pentadbiran</span>
                    <h1 class="text-lg font-black text-slate-900 uppercase tracking-tight">@yield('title', 'Admin Overview')</h1>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- User Profile Dropdown -->
                <div class="flex items-center gap-3 group cursor-pointer p-1 pl-3 rounded-xl bg-slate-100 border border-slate-200 hover:bg-white hover:border-brand-100 transition-all duration-300 hover:shadow-lg hover:shadow-brand-500/5">
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] font-black text-slate-900 leading-none mb-0.5">{{ auth()->user()->name ?? 'MASTER ADMIN' }}</span>
                        <span class="text-[7px] font-black text-brand-500 uppercase tracking-[0.15em]">Admin</span>
                    </div>
                    <div class="h-9 w-9 rounded-lg bg-gradient-to-tr from-brand-600 to-brand-400 flex items-center justify-center text-white text-xs font-black shadow-lg shadow-brand-200 ring-2 ring-white">
                        {{ substr(auth()->user()->name ?? 'M', 0, 1) }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Content Body -->
        <main class="flex-1 overflow-y-auto bg-slate-50 custom-scrollbar relative z-0">
            <div class="py-8 px-6 lg:px-10 max-w-[1600px] mx-auto">
                <!-- Notifications -->
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-8 rounded-2xl bg-emerald-50 p-5 border border-emerald-200 shadow-xl shadow-emerald-500/5 animate-in slide-in-from-top-6">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                                    <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-black text-emerald-900 uppercase tracking-widest leading-none mb-1">Berhasil</h4>
                                    <p class="text-xs font-bold text-emerald-700/80">{{ session('success') }}</p>
                                </div>
                            </div>
                            <button @click="show = false" class="text-emerald-400 hover:text-emerald-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-8 rounded-2xl bg-rose-50 p-5 border border-rose-200 shadow-xl shadow-rose-500/5 animate-in slide-in-from-top-6">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-xl bg-rose-100 flex items-center justify-center shrink-0">
                                    <svg class="h-5 w-5 text-rose-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-black text-rose-900 uppercase tracking-widest leading-none mb-1">Ralat Sistem</h4>
                                    <p class="text-xs font-bold text-rose-700/80">{{ session('error') }}</p>
                                </div>
                            </div>
                            <button @click="show = false" class="text-rose-400 hover:text-rose-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Yield Content -->
                <div class="animate-in fade-in slide-in-from-bottom-6 duration-700 fill-mode-both">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>


