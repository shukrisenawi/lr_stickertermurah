@extends('layouts.admin')

@section('title', 'Ringkasan Sistem')

@section('content')
<!-- Page Header -->
<div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6 border-b-2 border-slate-100 pb-8">
    <div>
        <h2 class="text-3xl font-black text-slate-900 tracking-tighter mb-2 uppercase leading-none">Selamat Kembali, Admin 👋</h2>
        <p class="text-slate-500 font-bold tracking-tight text-sm max-w-2xl uppercase">Prestasi perniagaan dan logistik StickerTermurah anda.</p>
    </div>
    <div class="flex items-center gap-2">
        <div class="bg-white border-2 border-slate-900 rounded-sm px-5 py-3 flex flex-col items-end">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">STATUS MASA</span>
            <span class="text-sm font-black text-slate-900 leading-none tracking-tight uppercase">{{ date('d M Y') }}</span>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-10">
    <!-- Stat 1 -->
    <div class="flat-card group relative">
        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
            <svg class="h-16 w-16 text-slate-900" fill="currentColor" viewBox="0 0 24 24"><path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
        </div>
        <div class="flex flex-col gap-5">
            <div class="h-12 w-12 rounded-sm bg-brand flex items-center justify-center text-white border-b-4 border-brand-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
            </div>
            <div>
                <p class="text-3xl font-black text-slate-900 leading-none mb-2 tracking-tighter">{{ $totalOrders }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Jumlah Tempahan</p>
            </div>
        </div>
    </div>
    
    <!-- Stat 2 -->
    <div class="flat-card group relative">
        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
            <svg class="h-16 w-16 text-slate-900" fill="currentColor" viewBox="0 0 24 24"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
        </div>
        <div class="flex flex-col gap-5">
            <div class="h-12 w-12 rounded-sm bg-amber-500 flex items-center justify-center text-white border-b-4 border-amber-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <p class="text-3xl font-black text-slate-900 leading-none tracking-tighter">{{ $pendingOrders }}</p>
                    <span class="text-[9px] font-black text-amber-600 bg-amber-50 px-2 py-0.5 rounded-sm border border-amber-200 uppercase tracking-widest">BARU</span>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Tempahan Menunggu</p>
            </div>
        </div>
    </div>

    <!-- Stat 3 -->
    <div class="flat-card group relative">
        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
            <svg class="h-16 w-16 text-slate-900" fill="currentColor" viewBox="0 0 24 24"><path d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
        </div>
        <div class="flex flex-col gap-5">
            <div class="h-12 w-12 rounded-sm bg-emerald-500 flex items-center justify-center text-white border-b-4 border-emerald-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
            </div>
            <div>
                <p class="text-3xl font-black text-slate-900 leading-none mb-2 tracking-tighter">{{ $totalDesigns }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Katalog Design</p>
            </div>
        </div>
    </div>

    <!-- Stat 4 -->
    <div class="flat-card group relative">
        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
            <svg class="h-16 w-16 text-slate-900" fill="currentColor" viewBox="0 0 24 24"><path d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.878.878 2.303.878 3.181 0l4.318-4.318c.878-.878.878-2.303 0-3.181l-9.581-9.581a2.25 2.25 0 00-1.591-.659z" /></svg>
        </div>
        <div class="flex flex-col gap-5">
            <div class="h-12 w-12 rounded-sm bg-indigo-500 flex items-center justify-center text-white border-b-4 border-indigo-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.878.878 2.303.878 3.181 0l4.318-4.318c.878-.878.878-2.303 0-3.181l-9.581-9.581a2.25 2.25 0 00-1.591-.659z" /></svg>
            </div>
            <div>
                <p class="text-3xl font-black text-slate-900 leading-none mb-2 tracking-tighter">{{ $totalCategories }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Edisi Saiz</p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders Table Section -->
<div class="flat-card border-slate-900 border-2 !p-0 overflow-hidden">
    <div class="px-6 py-6 border-b-2 border-slate-900 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50">
        <div>
            <h2 class="text-xl font-black text-slate-900 uppercase tracking-tighter mb-0.5">Tempahan Terbaru</h2>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Log aktiviti pesanan masuk dalam tempoh terdekat</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="flat-btn-primary !px-5 !py-2.5 !text-[11px]">
            Lihat Arkib Penuh
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
            </svg>
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full border-separate border-spacing-0">
            <thead>
                <tr class="bg-white">
                    <th scope="col" class="py-5 pl-6 pr-4 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest border-b-2 border-slate-100">IDENTITI</th>
                    <th scope="col" class="px-4 py-5 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest border-b-2 border-slate-100">INFO PELANGGAN</th>
                    <th scope="col" class="px-4 py-5 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest border-b-2 border-slate-100">STATUS ALIRAN</th>
                    <th scope="col" class="px-4 py-5 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest border-b-2 border-slate-100">NILAI (RM)</th>
                    <th scope="col" class="relative py-5 pl-4 pr-6 border-b-2 border-slate-100 text-right">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($recentOrders as $order)
                    <tr class="hover:bg-slate-50 transition-colors group/row">
                        <td class="whitespace-nowrap py-6 pl-6 pr-4">
                            <span class="text-sm font-black text-brand italic tracking-tight block mb-1">ST-{{ $order->order_no }}</span>
                            <div class="flex items-center gap-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                {{ $order->created_at->format('d M, h:i A') }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-6">
                            <div class="flex flex-col">
                                <span class="text-xs font-black text-slate-800 uppercase tracking-tight leading-tight mb-0.5">{{ $order->customer_name }}</span>
                                <span class="text-[10px] text-slate-400 font-bold tracking-widest uppercase">{{ $order->customer_phone }}</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-6">
                            @php
                                $statusColors = match($order->status) {
                                    'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
                                    'processing' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200'],
                                    'shipped' => ['bg' => 'bg-brand-50', 'text' => 'text-brand', 'border' => 'border-brand-200'],
                                    'completed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
                                    'cancelled' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200'],
                                    default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200'],
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-sm px-2.5 py-1 text-[10px] font-black uppercase tracking-widest {{ $statusColors['bg'] }} {{ $statusColors['text'] }} border {{ $statusColors['border'] }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-6">
                            <span class="text-xl font-black text-slate-900 tracking-tighter">RM{{ number_format($order->total, 2) }}</span>
                        </td>
                        <td class="relative whitespace-nowrap py-6 pl-4 pr-6 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex h-9 items-center justify-center rounded-sm bg-white px-5 text-[11px] font-black uppercase tracking-widest text-slate-900 border-2 border-slate-900 transition-all hover:bg-slate-900 hover:text-white active:scale-95">
                                KEMASKINI
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-24 text-center">
                            <div class="flex flex-col items-center">
                                <div class="h-24 w-24 bg-slate-50 rounded-sm flex items-center justify-center text-slate-200 mb-6 border-2 border-dashed border-slate-200">
                                    <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                </div>
                                <h3 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tighter">Belum Ada Rekod</h3>
                                <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">Perniagaan anda bersedia untuk menerima tempahan pertama.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection


