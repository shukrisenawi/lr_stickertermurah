@extends('layouts.frontend')

@section('title', 'Invoice Ahli')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Invoice</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">{{ $invoice->invoice_no }}</h1>
            <p class="mt-2 text-sm text-slate-500 font-medium">Tarikh: {{ $invoice->issue_date->format('d M Y') }}</p>
        </div>
        <button onclick="window.print()" class="rounded-2xl bg-slate-900 px-5 py-3 text-xs font-black uppercase tracking-widest text-white">Cetak</button>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pelanggan</p>
                <p class="mt-2 text-sm font-black text-slate-900">{{ $invoice->order->customer_name }}</p>
                <p class="text-sm font-medium text-slate-500">{{ $invoice->order->customer_phone }}</p>
                <p class="text-sm font-medium text-slate-500">{{ $invoice->order->customer_address }}</p>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">No Order</p>
                <p class="mt-2 text-sm font-black text-slate-900">{{ $invoice->order->order_no }}</p>
                <p class="text-sm font-medium text-slate-500">Status: {{ ucfirst($invoice->order->status) }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Item</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Kuantiti</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Harga</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($invoice->order->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-700">{{ $item->design->name }} ({{ $item->size->name }})</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-700">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-sm font-black text-brand-600">RM {{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="text-right">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jumlah Bayaran</p>
            <p class="mt-2 text-3xl font-black text-brand-600">RM {{ number_format($invoice->amount, 2) }}</p>
        </div>
    </div>
</div>
@endsection
