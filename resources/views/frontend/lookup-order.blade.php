@extends('layouts.frontend')

@section('title', 'Semak Status & Repeat Order')

@section('content')
<div class="mx-auto max-w-5xl space-y-8">
    <section class="space-y-4 text-center">
        <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-xl bg-brand-600 text-white shadow-lg shadow-brand-600/20">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" /></svg>
        </div>
        <h1 class="frontend-title">Semak Rekod Tempahan</h1>
        <p class="mx-auto max-w-2xl text-sm text-slate-500">Masukkan nombor telefon untuk melihat sejarah tempahan, status semasa, dan buat repeat order dengan cepat.</p>
    </section>

    <section class="frontend-flat-card p-8">
        <form method="post" action="{{ route('orders.lookup') }}" class="space-y-4">
            @csrf
            <div>
                <label>No. Telefon Pelanggan</label>
                <div class="mt-2 flex flex-col gap-4 sm:flex-row">
                    <input type="text" name="customer_phone" value="{{ old('customer_phone', $customerPhone ?? '') }}" required placeholder="Contoh: 0111223344">
                    <button type="submit" class="frontend-btn-primary whitespace-nowrap">Cari Rekod</button>
                </div>
            </div>
        </form>
    </section>

    @if(isset($orders))
        <section class="space-y-6">
            @if($orders->isEmpty())
                <div class="frontend-flat-card px-6 py-16 text-center">
                    <h3 class="text-2xl font-bold tracking-tight text-slate-900">Tiada rekod ditemui</h3>
                    <p class="mt-2 text-sm text-slate-500">Kami tidak menjumpai sebarang tempahan untuk nombor telefon tersebut.</p>
                </div>
            @else
                <div class="frontend-section-head">
                    <span class="frontend-section-accent"></span>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900">{{ $orders->count() }} Rekod Ditemui</h2>
                        <p class="text-sm text-slate-500">Semua rekod tempahan untuk nombor telefon yang dicari.</p>
                    </div>
                </div>

                @foreach($orders as $order)
                    <div class="frontend-flat-card p-6">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div class="space-y-2">
                                <span class="frontend-badge">#{{ $order->order_no }}</span>
                                <h3 class="text-2xl font-bold tracking-tight text-slate-900">{{ $order->customer_name }}</h3>
                                <p class="text-sm text-slate-500">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-700">{{ $order->status }}</span>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-5 py-4">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Tracking Number</p>
                                <p class="mt-2 font-semibold text-slate-900">{{ $order->tracking_no ?: 'Belum tersedia' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-5 py-4">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Jumlah Bayaran</p>
                                <p class="mt-2 font-semibold text-brand-600">RM {{ number_format($order->total, 2) }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('orders.repeat', $order) }}" class="frontend-btn-primary">Repeat Order Ini</a>
                        </div>
                    </div>
                @endforeach
            @endif
        </section>
    @endif
</div>
@endsection
