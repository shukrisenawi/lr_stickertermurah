@extends('layouts.frontend')

@section('title', 'Invoice Ahli')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <section class="space-y-4">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="frontend-section-head">
                <span class="frontend-section-accent"></span>
                <div>
                    <h1 class="frontend-title">{{ $invoice->invoice_no }}</h1>
                    <p class="frontend-copy">Tarikh: {{ $invoice->issue_date->format('d M Y') }}</p>
                </div>
            </div>
            <button onclick="window.print()" class="frontend-btn-secondary">Cetak</button>
        </div>
    </section>

    <section class="frontend-flat-card p-6 space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Pelanggan</p>
                <p class="mt-2 font-semibold text-slate-900">{{ $invoice->order->customer_name }}</p>
                <p class="text-sm text-slate-500">{{ $invoice->order->customer_phone }}</p>
                <p class="text-sm text-slate-500">{{ $invoice->order->customer_address }}</p>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">No Order</p>
                <p class="mt-2 font-semibold text-slate-900">{{ $invoice->order->order_no }}</p>
                <p class="text-sm text-slate-500">Status: {{ ucfirst($invoice->order->status) }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Kuantiti</th>
                        <th>Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->order->items as $item)
                        <tr>
                            <td>{{ $item->design->name }} ({{ $item->size->name }})</td>
                            <td>{{ $item->quantity }}</td>
                            <td class="font-semibold text-brand-600">RM {{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="text-right">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Jumlah Bayaran</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-brand-600">RM {{ number_format($invoice->amount, 2) }}</p>
        </div>
    </section>
</div>
@endsection
