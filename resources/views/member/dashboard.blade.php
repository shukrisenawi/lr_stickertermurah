@extends('layouts.frontend')

@section('title', 'Dashboard Ahli')

@section('content')
<div class="space-y-8">
    <div class="bg-white rounded-3xl border border-slate-200 p-8">
        <p class="text-[10px] font-black uppercase tracking-widest text-brand-500">Akaun Ahli</p>
        <h1 class="mt-2 text-3xl font-black text-slate-900">Selamat datang, {{ auth()->user()?->name }}</h1>
        <p class="mt-3 text-sm text-slate-500 font-medium">Urus order ulangan, semak invoice, dan buat tempahan baru terus dari panel ahli.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('orders.create') }}" class="rounded-2xl bg-brand-600 px-5 py-3 text-xs font-black uppercase tracking-widest text-white hover:bg-brand-700">Buat Tempahan</a>
            <a href="{{ route('member.orders.index') }}" class="rounded-2xl bg-slate-100 px-5 py-3 text-xs font-black uppercase tracking-widest text-slate-700 hover:bg-slate-200">Lihat Semua Order</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jumlah Order</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $totalOrders }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jumlah Invoice</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $totalInvoices }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Saiz Aktif</p>
            <p class="mt-2 text-3xl font-black text-slate-900">{{ $activeSizes }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="bg-white rounded-3xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-black text-slate-900">Order Terkini</h2>
                <a href="{{ route('member.orders.index') }}" class="text-xs font-black uppercase tracking-widest text-brand-600">Semua</a>
            </div>
            <div class="space-y-3">
                @forelse($recentOrders as $order)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-slate-900">{{ $order->order_no }}</p>
                                <p class="text-xs text-slate-500 font-semibold">{{ $order->created_at->format('d M Y') }} · {{ ucfirst($order->status) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-brand-600">RM {{ number_format($order->total, 2) }}</p>
                                <a href="{{ route('member.orders.show', $order) }}" class="text-[10px] font-black uppercase tracking-widest text-slate-500">Detail</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 font-medium">Belum ada order lagi.</p>
                @endforelse
            </div>
        </section>

        <section class="bg-white rounded-3xl border border-slate-200 p-6">
            <h2 class="text-lg font-black text-slate-900 mb-4">Semakan Harga Pantas</h2>
            <div class="space-y-2">
                @forelse($latestTiers as $tier)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-sm font-bold text-slate-700">{{ $tier->size?->name }}</p>
                        <p class="text-sm font-black text-brand-600">{{ $tier->quantity }} pcs · RM {{ number_format($tier->total_price, 2) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 font-medium">Data harga belum tersedia.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
