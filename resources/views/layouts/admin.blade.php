<!doctype html>
<html lang="ms" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard') | StickerTermurah</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-baru.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }

        .admin-sidebar {
            background:
                radial-gradient(circle at top left, rgba(217, 28, 92, 0.24), transparent 26%),
                linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }

        .admin-nav-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-left: 4px solid transparent;
            border-radius: 0.625rem;
            padding: 0.625rem 0.875rem;
            color: #94a3b8;
            transition: 180ms ease;
        }

        .admin-nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .admin-nav-link.active {
            color: #fff;
            border-left-color: #d91c5c;
            background: rgba(255, 255, 255, 0.08);
        }

        .admin-nav-icon {
            display: flex;
            height: 2rem;
            width: 2rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.625rem;
            background: rgba(15, 23, 42, 0.45);
            color: #94a3b8;
            transition: 180ms ease;
        }

        .admin-nav-link:hover .admin-nav-icon,
        .admin-nav-link.active .admin-nav-icon {
            background: #d91c5c;
            color: #fff;
        }

        .admin-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .admin-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .admin-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }
    </style>
</head>
<body class="admin-shell h-full overflow-hidden antialiased" x-data="{ mobileSidebarOpen: false }">
    <div x-cloak x-show="mobileSidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:hidden" @click="mobileSidebarOpen = false"></div>

    <div class="flex h-full">
        <aside
            :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="admin-sidebar fixed inset-y-0 left-0 z-50 flex w-[260px] flex-col border-r border-slate-700 shadow-2xl transition-transform duration-300 lg:static lg:translate-x-0"
        >
            <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 text-white shadow-lg shadow-brand-900/20">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h16.5v4.5H3.75zM3.75 10.5h7.5v9h-7.5zM12.75 10.5h7.5V21h-7.5z" />
                    </svg>
                </div>
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="block text-lg font-extrabold leading-none text-white">StickerTermurah</a>
                    <p class="mt-1 text-[10px] uppercase tracking-[0.28em] text-slate-400">Admin Suite</p>
                </div>
            </div>

            <nav class="admin-scrollbar flex-1 overflow-y-auto px-2 py-3">
                <div class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="admin-nav-icon">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12 12 4.5l8.25 7.5V20.25H14.25v-5.25h-4.5v5.25H3.75V12Z" />
                            </svg>
                        </span>
                        <span class="text-[13px] font-semibold">Dashboard</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <span class="admin-nav-icon">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75V6a3.75 3.75 0 1 0-7.5 0v.75M4.5 8.25h15l-1.2 11.1a1.5 1.5 0 0 1-1.49 1.35H7.19a1.5 1.5 0 0 1-1.49-1.35L4.5 8.25Z" />
                            </svg>
                        </span>
                        <span class="text-[13px] font-semibold">Tempahan</span>
                        @php $unprocessedCount = \App\Models\Order::where('status', 'pending')->count(); @endphp
                        @if($unprocessedCount > 0)
                            <span class="ml-auto rounded-full bg-brand-600 px-2 py-1 text-[10px] font-semibold text-white">{{ $unprocessedCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('admin.invoices.create') }}" class="admin-nav-link {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                        <span class="admin-nav-icon">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6l3 3v13.5H7.5A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 3.75v3h3" />
                            </svg>
                        </span>
                        <span class="text-[13px] font-semibold">Invoice</span>
                    </a>

                    <a href="{{ route('admin.customers.index') }}" class="admin-nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                        <span class="admin-nav-icon">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.75a3.75 3.75 0 1 0-7.5 0M6 20.25V18a6 6 0 0 1 12 0v2.25M12 10.5a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" />
                            </svg>
                        </span>
                        <span class="text-[13px] font-semibold">Pelanggan</span>
                    </a>

                    <a href="{{ route('admin.jnt.index') }}" class="admin-nav-link {{ request()->routeIs('admin.jnt.*') ? 'active' : '' }}">
                        <span class="admin-nav-icon">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25h6M3.75 7.5h11.379a2.25 2.25 0 0 1 1.974 1.17l2.397 4.455a2.25 2.25 0 0 1 .27 1.068v2.307A1.5 1.5 0 0 1 18.27 18h-.52a2.25 2.25 0 1 1-4.5 0h-2.5a2.25 2.25 0 1 1-4.5 0H5.25A1.5 1.5 0 0 1 3.75 16.5v-9Z" />
                            </svg>
                        </span>
                        <span class="text-[13px] font-semibold">J&T Shipping</span>
                    </a>

                    <a href="{{ route('admin.categories.index') }}" class="admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <span class="admin-nav-icon">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3.75H6A2.25 2.25 0 0 0 3.75 6v3.568c0 .597.237 1.17.659 1.591l8.75 8.75a2.25 2.25 0 0 0 3.182 0l3.568-3.568a2.25 2.25 0 0 0 0-3.182l-8.75-8.75a2.25 2.25 0 0 0-1.591-.659Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h.008v.008H7.5V7.5Z" />
                            </svg>
                        </span>
                        <span class="text-[13px] font-semibold">Kategori</span>
                    </a>

                    <a href="{{ route('admin.designs.index') }}" class="admin-nav-link {{ request()->routeIs('admin.designs.*') ? 'active' : '' }}">
                        <span class="admin-nav-icon">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5v13.5H3.75z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m7.5 15 3-3 2.25 2.25L15.75 10.5 18 12.75" />
                            </svg>
                        </span>
                        <span class="text-[13px] font-semibold">Design Sticker</span>
                    </a>

                    <a href="{{ route('admin.sizes.index') }}" class="admin-nav-link {{ request()->routeIs('admin.sizes.*') ? 'active' : '' }}">
                        <span class="admin-nav-icon">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m4.5-11.25H9.75a2.25 2.25 0 0 0 0 4.5h4.5a2.25 2.25 0 0 1 0 4.5H7.5" />
                            </svg>
                        </span>
                        <span class="text-[13px] font-semibold">Saiz & Kos</span>
                    </a>

                    <a href="{{ route('admin.contacts.google.index') }}" class="admin-nav-link {{ request()->routeIs('admin.contacts.google.*') ? 'active' : '' }}">
                        <span class="admin-nav-icon">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 8.25h7.5M8.25 12h7.5M8.25 15.75h4.5" />
                            </svg>
                        </span>
                        <span class="text-[13px] font-semibold">Google Contact</span>
                    </a>

                    <a href="{{ route('admin.contacts.extract') }}" class="admin-nav-link {{ request()->routeIs('admin.contacts.extract*') ? 'active' : '' }}">
                        <span class="admin-nav-icon">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 4.5h9A2.25 2.25 0 0 1 18.75 6.75v10.5A2.25 2.25 0 0 1 16.5 19.5h-9a2.25 2.25 0 0 1-2.25-2.25V6.75A2.25 2.25 0 0 1 7.5 4.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 8.25h7.5M8.25 12h4.5" />
                            </svg>
                        </span>
                        <span class="text-[13px] font-semibold">Extract Contact</span>
                    </a>
                </div>
            </nav>

            <div class="space-y-3 border-t border-white/10 p-3">
                <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-3 py-2.5">
                    @if(auth()->user()?->avatar_path)
                        <img
                            src="{{ asset('storage/' . auth()->user()->avatar_path) }}"
                            alt="{{ auth()->user()->name ?? 'Admin' }}"
                            class="h-9 w-9 rounded-full border border-white/20 object-cover"
                        >
                    @else
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-sm font-bold text-slate-800">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="truncate text-[13px] font-semibold text-white">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-slate-400">System Admin</p>
                    </div>
                </div>

                <form method="post" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-[13px] font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m-6-3h11.25m0 0-3-3m3 3-3 3" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1 overflow-hidden">
            <header class="fixed left-0 right-0 top-0 z-30 flex h-[72px] items-center justify-between border-b border-slate-200 bg-white px-4 lg:left-[260px] lg:px-8">
                <div class="flex flex-1 items-center gap-4">
                    <button @click="mobileSidebarOpen = true" type="button" class="rounded-xl border border-slate-200 p-2 text-slate-600 lg:hidden">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <div class="relative hidden w-full max-w-xs md:block">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                            </svg>
                        </div>
                        <input type="search" placeholder="Cari modul admin..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-brand-300 focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>

                <nav class="hidden items-center gap-8 px-8 lg:flex">
                    <a href="{{ route('admin.dashboard') }}" class="border-b-2 {{ request()->routeIs('admin.dashboard') ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-900' }} pb-1 text-sm font-semibold">Overview</a>
                    <a href="{{ route('admin.orders.index') }}" class="border-b-2 {{ request()->routeIs('admin.orders.*') ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-900' }} pb-1 text-sm font-semibold">Orders</a>
                    <a href="{{ route('admin.customers.index') }}" class="border-b-2 {{ request()->routeIs('admin.customers.*') ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-900' }} pb-1 text-sm font-semibold">Team</a>
                </nav>

                <div class="flex items-center gap-3">
                    <button type="button" class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 1 5.454 1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.142 23.848 23.848 0 0 1 5.454-1.31m5.715 0a24.255 24.255 0 0 0-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </button>
                    <button type="button" class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h6.75v6.75H3.75zm9.75 0h6.75v6.75H13.5zm-9.75 9.75h6.75v3.75H3.75zm9.75 0h6.75v3.75H13.5z" />
                        </svg>
                    </button>
                    <div class="relative hidden border-l border-slate-200 pl-4 sm:block" x-data="{ profileMenuOpen: false }">
                        <button
                            type="button"
                            class="flex items-center gap-3 rounded-xl px-2 py-1.5 transition hover:bg-slate-50"
                            @click="profileMenuOpen = !profileMenuOpen"
                            @keydown.escape.window="profileMenuOpen = false"
                            :aria-expanded="profileMenuOpen.toString()"
                        >
                            <div class="text-right">
                                <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name ?? 'Admin' }}</p>
                                <p class="text-xs text-slate-500">System Admin</p>
                            </div>
                            @if(auth()->user()?->avatar_path)
                                <img
                                    src="{{ asset('storage/' . auth()->user()->avatar_path) }}"
                                    alt="{{ auth()->user()->name ?? 'Admin' }}"
                                    class="h-9 w-9 rounded-full border border-slate-200 object-cover"
                                >
                            @else
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                                </div>
                            @endif
                        </button>

                        <div
                            x-cloak
                            x-show="profileMenuOpen"
                            x-transition.origin.top.right
                            @click.away="profileMenuOpen = false"
                            class="absolute right-0 top-[calc(100%+0.75rem)] z-50 w-64 rounded-2xl border border-slate-200 bg-white p-2 shadow-[0_18px_40px_rgba(15,23,42,0.12)]"
                        >
                            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3">
                                <div class="flex items-center gap-3">
                                    @if(auth()->user()?->avatar_path)
                                        <img
                                            src="{{ asset('storage/' . auth()->user()->avatar_path) }}"
                                            alt="{{ auth()->user()->name ?? 'Admin' }}"
                                            class="h-11 w-11 rounded-full border border-white object-cover"
                                        >
                                    @else
                                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white">
                                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name ?? 'Admin' }}</p>
                                        <p class="mt-1 truncate text-xs text-slate-500">{{ auth()->user()->email ?? 'admin@stickertermurah.com' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2 space-y-1">
                                <a
                                    href="{{ route('admin.profile.edit') }}"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900"
                                    @click="profileMenuOpen = false"
                                >
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    <span>Profil</span>
                                </a>

                                <a
                                    href="{{ route('admin.password.edit') }}"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900"
                                    @click="profileMenuOpen = false"
                                >
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21h-10.5A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                                    </svg>
                                    <span>Tukar Kata Laluan</span>
                                </a>
                            </div>

                            <div class="mt-2 border-t border-slate-100 pt-2">
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m-6-3h11.25m0 0-3-3m3 3-3 3" />
                                        </svg>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="admin-scrollbar h-full overflow-y-auto pt-[72px]">
                <div class="mx-auto max-w-[1440px] space-y-6 px-4 py-8 lg:px-8">
                    @if(session('success'))
                        <div x-data="{ show: true }" x-show="show" x-transition class="admin-flat-card border-emerald-200 bg-emerald-50 px-5 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-700">Berjaya</p>
                                    <p class="mt-1 text-sm text-emerald-900">{{ session('success') }}</p>
                                </div>
                                <button @click="show = false" type="button" class="text-emerald-500 hover:text-emerald-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div x-data="{ show: true }" x-show="show" x-transition class="admin-flat-card border-rose-200 bg-rose-50 px-5 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-rose-700">Ralat</p>
                                    <p class="mt-1 text-sm text-rose-900">{{ session('error') }}</p>
                                </div>
                                <button @click="show = false" type="button" class="text-rose-500 hover:text-rose-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

