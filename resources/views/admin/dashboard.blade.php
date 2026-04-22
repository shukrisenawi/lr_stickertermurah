@extends('layouts.admin')

@section('title', 'Ringkasan Sistem')

@section('content')
<section class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div class="space-y-3">
            <div class="admin-title-block">
                <span class="admin-title-accent"></span>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Dashboard Admin</h1>
                    <p class="mt-1 text-sm text-slate-500">Ringkasan operasi StickerTermurah untuk pemantauan tempahan, katalog, dan prestasi semasa.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="admin-pill">Kemaskini langsung</span>
                <span class="admin-pill">{{ now()->format('d M Y') }}</span>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.orders.index') }}" class="admin-btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75V6a3.75 3.75 0 1 0-7.5 0v.75M4.5 8.25h15l-1.2 11.1a1.5 1.5 0 0 1-1.49 1.35H7.19a1.5 1.5 0 0 1-1.49-1.35L4.5 8.25Z" />
                </svg>
                Lihat Tempahan
            </a>
            <a href="{{ route('admin.invoices.create') }}" class="admin-btn-secondary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6l3 3v13.5H7.5A2.25 2.25 0 0 1 5.25 18V6A2.25 2.25 0 0 1 7.5 3.75Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 3.75v3h3" />
                </svg>
                Jana Invoice
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="admin-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="admin-kpi-label">Jumlah Tempahan</p>
                    <p class="admin-kpi-value">{{ number_format($totalOrders) }}</p>
                    <p class="mt-2 text-sm text-slate-500">Semua pesanan yang direkodkan dalam sistem.</p>
                </div>
                <div class="admin-kpi-icon bg-rose-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75V6a3.75 3.75 0 1 0-7.5 0v.75M4.5 8.25h15l-1.2 11.1a1.5 1.5 0 0 1-1.49 1.35H7.19a1.5 1.5 0 0 1-1.49-1.35L4.5 8.25Z" />
                    </svg>
                </div>
            </div>
        </article>

        <article class="admin-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="admin-kpi-label">Menunggu Tindakan</p>
                    <p class="admin-kpi-value">{{ number_format($pendingOrders) }}</p>
                    <p class="mt-2 text-sm text-slate-500">Pesanan baharu yang masih belum diproses.</p>
                </div>
                <div class="admin-kpi-icon bg-amber-500">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3.75 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </article>

        <article class="admin-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="admin-kpi-label">Design Aktif</p>
                    <p class="admin-kpi-value">{{ number_format($totalDesigns) }}</p>
                    <p class="mt-2 text-sm text-slate-500">Jumlah aset design yang tersedia untuk jualan.</p>
                </div>
                <div class="admin-kpi-icon bg-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5v13.5H3.75z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m7.5 15 3-3 2.25 2.25L15.75 10.5 18 12.75" />
                    </svg>
                </div>
            </div>
        </article>

        <article class="admin-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="admin-kpi-label">Kategori / Saiz</p>
                    <p class="admin-kpi-value">{{ number_format($totalCategories) }}</p>
                    <p class="mt-2 text-sm text-slate-500">Struktur katalog utama yang aktif dalam panel.</p>
                </div>
                <div class="admin-kpi-icon bg-slate-800">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3.75H6A2.25 2.25 0 0 0 3.75 6v3.568c0 .597.237 1.17.659 1.591l8.75 8.75a2.25 2.25 0 0 0 3.182 0l3.568-3.568a2.25 2.25 0 0 0 0-3.182l-8.75-8.75a2.25 2.25 0 0 0-1.591-.659Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h.008v.008H7.5V7.5Z" />
                    </svg>
                </div>
            </div>
        </article>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <section class="space-y-4 xl:col-span-7">
            <div class="admin-title-block">
                <span class="admin-title-accent"></span>
                <div>
                    <h2 class="admin-section-title">Tempahan Terkini</h2>
                    <p class="admin-section-copy">Jadual ringkas untuk semak order terbaru dan terus masuk ke halaman tindakan.</p>
                </div>
            </div>

            <div class="admin-flat-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Status</th>
                                <th>Jumlah</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td>
                                        <div class="space-y-1">
                                            <p class="font-semibold text-slate-900">ST-{{ $order->order_no }}</p>
                                            <p class="text-xs text-slate-500">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="space-y-1">
                                            <p class="font-semibold text-slate-900">{{ $order->customer_name }}</p>
                                            <p class="text-xs text-slate-500">{{ $order->customer_phone }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusClasses = match($order->status) {
                                                'pending' => 'bg-amber-100 text-amber-700',
                                                'processing' => 'bg-sky-100 text-sky-700',
                                                'shipped' => 'bg-rose-100 text-rose-700',
                                                'completed' => 'bg-emerald-100 text-emerald-700',
                                                'cancelled' => 'bg-slate-200 text-slate-700',
                                                default => 'bg-slate-100 text-slate-700',
                                            };
                                        @endphp
                                        <span class="admin-status {{ $statusClasses }}">{{ $order->status }}</span>
                                    </td>
                                    <td class="font-semibold text-slate-900">RM{{ number_format($order->total, 2) }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="admin-btn-secondary px-4 py-2 text-xs">Buka</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="mx-auto max-w-md space-y-2">
                                            <p class="text-lg font-semibold text-slate-900">Belum ada tempahan untuk dipaparkan.</p>
                                            <p class="text-sm text-slate-500">Rekod baru akan muncul di sini sebaik sahaja pesanan diterima.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="space-y-4 xl:col-span-5">
            <div class="admin-title-block">
                <span class="admin-title-accent"></span>
                <div>
                    <h2 class="admin-section-title">Widget Operasi</h2>
                    <p class="admin-section-copy">Komponen ringkas ikut gaya dashboard rujukan untuk highlight info semasa.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <article class="admin-flat-card p-6 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border-4 border-white bg-slate-100 shadow-sm">
                        <span class="text-2xl font-bold text-slate-800">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                    </div>
                    <h3 class="mt-4 text-xl font-bold text-slate-900">{{ auth()->user()->name ?? 'Admin' }}</h3>
                    <p class="text-sm text-slate-500">System Administrator</p>
                    <a href="{{ route('admin.customers.index') }}" class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-slate-50 px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.16em] text-slate-700 transition hover:bg-slate-100">Lihat Pelanggan</a>
                </article>

                <div class="grid grid-rows-2 gap-4">
                    <article class="admin-flat-card flex items-center justify-between p-5">
                        <div>
                            <p class="admin-mini-label">Tempahan Baru</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-rose-600">{{ number_format($pendingOrders) }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3.75 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                    </article>

                    <article class="admin-flat-card flex items-center justify-between p-5">
                        <div>
                            <p class="admin-mini-label">Katalog Design</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-emerald-600">{{ number_format($totalDesigns) }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5v13.5H3.75z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m7.5 15 3-3 2.25 2.25L15.75 10.5 18 12.75" />
                            </svg>
                        </div>
                    </article>
                </div>
            </div>

            <article class="admin-flat-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="admin-mini-label">Prestasi Mingguan</p>
                        <h3 class="mt-2 text-xl font-bold text-slate-900">Aliran Tempahan</h3>
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">7 Hari</span>
                </div>

                <div class="mt-8 flex h-56 items-end justify-between gap-3">
                    @foreach([42, 58, 36, 72, 54, 84] as $index => $height)
                        <div class="group flex flex-1 flex-col items-center justify-end gap-3">
                            <div class="relative flex h-44 w-full max-w-[42px] items-end overflow-hidden rounded-t-xl bg-rose-100/70">
                                <div class="w-full rounded-t-xl bg-brand-600 transition duration-200 group-hover:brightness-110" style="height: {{ $height }}%;"></div>
                            </div>
                            <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">{{ ['Isn','Sel','Rab','Kha','Jum','Sab'][$index] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>
    </div>

    <section class="space-y-4">
        <div class="admin-title-block">
            <span class="admin-title-accent"></span>
            <div>
                <h2 class="admin-section-title">System Overview</h2>
                <p class="admin-section-copy">Bento-style panel untuk beri gambaran pantas tentang operasi harian dan kualiti servis.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4 md:grid-rows-2 md:h-[420px]">
            <article class="admin-flat-card relative overflow-hidden p-6 md:col-span-2 md:row-span-2">
                <div class="flex h-full flex-col justify-between">
                    <div>
                        <h3 class="text-2xl font-bold tracking-tight text-slate-900">Operasi Menyeluruh</h3>
                        <p class="mt-3 max-w-md text-sm leading-6 text-slate-500">Pantau tempahan, pelanggan, invoice dan logistik dalam satu aliran kerja yang lebih kemas dan mudah diimbas.</p>
                    </div>
                    <div class="flex gap-8">
                        <div>
                            <p class="text-2xl font-bold text-brand-600">99.9%</p>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Uptime Admin</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-brand-600">&lt; 2 min</p>
                            <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Respon Operasi</p>
                        </div>
                    </div>
                </div>
                <div class="absolute right-6 top-6 text-slate-100">
                    <svg class="h-24 w-24" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm-4.5-9h9" />
                    </svg>
                </div>
            </article>

            <article class="admin-flat-card flex items-center justify-between p-6 md:col-span-2">
                <div>
                    <p class="admin-mini-label">Keselamatan & Kawalan</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900">Portal Admin Stabil</h3>
                    <p class="mt-2 text-sm text-slate-500">Akses pentadbiran, modul operasi, dan pengurusan katalog kini lebih tersusun.</p>
                </div>
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 text-brand-600">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-1.5 0h12a1.5 1.5 0 0 1 1.5 1.5v6.75a1.5 1.5 0 0 1-1.5 1.5H6a1.5 1.5 0 0 1-1.5-1.5V12a1.5 1.5 0 0 1 1.5-1.5Z" />
                    </svg>
                </div>
            </article>

            <article class="admin-flat-card p-6 text-center">
                <p class="admin-mini-label">Kadar Tindak Balas</p>
                <p class="mt-3 text-4xl font-bold tracking-tight text-slate-900">+12%</p>
                <div class="mt-4 h-2 rounded-full bg-slate-100">
                    <div class="h-full w-2/3 rounded-full bg-brand-600"></div>
                </div>
            </article>

            <article class="admin-flat-card p-6 text-center">
                <p class="admin-mini-label">Skor Servis</p>
                <p class="mt-3 text-4xl font-bold tracking-tight text-slate-900">4.9</p>
                <div class="mt-4 flex justify-center gap-1 text-amber-400">
                    @for($i = 0; $i < 5; $i++)
                        <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                            <path d="m9.049 2.927 1.902 3.854 4.254.618-3.078 3 .726 4.236L9.049 12.6l-3.804 2.035.726-4.236-3.078-3 4.254-.618 1.902-3.854Z" />
                        </svg>
                    @endfor
                </div>
            </article>
        </div>
    </section>
</section>
@endsection
