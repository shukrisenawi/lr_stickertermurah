@extends('layouts.frontend')

@section('title', 'Dashboard Ahli')

@section('content')
<div class="space-y-6">
    <section class="space-y-4">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="space-y-3">
                <div class="frontend-section-head">
                    <span class="frontend-section-accent"></span>
                    <div>
                        <h1 class="frontend-title">Dashboard Ahli</h1>
                        <p class="frontend-copy">Urus order ulangan, semak invoice, dan buat tempahan baru terus dari akaun anda.</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <span class="frontend-badge">Akaun aktif</span>
                    <span class="frontend-badge">{{ auth()->user()?->email }}</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('orders.create') }}" class="frontend-btn-primary">Buat Tempahan</a>
                <a href="{{ route('member.orders.index') }}" class="frontend-btn-secondary">Semua Order</a>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <article class="frontend-flat-card p-6">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Jumlah Order</p>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $totalOrders }}</p>
        </article>
        <article class="frontend-flat-card p-6">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Jumlah Invoice</p>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $totalInvoices }}</p>
        </article>
        <article class="frontend-flat-card p-6">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Saiz Aktif</p>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $activeSizes }}</p>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <article class="frontend-flat-card overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5">
                <div class="frontend-section-head">
                    <span class="frontend-section-accent"></span>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900">Order Terkini</h2>
                        <p class="text-sm text-slate-500">Rekod tempahan paling baru untuk tindakan pantas.</p>
                    </div>
                </div>
            </div>
            <div class="space-y-3 p-6">
                @forelse($recentOrders as $order)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $order->order_no }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $order->created_at->format('d M Y') }} · {{ ucfirst($order->status) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-brand-600">RM {{ number_format($order->total, 2) }}</p>
                                <a href="{{ route('member.orders.show', $order) }}" class="mt-1 inline-block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Detail</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada order lagi.</p>
                @endforelse
            </div>
        </article>

        <article class="frontend-flat-card overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-5">
                <div class="frontend-section-head">
                    <span class="frontend-section-accent"></span>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900">Semakan Harga Pantas</h2>
                        <p class="text-sm text-slate-500">Rujukan harga ringkas untuk saiz aktif terkini.</p>
                    </div>
                </div>
            </div>
            <div class="space-y-3 p-6">
                @forelse($latestTiers as $tier)
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $tier->size?->name }}</p>
                            <p class="text-xs text-slate-500">{{ $tier->quantity }} pcs</p>
                        </div>
                        <p class="font-semibold text-brand-600">RM {{ number_format($tier->total_price, 2) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Data harga belum tersedia.</p>
                @endforelse
            </div>
        </article>
    </section>
</div>
@endsection
