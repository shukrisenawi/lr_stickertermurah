@extends('layouts.frontend')

@section('title', 'Semak Status & Repeat Order')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header Section -->
    <div class="text-center mb-12 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-brand-50 text-brand-600 mb-6 shadow-sm ring-1 ring-brand-100">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </div>
        <h1 class="text-4xl font-black text-slate-900 tracking-tight sm:text-5xl">Semak Rekod <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-brand-400">Tempahan</span></h1>
        <p class="mt-4 text-slate-600 text-lg font-medium max-w-xl mx-auto">Masukkan nombor telefon anda untuk melihat sejarah tempahan, status terkini, dan buat tempahan ulangan dengan mudah.</p>
    </div>

    <!-- Search Form Card -->
    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/60 ring-1 ring-slate-400/30 p-8 sm:p-10 mb-16 animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100">
        <form method="post" action="{{ route('orders.lookup') }}">
            @csrf
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-black text-slate-700 uppercase tracking-widest mb-3 ml-1">No. Telefon Pelanggan</label>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="relative flex-1 group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-slate-500 group-focus-within:text-brand-600 transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                </svg>
                            </div>
                            <input type="text" name="customer_phone" value="{{ old('customer_phone', $customerPhone ?? '') }}" required
                                class="block w-full rounded-2xl border-0 py-4.5 pl-13 pr-5 text-slate-900 ring-1 ring-inset ring-slate-400/50 placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm transition-all shadow-sm bg-slate-100"
                                placeholder="Contoh: 0111223344">
                        </div>
                        <button type="submit" class="rounded-2xl bg-brand-600 px-10 py-4.5 text-sm font-black text-white shadow-lg shadow-brand-200 hover:bg-brand-700 hover:shadow-brand-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 transition-all active:scale-95 flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                            Cari Rekod
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    @if(isset($orders))
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-8 duration-700">
            @if($orders->isEmpty())
                <div class="text-center py-20 px-6 bg-white rounded-[2.5rem] border border-slate-200 shadow-sm">
                    <div class="mx-auto h-24 w-24 text-slate-200 mb-6 bg-slate-50 rounded-full flex items-center justify-center">
                        <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900">Tiada rekod ditemui</h3>
                    <p class="text-slate-600 mt-2 max-w-sm mx-auto font-medium">Kami tidak menjumpai sebarang tempahan berdaftar di bawah nombor telefon tersebut.</p>
                </div>
            @else
                <div class="flex items-center justify-between mb-2 px-6">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-1.5 rounded-full bg-brand-600"></div>
                        <h2 class="text-lg font-black text-slate-900 uppercase tracking-widest">{{ $orders->count() }} Rekod Ditemui</h2>
                    </div>
                </div>
                
                @foreach($orders as $order)
                    <div class="group bg-white rounded-[2.5rem] shadow-sm ring-1 ring-slate-200 overflow-hidden transition-all hover:ring-brand-300 hover:shadow-2xl hover:shadow-brand-500/10">
                        <div class="p-8 sm:p-10">
                            <!-- Order Info Header -->
                            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-8">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-3 mb-1">
                                        <div class="px-3 py-1 rounded-full bg-brand-50 border border-brand-100 text-brand-700 text-[10px] font-black uppercase tracking-widest">
                                            #{{ $order->order_no }}
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-500">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ $order->created_at->format('d M Y, h:i A') }}
                                        </div>
                                    </div>
                                    <h3 class="text-2xl font-black text-slate-900 capitalize leading-none">{{ $order->customer_name }}</h3>
                                </div>
                                <div class="flex items-center gap-3">
                                    @php
                                        $statusConfig = match($order->status) {
                                            'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'ring' => 'ring-amber-600/20', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                                            'processing' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'ring' => 'ring-blue-600/20', 'icon' => 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0'],
                                            'shipped' => ['bg' => 'bg-brand-50', 'text' => 'text-brand-700', 'ring' => 'ring-brand-600/20', 'icon' => 'M8.25 18.75a1.5 1.5 0 01-3 0m13.5 0a1.5 1.5 0 01-3 0m-13.5 0H5.25m3.75 0h6m3.75 0h1.5m-14.75-2.25L3.374 4.5H2.25m1.124 0h11.232L17.25 12h-4.5V4.5H6.75v3.375M16.5 12h3.375a.375.375 0 01.375.375v2.25a.375.375 0 01-.375.375h-3.375m-6 3.75h3.75a.375.375 0 01.375.375v2.25a.375.375 0 01-.375.375h-3.375'],
                                            'completed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-600/20', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                            'cancelled' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'ring' => 'ring-rose-600/20', 'icon' => 'M6 18L18 6M6 6l12 12'],
                                            default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'ring' => 'ring-slate-600/20', 'icon' => 'M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-black uppercase tracking-wider ring-1 ring-inset {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} {{ $statusConfig['ring'] }}">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $statusConfig['icon'] }}" />
                                        </svg>
                                        {{ $order->status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Details Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                                <div class="bg-slate-50 px-6 py-5 rounded-3xl border border-slate-100 transition-colors group-hover:bg-brand-50/50 group-hover:border-brand-100/50">
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 block mb-2">Tracking Number</span>
                                    @if($order->tracking_no)
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-bold text-slate-900 tracking-tight">{{ $order->tracking_no }}</span>
                                            <button onclick="navigator.clipboard.writeText('{{ $order->tracking_no }}')" class="p-1.5 rounded-lg hover:bg-white text-slate-400 hover:text-brand-600 transition-colors" title="Copy Tracking No">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                                </svg>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-sm font-bold text-slate-500">Belum Tersedia</span>
                                    @endif
                                </div>
                                <div class="bg-brand-600 px-6 py-5 rounded-3xl border border-brand-500 shadow-lg shadow-brand-100 transition-transform group-hover:scale-[1.02]">
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-200 block mb-2">Jumlah Bayaran</span>
                                    <span class="text-xl font-black text-white italic tracking-tight">RM {{ number_format($order->total, 2) }}</span>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center gap-6 pt-6 border-t border-slate-100">
                                @if($order->invoice)
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Invoice No</span>
                                        <span class="text-xs font-bold text-slate-700">{{ $order->invoice->invoice_no }}</span>
                                    </div>
                                @endif
                                <div class="flex-1"></div>
                                <a href="{{ route('orders.repeat', $order) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 rounded-2xl bg-slate-900 px-8 py-4 text-xs font-black uppercase tracking-widest text-white hover:bg-brand-600 transition-all shadow-xl shadow-slate-200 hover:shadow-brand-200 active:scale-95 group">
                                    <svg class="h-4 w-4 transition-transform group-hover:rotate-180 duration-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    Repeat Order Ini
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endif
</div>
@endsection


