@extends('layouts.admin')

@section('title', 'Ringkasan Sistem')

@section('content')
<!-- Page Header -->
<div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-8">
    <div>
        <h2 class="text-4xl font-black text-slate-900 tracking-tight mb-3 uppercase leading-none">Selamat Kembali, Admin 👋</h2>
        <p class="text-slate-500 font-medium tracking-wide text-lg max-w-2xl">Visualisasikan prestasi perniagaan dan urus logistik StickerTermurah anda dari satu pusat kawalan.</p>
    </div>
    <div class="flex items-center gap-3">
        <div class="bg-white rounded-[1.5rem] px-8 py-5 shadow-sm ring-1 ring-slate-200 flex flex-col items-end">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em] leading-none mb-2">STATUS MASA</span>
            <span class="text-lg font-black text-slate-900 leading-none tracking-tight uppercase">{{ date('d M Y') }}</span>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4 mb-12">
    <!-- Stat 1 -->
    <div class="relative group bg-white p-2 rounded-[2.5rem] shadow-sm ring-1 ring-slate-200 transition-all hover:shadow-2xl hover:shadow-brand-500/10 hover:-translate-y-1 overflow-hidden">
        <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-150 transition-transform duration-700">
            <svg class="h-20 w-20 text-brand-600" fill="currentColor" viewBox="0 0 24 24"><path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
        </div>
        <div class="p-8 flex flex-col gap-6">
            <div class="h-14 w-14 rounded-2xl bg-brand-600 flex items-center justify-center text-white shadow-xl shadow-brand-100 group-hover:rotate-[10deg] transition-transform duration-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
            </div>
            <div>
                <p class="text-3xl font-black text-slate-900 leading-none mb-2 tracking-tighter">{{ $totalOrders }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none">Jumlah Tempahan</p>
            </div>
        </div>
    </div>
    
    <!-- Stat 2 -->
    <div class="relative group bg-white p-2 rounded-[2.5rem] shadow-sm ring-1 ring-slate-200 transition-all hover:shadow-2xl hover:shadow-amber-500/10 hover:-translate-y-1 overflow-hidden font-jakarta">
        <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-150 transition-transform duration-700">
            <svg class="h-20 w-20 text-amber-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
        </div>
        <div class="p-8 flex flex-col gap-6">
            <div class="h-14 w-14 rounded-2xl bg-amber-500 flex items-center justify-center text-white shadow-xl shadow-amber-100 group-hover:rotate-[10deg] transition-transform duration-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </div>
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <p class="text-3xl font-black text-slate-900 leading-none tracking-tighter">{{ $pendingOrders }}</p>
                    <span class="text-[9px] font-black text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full ring-1 ring-amber-200/50 uppercase tracking-widest">BARU</span>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none">Tempahan Menunggu</p>
            </div>
        </div>
    </div>

    <!-- Stat 3 -->
    <div class="relative group bg-white p-2 rounded-[2.5rem] shadow-sm ring-1 ring-slate-200 transition-all hover:shadow-2xl hover:shadow-emerald-500/10 hover:-translate-y-1 overflow-hidden">
        <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-150 transition-transform duration-700">
            <svg class="h-20 w-20 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
        </div>
        <div class="p-8 flex flex-col gap-6">
            <div class="h-14 w-14 rounded-2xl bg-emerald-500 flex items-center justify-center text-white shadow-xl shadow-emerald-100 group-hover:rotate-[10deg] transition-transform duration-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
            </div>
            <div>
                <p class="text-3xl font-black text-slate-900 leading-none mb-2 tracking-tighter">{{ $totalDesigns }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none">Katalog Design</p>
            </div>
        </div>
    </div>

    <!-- Stat 4 -->
    <div class="relative group bg-white p-2 rounded-[2.5rem] shadow-sm ring-1 ring-slate-200 transition-all hover:shadow-2xl hover:shadow-brand-500/10 hover:-translate-y-1 overflow-hidden">
        <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-150 transition-transform duration-700">
            <svg class="h-20 w-20 text-brand-600" fill="currentColor" viewBox="0 0 24 24"><path d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.878.878 2.303.878 3.181 0l4.318-4.318c.878-.878.878-2.303 0-3.181l-9.581-9.581a2.25 2.25 0 00-1.591-.659z" /></svg>
        </div>
        <div class="p-8 flex flex-col gap-6">
            <div class="h-14 w-14 rounded-2xl bg-brand-500 flex items-center justify-center text-white shadow-xl shadow-brand-100 group-hover:rotate-[10deg] transition-transform duration-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.878.878 2.303.878 3.181 0l4.318-4.318c.878-.878.878-2.303 0-3.181l-9.581-9.581a2.25 2.25 0 00-1.591-.659z" /></svg>
            </div>
            <div>
                <p class="text-3xl font-black text-slate-900 leading-none mb-2 tracking-tighter">{{ $totalCategories }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none">Edisi Saiz</p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders Table Section -->
<div class="bg-white rounded-[2.5rem] shadow-sm ring-1 ring-slate-400 overflow-hidden animate-in fade-in slide-in-from-bottom-8 duration-700">
    <div class="px-10 py-10 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-slate-50/30">
        <div>
            <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-1">Tempahan Terbaru</h2>
            <p class="text-sm font-bold text-slate-500 uppercase tracking-[0.15em]">Log aktiviti pesanan masuk dalam tempoh terdekat</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-3 rounded-2xl bg-white px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-700 ring-1 ring-slate-400 shadow-sm hover:bg-slate-900 hover:text-white hover:ring-slate-900 hover:shadow-xl hover:shadow-slate-200 transition-all group">
            Lihat Arkib Penuh
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
            </svg>
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full border-separate border-spacing-0">
            <thead>
                <tr class="bg-white">
                    <th scope="col" class="py-6 pl-10 pr-4 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-200">IDENTITI</th>
                    <th scope="col" class="px-4 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-200">INFO PELANGGAN</th>
                    <th scope="col" class="px-4 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-200">STATUS ALIRAN</th>
                    <th scope="col" class="px-4 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-slate-200">NILAI (RM)</th>
                    <th scope="col" class="relative py-6 pl-4 pr-10 border-b border-slate-200 text-right">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($recentOrders as $order)
                    <tr class="hover:bg-brand-50/20 transition-all group/row">
                        <td class="whitespace-nowrap py-8 pl-10 pr-4">
                            <span class="text-base font-black text-brand-600 italic tracking-tight block mb-1">ST-{{ $order->order_no }}</span>
                            <div class="flex items-center gap-1.5 text-[9px] font-black text-slate-500 uppercase tracking-widest">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                {{ $order->created_at->format('d M, h:i A') }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-8">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-slate-800 uppercase tracking-tight leading-tight mb-1">{{ $order->customer_name }}</span>
                                <span class="text-xs text-slate-500 font-bold tracking-wide">{{ $order->customer_phone }}</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-8">
                            @php
                                $statusColors = match($order->status) {
                                    'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'dot' => 'bg-amber-500', 'ring' => 'ring-amber-200/50'],
                                    'processing' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'dot' => 'bg-blue-500', 'ring' => 'ring-blue-200/50'],
                                    'shipped' => ['bg' => 'bg-brand-50', 'text' => 'text-brand-700', 'dot' => 'bg-brand-500', 'ring' => 'ring-brand-200/50'],
                                    'completed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500', 'ring' => 'ring-emerald-200/50'],
                                    'cancelled' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'dot' => 'bg-rose-500', 'ring' => 'ring-rose-200/50'],
                                    default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'dot' => 'bg-slate-500', 'ring' => 'ring-slate-200/50'],
                                };
                            @endphp
                            <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-[10px] font-black uppercase tracking-[0.1em] {{ $statusColors['bg'] }} {{ $statusColors['text'] }} ring-1 ring-inset {{ $statusColors['ring'] }}">
                                <span class="h-2 w-2 rounded-full {{ $statusColors['dot'] }} {{ $order->status === 'processing' ? 'animate-pulse' : '' }}"></span>
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-8">
                            <span class="text-xl font-black text-slate-900 tracking-tight">RM {{ number_format($order->total, 2) }}</span>
                        </td>
                        <td class="relative whitespace-nowrap py-8 pl-4 pr-10 text-right text-sm">
                            <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-7 py-3 text-xs font-black uppercase tracking-widest text-slate-700 shadow-sm ring-1 ring-slate-400 transition-all hover:bg-brand-600 hover:text-white hover:ring-brand-600 hover:shadow-xl hover:shadow-brand-100 active:scale-95">
                                KEMASKINI
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-32 text-center">
                            <div class="flex flex-col items-center">
                                <div class="h-32 w-32 bg-slate-50 rounded-[3rem] flex items-center justify-center text-slate-400 mb-8 border border-dashed border-slate-400">
                                    <svg class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                </div>
                                <h3 class="text-3xl font-black text-slate-900 mb-3 uppercase tracking-tight">Belum Ada Rekod</h3>
                                <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">Perniagaan anda bersedia untuk menerima tempahan pertama.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection


