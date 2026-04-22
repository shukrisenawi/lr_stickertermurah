@extends('layouts.frontend')

@section('title', 'Tempahan Berjaya!')

@section('content')
<div class="mx-auto max-w-3xl space-y-8 py-6">
    <section class="space-y-4 text-center">
        <div class="mx-auto inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-emerald-500 text-white shadow-xl shadow-emerald-200">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
        </div>
        <h1 class="frontend-title">Tempahan Berjaya</h1>
        <p class="mx-auto max-w-xl text-sm text-slate-500">Terima kasih, tempahan anda telah diterima dan sedang diproses oleh pasukan kami.</p>
    </section>

    <section class="frontend-flat-card overflow-hidden">
        <div class="bg-brand-600 px-8 py-8 text-white">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-100">ID Tempahan Anda</p>
            <p class="mt-2 text-3xl font-bold tracking-tight">#{{ $order->order_no }}</p>
        </div>
        <div class="space-y-6 p-8">
            @foreach($order->items as $item)
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4 last:border-b-0 last:pb-0">
                    <div>
                        <h3 class="font-semibold text-slate-900">{{ $item->design->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $item->size->name }} · Qty {{ $item->quantity }}</p>
                    </div>
                    <span class="font-semibold text-brand-600">RM {{ number_format($item->line_total, 2) }}</span>
                </div>
            @endforeach

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-5 py-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Tracking Number</p>
                    <p class="mt-2 font-semibold text-slate-900">{{ $order->tracking_no ?: 'Sedang dikemaskini' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-5 py-4 text-right">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Jumlah Dibayar</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-brand-600">RM {{ number_format($order->total, 2) }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <a href="{{ route('orders.lookup-form') }}" class="frontend-btn-secondary">Semak Status Tempahan</a>
        <a href="{{ route('home') }}" class="frontend-btn-primary">Kembali ke Utama</a>
    </div>
</div>
@endsection
