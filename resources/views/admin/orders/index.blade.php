@extends('layouts.admin')

@section('title', 'Pengurusan Tempahan')

@section('content')
<!-- Page Header -->
<div class="mb-10 flex flex-col xl:flex-row xl:items-center justify-between gap-6 border-b-2 border-slate-100 pb-8">
    <div>
        <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase leading-none mb-2">Pusat Kawalan Tempahan</h2>
        <p class="text-[11px] font-black text-slate-400 tracking-widest uppercase">Pantau pesanan, bayaran, dan logistik secara masa nyata.</p>
    </div>
    
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <!-- Filter Form -->
        <form method="get" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative group">
                <select name="status" class="flat-input w-full sm:w-56 h-12 py-0 pl-5 pr-10 text-[10px] font-black uppercase tracking-widest bg-white cursor-pointer appearance-none">
                    <option value="">SEMUA STATUS</option>
                    @foreach(['pending','paid','processing','shipped','completed','cancelled'] as $s)
                        <option value="{{ $s }}" {{ ($status ?? '') === $s ? 'selected' : '' }}>{{ strtoupper($s) }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                </div>
            </div>
            <button type="submit" class="flat-btn-primary !h-12 px-8 text-[11px] font-black uppercase tracking-widest">
                TAPIS
            </button>
        </form>
    </div>
</div>

<!-- Main Table Section -->
<div class="flat-card !p-0 overflow-hidden animate-in fade-in duration-500">
    <div class="overflow-x-auto min-h-[400px]">
        <table class="min-w-full divide-y divide-slate-100">
            <thead>
                <tr class="bg-slate-50">
                    <th scope="col" class="py-5 pl-8 pr-4 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest">ID TEMPAHAN</th>
                    <th scope="col" class="px-4 py-5 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest">INFO PELANGGAN</th>
                    <th scope="col" class="px-4 py-5 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest">STATUS CAKERA</th>
                    <th scope="col" class="px-4 py-5 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest">LOGISTIK TRACKING</th>
                    <th scope="col" class="px-4 py-5 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest">TOTAL</th>
                    <th scope="col" class="py-5 pl-4 pr-8 text-right text-[10px] font-black text-slate-900 uppercase tracking-widest">TINDAKAN</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 bg-white">
                @forelse($orders as $order)
                    <tr class="hover:bg-slate-50 transition-colors group/row">
                        <td class="whitespace-nowrap py-6 pl-8 pr-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-slate-900 italic tracking-tight uppercase leading-none mb-1">ST-{{ $order->order_no }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $order->created_at->format('d/m/Y') }}</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-6">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-sm bg-slate-900 flex items-center justify-center text-brand font-black text-sm border-b-2 border-slate-700">
                                    {{ substr($order->customer_name, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-black text-slate-900 uppercase tracking-tight leading-none mb-1">{{ $order->customer_name }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 tracking-widest italic leading-none">{{ $order->customer_phone }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-6">
                            @php
                                $statusColors = match($order->status) {
                                    'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
                                    'paid' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
                                    'processing' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200'],
                                    'shipped' => ['bg' => 'bg-brand-50', 'text' => 'text-brand', 'border' => 'border-brand-200'],
                                    'completed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
                                    'cancelled' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200'],
                                    default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200'],
                                };
                            @endphp
                            <span class="inline-flex items-center gap-2 rounded-sm {{ $statusColors['bg'] }} {{ $statusColors['text'] }} px-3 py-1.5 text-[9px] font-black uppercase tracking-widest border border-current/20 border-b-2 border-current/40">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-6">
                            @if($order->tracking_no)
                                <div class="flex items-center gap-3">
                                    <span class="text-[11px] font-black text-slate-800 tracking-widest bg-slate-100 px-2 py-1 border border-slate-200">{{ $order->tracking_no }}</span>
                                    <button onclick="navigator.clipboard.writeText('{{ $order->tracking_no }}')" class="h-7 w-7 rounded-sm bg-white flex items-center justify-center text-slate-400 hover:text-slate-900 transition-all border border-slate-200 hover:border-slate-900 shadow-sm" title="Salin">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" /></svg>
                                    </button>
                                </div>
                            @else
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest italic">BELUMADA INFO</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-6">
                            <span class="text-sm font-black text-slate-900 tracking-tighter italic">RM {{ number_format($order->total, 2) }}</span>
                        </td>
                        <td class="whitespace-nowrap py-6 pl-4 pr-8 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center justify-center rounded-sm bg-white px-5 py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-900 border-2 border-slate-900 transition-all hover:bg-slate-900 hover:text-white active:translate-y-0.5 group/btn">
                                PERINCIAN
                                <svg class="h-3.5 w-3.5 ml-2 transition-transform group-hover/btn:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-32 text-center bg-slate-50">
                            <div class="flex flex-col items-center">
                                <div class="h-20 w-20 bg-white rounded-sm flex items-center justify-center text-slate-200 mb-6 border-2 border-slate-200 border-dashed">
                                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                </div>
                                <h3 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tighter">DATA KOSONG</h3>
                                <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">Tiada rekod tempahan ditemui dalam sistem.</p>
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
<div class="mt-10">
    {{ $orders->links() }}
</div>
@endif

@endsection


