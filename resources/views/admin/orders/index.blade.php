@extends('layouts.admin')

@section('title', 'Pengurusan Tempahan')

@section('content')
<!-- Page Header -->
<div class="mb-10 flex flex-col xl:flex-row xl:items-end justify-between gap-8">
    <div>
        <h2 class="text-4xl font-black text-slate-900 tracking-tight mb-3 uppercase leading-none">Pusat Kawalan Tempahan</h2>
        <p class="text-slate-500 font-medium tracking-wide text-lg max-w-2xl">Pantau aliran pesanan masuk, status bayaran, dan logistik penghantaran secara masa nyata.</p>
    </div>
    
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
        <!-- Filter Form -->
        <form method="get" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 p-2 bg-white rounded-3xl shadow-sm ring-1 ring-slate-200">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.444-1.444c0 .54-.384 1.006-.917 1.096A48.32 48.32 0 0112 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18m-18 5h18m-18 5h18m-18 5h18" /></svg>
                </div>
                <select name="status" class="block w-full sm:w-56 rounded-2xl border-0 py-3.5 pl-11 pr-10 text-slate-900 ring-0 focus:ring-2 focus:ring-inset focus:ring-indigo-600 text-[10px] font-black uppercase tracking-[0.15em] bg-slate-50 cursor-pointer appearance-none transition-all">
                    <option value="">SEMUA STATUS PESANAN</option>
                    @foreach(['pending','paid','processing','shipped','completed','cancelled'] as $s)
                        <option value="{{ $s }}" {{ ($status ?? '') === $s ? 'selected' : '' }}>{{ strtoupper($s) }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                </div>
            </div>
            <button type="submit" class="rounded-2xl bg-slate-900 px-8 py-3.5 text-xs font-black uppercase tracking-widest text-white shadow-xl shadow-slate-100 hover:bg-indigo-600 hover:shadow-indigo-100 transition-all active:scale-95">
                TAPIS DATA
            </button>
        </form>
    </div>
</div>

<!-- Main Table Section -->
<div class="bg-white rounded-[2.5rem] shadow-sm ring-1 ring-slate-200 overflow-hidden animate-in fade-in slide-in-from-bottom-8 duration-700">
    <div class="overflow-x-auto custom-scrollbar min-h-[500px]">
        <table class="min-w-full border-separate border-spacing-0">
            <thead>
                <tr class="bg-slate-50/80">
                    <th scope="col" class="py-7 pl-10 pr-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">IDENTITI TEMPAHAN</th>
                    <th scope="col" class="px-4 py-7 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">INFO PELANGGAN</th>
                    <th scope="col" class="px-4 py-7 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">STATUS ALIRAN</th>
                    <th scope="col" class="px-4 py-7 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">LOGISTIK</th>
                    <th scope="col" class="px-4 py-7 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">NILAI (RM)</th>
                    <th scope="col" class="relative py-7 pl-4 pr-10 border-b border-slate-100 text-right">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 bg-white">
                @forelse($orders as $order)
                    <tr class="hover:bg-indigo-50/20 transition-all group/row">
                        <td class="whitespace-nowrap py-8 pl-10 pr-4">
                            <div class="flex flex-col">
                                <span class="text-base font-black text-indigo-600 italic tracking-tight mb-1">ST-{{ $order->order_no }}</span>
                                <div class="flex items-center gap-2 px-2 py-1 bg-slate-100 rounded-lg w-fit">
                                    <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider">{{ $order->created_at->format('d M, h:i A') }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-8">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center text-indigo-600 font-black text-lg ring-1 ring-slate-100 group-hover/row:bg-indigo-600 group-hover/row:text-white transition-all duration-300">
                                    {{ substr($order->customer_name, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-800 uppercase tracking-tight leading-tight">{{ $order->customer_name }}</span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="h-1.5 w-1.5 rounded-full bg-emerald-500"></div>
                                        <span class="text-xs font-bold text-slate-500 tracking-wide">{{ $order->customer_phone }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-8">
                            @php
                                $statusColors = match($order->status) {
                                    'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'dot' => 'bg-amber-500', 'ring' => 'ring-amber-200'],
                                    'paid' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500', 'ring' => 'ring-emerald-200'],
                                    'processing' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'dot' => 'bg-sky-500', 'ring' => 'ring-sky-200'],
                                    'shipped' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'dot' => 'bg-indigo-500', 'ring' => 'ring-indigo-200'],
                                    'completed' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'dot' => 'bg-emerald-600', 'ring' => 'ring-emerald-300'],
                                    'cancelled' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'dot' => 'bg-rose-500', 'ring' => 'ring-rose-200'],
                                    default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'dot' => 'bg-slate-500', 'ring' => 'ring-slate-200'],
                                };
                            @endphp
                            <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-[0.1em] {{ $statusColors['bg'] }} {{ $statusColors['text'] }} ring-1 ring-inset {{ $statusColors['ring'] }}">
                                <span class="h-2 w-2 rounded-full {{ $statusColors['dot'] }} {{ $order->status === 'processing' ? 'animate-pulse' : '' }}"></span>
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-8">
                            @if($order->tracking_no)
                                <div class="flex items-center gap-3">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-slate-800 tracking-[0.1em]">{{ $order->tracking_no }}</span>
                                        <span class="text-[9px] font-bold text-indigo-500 uppercase mt-0.5">READY TO TRACK</span>
                                    </div>
                                    <button onclick="navigator.clipboard.writeText('{{ $order->tracking_no }}')" class="h-8 w-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-all border border-slate-100 shadow-sm" title="Salin No Tracking">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" /></svg>
                                    </button>
                                </div>
                            @else
                                <div class="flex items-center gap-2 opacity-40">
                                    <div class="h-8 w-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-300 border border-dashed border-slate-300">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                    </div>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">TIADA INFO</span>
                                </div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-8">
                            <div class="flex flex-col">
                                <span class="text-xl font-black text-slate-900 tracking-tight leading-none">RM {{ number_format($order->total, 2) }}</span>
                                @if($order->status === 'paid')
                                    <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mt-1">SAH BAYAR</span>
                                @endif
                            </div>
                        </td>
                        <td class="relative whitespace-nowrap py-8 pl-4 pr-10 text-right text-sm">
                            <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-7 py-3 text-xs font-black uppercase tracking-widest text-slate-700 shadow-sm ring-1 ring-slate-200 transition-all hover:bg-slate-900 hover:text-white hover:shadow-xl hover:shadow-slate-200 active:scale-95 group/btn">
                                DETAILS
                                <svg class="h-4 w-4 ml-3 transition-transform group-hover/btn:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-32 text-center">
                            <div class="flex flex-col items-center">
                                <div class="h-32 w-32 bg-slate-50 rounded-[3rem] flex items-center justify-center text-slate-200 mb-8 border border-dashed border-slate-200">
                                    <svg class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                </div>
                                <h3 class="text-3xl font-black text-slate-900 mb-3 uppercase tracking-tight">Data Kosong</h3>
                                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Belum ada tempahan yang memenuhi kriteria pilihan anda.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination Section -->
@if($orders->hasPages())
<div class="mt-12 px-2">
    {{ $orders->links() }}
</div>
@endif

<style>
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }
</style>

@endsection

