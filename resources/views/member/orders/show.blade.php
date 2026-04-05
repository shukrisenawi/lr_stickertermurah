@extends('layouts.frontend')

@section('title', 'Detail Order')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Order Saya</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">{{ $order->order_no }}</h1>
            <p class="mt-2 text-sm text-slate-500 font-medium">Tarikh: {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <div class="flex gap-2">
            <form method="post" action="{{ route('member.orders.repeat', $order) }}">
                @csrf
                <button type="submit" class="rounded-2xl bg-brand-600 px-5 py-3 text-xs font-black uppercase tracking-widest text-white">Repeat Order</button>
            </form>
            @if($order->invoice)
                <a href="{{ route('member.invoices.show', $order->invoice) }}" class="rounded-2xl bg-emerald-100 px-5 py-3 text-xs font-black uppercase tracking-widest text-emerald-700">Lihat Invoice</a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Status</p>
            <p class="mt-2 text-lg font-black text-slate-900">{{ ucfirst($order->status) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tracking No</p>
            <p class="mt-2 text-lg font-black text-slate-900">{{ $order->tracking_no ?: '-' }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jumlah</p>
            <p class="mt-2 text-lg font-black text-brand-600">RM {{ number_format($order->total, 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="text-lg font-black text-slate-900">Item Tempahan</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($order->items as $item)
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <p class="text-sm font-black text-slate-900">{{ $item->design->name }} · {{ $item->size->name }}</p>
                        <p class="text-xs text-slate-500 font-semibold">{{ $item->quantity }} pcs × RM {{ number_format($item->unit_price, 2) }}</p>
                    </div>
                    <p class="text-sm font-black text-brand-600">RM {{ number_format($item->line_total, 2) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
