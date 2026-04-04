@extends('layouts.frontend')

@section('title', 'Tempahan Berjaya!')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <!-- Celebratory Header -->
    <div class="text-center mb-12 animate-in fade-in zoom-in duration-1000">
        <div class="relative inline-block mb-8">
            <div class="absolute inset-0 rounded-full bg-emerald-400 blur-2xl opacity-20 animate-pulse"></div>
            <div class="relative mx-auto flex h-24 w-24 items-center justify-center rounded-3xl bg-emerald-500 text-white shadow-2xl shadow-emerald-200">
                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <!-- Decorative particles (CSS only) -->
            <div class="absolute -top-2 -right-2 h-4 w-4 rounded-full bg-amber-400 animate-bounce"></div>
            <div class="absolute -bottom-4 -left-2 h-3 w-3 rounded-full bg-brand-400 animate-pulse"></div>
        </div>
        <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-4 sm:text-5xl">Tempahan <span class="text-emerald-600">Berjaya!</span></h1>
        <p class="text-slate-500 text-lg font-medium max-w-md mx-auto">Terima kasih, tempahan anda telah kami terima dan sedang diproses dengan penuh kasih sayang.</p>
    </div>

    <!-- Success Card -->
    <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/60 ring-1 ring-slate-200 overflow-hidden mb-12 animate-in fade-in slide-in-from-bottom-8 duration-700 delay-200">
        <!-- Card Header -->
        <div class="bg-gradient-to-br from-brand-600 to-brand-700 px-8 py-10 text-white relative overflow-hidden">
            <!-- Decorative Pattern -->
            <div class="absolute inset-0 opacity-10">
                <svg class="h-full w-full" fill="none" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <path d="M0 0L100 100M100 0L0 100" stroke="currentColor" stroke-width="2" />
                </svg>
            </div>
            <div class="relative flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="text-center sm:text-left">
                    <span class="text-brand-200 text-[10px] font-black uppercase tracking-[0.3em] block mb-2">ID Tempahan Anda</span>
                    <span class="text-3xl font-black tracking-widest leading-none">#{{ $order->order_no }}</span>
                </div>
                <div class="flex items-center gap-3 px-5 py-2.5 bg-white/10 rounded-2xl backdrop-blur-md border border-white/20">
                    <div class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></div>
                    <span class="text-xs font-black uppercase tracking-widest text-brand-50">Status: {{ strtoupper($order->status) }}</span>
                </div>
            </div>
        </div>

        <div class="p-8 sm:p-10">
            <div class="flex items-center gap-3 mb-8">
                <div class="h-1 w-8 rounded-full bg-brand-600"></div>
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em]">Ringkasan Pesanan</h2>
            </div>
            
            <div class="space-y-6 mb-10">
                @foreach($order->items as $item)
                    <div class="flex items-start justify-between gap-6 group">
                        <div class="flex gap-4">
                            <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center shrink-0 border border-slate-100 group-hover:bg-brand-50 group-hover:border-brand-100 transition-colors">
                                <svg class="h-6 w-6 text-slate-400 group-hover:text-brand-600 transition-all" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 group-hover:text-brand-600 transition-colors leading-tight mb-1">{{ $item->design->name }}</h3>
                                <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    <span>{{ $item->size->name }}</span>
                                    <span class="text-slate-200">/</span>
                                    <span>Qty: {{ $item->quantity }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="text-sm font-black text-slate-700 pt-1">RM {{ number_format($item->line_total, 2) }}</span>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-dashed border-slate-200 pt-8 space-y-4">
                <div class="flex items-center justify-between px-2">
                    <span class="text-xs text-slate-400 font-black uppercase tracking-widest">Tracking Number</span>
                    <span class="text-sm font-bold text-slate-900 bg-slate-50 px-3 py-1 rounded-lg border border-slate-100">{{ $order->tracking_no ?: 'Sedang Dikemaskini' }}</span>
                </div>
                <div class="flex items-center justify-between bg-slate-900 -mx-10 sm:-mx-12 px-10 sm:px-12 py-8 mt-6">
                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] leading-none">Jumlah Dibayar</span>
                    <span class="text-3xl font-black text-white leading-none italic tracking-tight">RM {{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 animate-in fade-in slide-in-from-bottom-10 duration-700 delay-300">
        <a href="{{ route('orders.lookup-form') }}" class="inline-flex items-center justify-center gap-3 rounded-2xl bg-white px-8 py-5 text-xs font-black uppercase tracking-widest text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 hover:ring-slate-300 transition-all shadow-sm active:scale-95 leading-none">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            Semak Status Tempahan
        </a>
        <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-3 rounded-2xl bg-brand-600 px-8 py-5 text-xs font-black uppercase tracking-widest text-white hover:bg-brand-700 transition-all shadow-xl shadow-brand-100 active:scale-95 leading-none">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            Kembali ke Utama
        </a>
    </div>

    <div class="mt-16 text-center animate-in fade-in duration-1000 delay-500">
        <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] mb-4">Ada sebarang masalah?</p>
        <a href="https://wa.me/yournumber" class="inline-flex items-center gap-2 text-brand-600 hover:text-brand-700 font-black text-sm transition-colors group">
            Hubungi Team Support Kami (WhatsApp)
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
            </svg>
        </a>
    </div>
</div>
@endsection


