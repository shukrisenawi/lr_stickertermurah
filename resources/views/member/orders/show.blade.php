@extends('layouts.frontend')

@section('title', 'Detail Order')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <section class="space-y-4">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="frontend-section-head">
                <span class="frontend-section-accent"></span>
                <div>
                    <h1 class="frontend-title">{{ $order->order_no }}</h1>
                    <p class="frontend-copy">Tarikh: {{ $order->created_at->format('d M Y, h:i A') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <form method="post" action="{{ route('member.orders.repeat', $order) }}">
                    @csrf
                    <button type="submit" class="frontend-btn-primary">Repeat Order</button>
                </form>
                @if($order->invoice)
                    <a href="{{ route('member.invoices.show', $order->invoice) }}" class="frontend-btn-secondary">Lihat Invoice</a>
                @endif
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <article class="frontend-flat-card p-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Status</p>
            <p class="mt-2 text-xl font-bold text-slate-900">{{ ucfirst($order->status) }}</p>
        </article>
        <article class="frontend-flat-card p-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Tracking No</p>
            <p class="mt-2 text-xl font-bold text-slate-900">{{ $order->tracking_no ?: '-' }}</p>
        </article>
        <article class="frontend-flat-card p-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Jumlah</p>
            <p class="mt-2 text-xl font-bold text-brand-600">RM {{ number_format($order->total, 2) }}</p>
        </article>
    </section>

    <section class="frontend-flat-card overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-5">
            <div class="frontend-section-head">
                <span class="frontend-section-accent"></span>
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-slate-900">Item Tempahan</h2>
                    <p class="text-sm text-slate-500">Senarai penuh item yang telah ditempah.</p>
                </div>
            </div>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($order->items as $item)
                <div class="flex flex-col gap-2 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $item->design->name }} · {{ $item->size->name }}</p>
                        <p class="text-sm text-slate-500">{{ $item->quantity }} pcs · RM {{ number_format($item->unit_price, 2) }}</p>
                    </div>
                    <p class="font-semibold text-brand-600">RM {{ number_format($item->line_total, 2) }}</p>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
