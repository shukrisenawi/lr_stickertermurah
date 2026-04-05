@extends('layouts.admin')

@section('title', 'Create Invoice')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Create Invoice Customer</h2>
                <p class="mt-2 text-xs font-medium text-slate-500">Pilih order customer yang belum ada invoice, kemudian klik create invoice.</p>
            </div>

            <form method="get" class="flex items-center gap-2 p-1.5 bg-white rounded-2xl shadow-sm ring-1 ring-slate-200">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari order no / nama / hp / email"
                    class="w-64 rounded-xl border-0 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-brand-600"
                >
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2 text-xs font-black uppercase tracking-widest text-white shadow-md hover:bg-brand-600 transition-all active:scale-95">
                    Cari
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar min-h-[420px]">
            <table class="min-w-full border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50/80">
                        <th class="py-4 pl-6 pr-4 text-left text-xs font-black text-slate-500 uppercase tracking-[0.15em] border-b border-slate-100">Order</th>
                        <th class="px-4 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-[0.15em] border-b border-slate-100">Customer</th>
                        <th class="px-4 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-[0.15em] border-b border-slate-100">Jumlah</th>
                        <th class="px-4 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-[0.15em] border-b border-slate-100">Nota Invoice</th>
                        <th class="px-4 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-[0.15em] border-b border-slate-100">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    @forelse($orders as $order)
                        <tr class="hover:bg-brand-50/20 transition-all align-top">
                            <td class="py-4 pl-6 pr-4">
                                <p class="text-sm font-black text-slate-800">{{ $order->order_no }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $order->created_at?->format('d M Y, h:i A') }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-sm font-black text-slate-800 uppercase">{{ $order->customer_name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $order->customer_phone }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $order->user?->email ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-4 text-sm font-black text-slate-900">RM {{ number_format((float) $order->total, 2) }}</td>
                            <td class="px-4 py-4">
                                <form method="post" action="{{ route('admin.invoices.store-from-menu') }}" class="space-y-2">
                                    @csrf
                                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                                    <textarea
                                        name="notes"
                                        rows="2"
                                        placeholder="Nota invoice (optional)"
                                        class="w-64 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-brand-600"
                                    ></textarea>
                            </td>
                            <td class="px-4 py-4">
                                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-black uppercase tracking-widest text-white hover:bg-emerald-700 transition-all">
                                        Create Invoice
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-20 text-center">
                                <p class="text-xl font-black text-slate-900 mb-1 uppercase tracking-tight">Tiada Order Untuk Invoice</p>
                                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Semua order sudah ada invoice atau tiada data padanan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($orders->hasPages())
    <div class="mt-8 px-2">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
