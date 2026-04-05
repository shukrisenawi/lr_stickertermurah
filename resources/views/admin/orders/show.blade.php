@extends('layouts.admin')

@section('title', 'Perincian Tempahan: ' . $order->order_no)

@section('content')
<!-- Page Header -->
<div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.orders.index') }}" class="group h-12 w-12 flex items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-300 hover:ring-brand-300 hover:bg-slate-50 transition-all">
            <svg class="h-5 w-5 text-slate-500 group-hover:text-brand-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0l7.5-7.5M3 12h18" /></svg>
        </a>
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight uppercase leading-none mb-1">Perincian Tempahan</h2>
            <div class="flex items-center gap-3">
                <span class="text-xs font-black text-brand-600 italic tracking-widest bg-brand-50 px-3 py-1 rounded-full ring-1 ring-brand-200">#{{ $order->order_no }}</span>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-[0.2em]">{{ $order->created_at->format('d M Y, h:i A') }}</span>
            </div>
        </div>
    </div>
    
    <div class="flex items-center gap-3">
        @php
            $statusColors = match($order->status) {
                'pending' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'dot' => 'bg-amber-500'],
                'paid' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500'],
                'processing' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'dot' => 'bg-blue-500'],
                'shipped' => ['bg' => 'bg-brand-100', 'text' => 'text-brand-700', 'dot' => 'bg-brand-500'],
                'completed' => ['bg' => 'bg-emerald-100/50', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500'],
                'cancelled' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'dot' => 'bg-rose-500'],
                default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'dot' => 'bg-slate-500'],
            };
        @endphp
        <div class="inline-flex items-center gap-2 rounded-2xl {{ $statusColors['bg'] }} px-5 py-3 shadow-sm">
            <span class="h-2 w-2 rounded-full {{ $statusColors['dot'] }} animate-pulse"></span>
            <span class="text-xs font-black uppercase tracking-widest {{ $statusColors['text'] }}">{{ $order->status }}</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-8 animate-in fade-in slide-in-from-left-8 duration-700">
        <!-- Customer Info Card -->
        <div class="bg-white rounded-[2.5rem] shadow-sm ring-1 ring-slate-200 overflow-hidden transform transition-all duration-500 hover:shadow-xl hover:shadow-brand-500/5">
            <div class="px-10 py-8 border-b border-slate-200 bg-slate-100 flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-brand-600 ring-1 ring-slate-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">Maklumat Pelanggan</h2>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-[0.2em] mt-0.5">Identiti dan alamat penghantaran</p>
                </div>
            </div>
            
            <div class="p-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <div class="space-y-6">
                        <div class="group">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block group-hover:text-brand-500 transition-colors">Nama Penuh</span>
                            <p class="text-2xl font-black text-slate-900 capitalize tracking-tight leading-none">{{ $order->customer_name }}</p>
                        </div>
                        
                        <div class="group">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block group-hover:text-brand-500 transition-colors">Nombor Telefon</span>
                            <div class="flex items-center gap-4">
                                <p class="text-xl font-black text-brand-600 tracking-wider leading-none italic">{{ $order->customer_phone }}</p>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->customer_phone) }}" target="_blank" class="h-8 w-8 rounded-lg bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-200 hover:scale-110 active:scale-95 transition-all">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" /></svg>
                                </a>
                            </div>
                        </div>

                        <div class="group">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 block group-hover:text-brand-500 transition-colors">Alamat Lengkap</span>
                            <div class="p-6 bg-slate-100 rounded-3xl border border-slate-200 text-slate-700 font-bold leading-relaxed italic relative overflow-hidden group-hover:shadow-md transition-all">
                                <div class="absolute top-0 right-0 p-3 text-slate-300">
                                    <svg class="h-10 w-10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" /></svg>
                                </div>
                                {{ $order->customer_address }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="group">
                             <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 block">Material Sticker</span>
                             <div class="inline-flex items-center gap-3 px-6 py-4 bg-slate-100 rounded-2xl border border-slate-200">
                                <svg class="h-5 w-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-5.25v9" /></svg>
                                <span class="text-lg font-black text-slate-900 tracking-tight">{{ $order->material }}</span>
                             </div>
                        </div>

                        <div class="group">
                             <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 block">Permintaan Khas</span>
                             <div class="p-5 bg-slate-100 rounded-[1.5rem] border border-slate-200 text-slate-700 font-medium italic min-h-[5rem] relative">
                                <svg class="absolute -top-3 -right-3 h-8 w-8 text-slate-200" fill="currentColor" viewBox="0 0 24 24"><path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z" /></svg>
                                {{ $order->custom_request ?: 'Tiada sebarang permintaan khas daripada pelanggan.' }}
                             </div>
                        </div>

                        @if($order->payment_receipt_path)
                            <div class="pt-4">
                                <a href="{{ asset('storage/'.$order->payment_receipt_path) }}" target="_blank" class="inline-flex w-full items-center justify-center gap-3 rounded-2xl bg-slate-900 px-6 py-4 text-sm font-black text-white hover:bg-brand-600 transition-all shadow-xl shadow-slate-200 group/receipt">
                                    <svg class="h-5 w-5 transition-transform group-hover/receipt:scale-125" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    LIHAT RESIT PEMBAYARAN
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items Section -->
        <div class="bg-white rounded-[2.5rem] shadow-sm ring-1 ring-slate-300 overflow-hidden transform transition-all duration-500 hover:shadow-xl hover:shadow-brand-500/5">
            <div class="px-10 py-8 border-b border-slate-200 bg-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-brand-600 ring-1 ring-slate-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">Kandungan Tempahan</h2>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-[0.2em] mt-0.5">Senarai item dan pengiraan harga</p>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr class="bg-white">
                            <th scope="col" class="py-6 pl-10 pr-3 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Penerangan Design</th>
                            <th scope="col" class="px-3 py-6 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Spesifikasi Saiz</th>
                            <th scope="col" class="px-3 py-6 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Kuantiti</th>
                            <th scope="col" class="px-3 py-6 text-right text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Harga Unit</th>
                            <th scope="col" class="py-6 pl-3 pr-10 text-right text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($order->items as $item)
                        <tr class="group/row hover:bg-slate-50 transition-all">
                            <td class="whitespace-nowrap py-7 pl-10 pr-3 text-sm font-black text-slate-900 tracking-tight">{{ $item->design->name }}</td>
                            <td class="whitespace-nowrap px-3 py-7 text-sm font-bold text-slate-500 uppercase tracking-wider">{{ $item->size->name }}</td>
                            <td class="whitespace-nowrap px-3 py-7 text-sm text-center">
                                <span class="inline-flex items-center justify-center h-8 w-12 rounded-lg bg-brand-50 text-brand-700 font-black italic shadow-inner border border-brand-100">{{ $item->quantity }}</span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-7 text-sm text-slate-400 font-bold text-right tracking-wider italic">RM {{ number_format($item->unit_price, 2) }}</td>
                            <td class="whitespace-nowrap py-7 pl-3 pr-10 text-right">
                                <span class="text-base font-black text-slate-900 italic tracking-wider">RM {{ number_format($item->line_total, 2) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-100">
                            <td colspan="4" class="py-10 pl-10 pr-3 text-right">
                                <span class="text-xs font-black text-slate-500 uppercase tracking-[0.3em] mr-4">Jumlah Keseluruhan (NETT)</span>
                            </td>
                            <td class="py-10 pl-3 pr-10 text-right">
                                <div class="inline-flex flex-col items-end">
                                    <span class="text-[3rem] font-black text-brand-600 leading-none italic tracking-tighter">RM {{ number_format($order->total, 2) }}</span>
                                    <span class="text-[10px] font-black text-brand-400 uppercase tracking-[0.2em] mt-2">Termasuk caj penghantaran & servis</span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar Actions -->
    <div class="space-y-8 animate-in fade-in slide-in-from-right-8 duration-700">
        <!-- Update Form Card -->
        <div class="bg-white rounded-[2.5rem] shadow-sm ring-1 ring-slate-200 overflow-hidden transform transition-all duration-500 hover:shadow-xl">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/30">
                <h2 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em]">Tindakan Pantas</h2>
            </div>
            <div class="p-8">
                <form method="post" action="{{ route('admin.orders.update', $order) }}" class="space-y-8">
                    @csrf @method('put')
                    <div class="group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 pl-1 group-hover:text-brand-600 transition-colors">Tukar Status Pesanan</label>
                        <div class="relative">
                            <select name="status" class="block w-full rounded-2xl border-0 py-4 pl-5 pr-12 text-slate-900 font-black uppercase tracking-widest ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-brand-600 shadow-sm appearance-none cursor-pointer text-xs" required>
                                @foreach(['pending','paid','processing','shipped','completed','cancelled'] as $s)
                                    <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ strtoupper($s) }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 pl-1 group-hover:text-brand-600 transition-colors">Nombor Tracking (Courier)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-300">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.129-1.125V11.25c0-1.58-1.282-2.812-2.82-2.863l-2.008-.066a2.25 2.25 0 0 0-1.898 1.144l-1.642 2.736a2.25 2.25 0 0 1-1.928 1.091H10.5" /></svg>
                            </div>
                            <input type="text" name="tracking_no" value="{{ $order->tracking_no }}" class="block w-full rounded-2xl border-0 py-4 pl-12 px-4 text-slate-900 font-black tracking-widest ring-1 ring-inset ring-slate-200 placeholder:text-slate-300 focus:ring-2 focus:ring-brand-600 text-xs shadow-inner" placeholder="MASUKKAN NO TRACKING">
                        </div>
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-5 text-sm font-black text-white shadow-xl shadow-slate-200 hover:bg-brand-600 hover:shadow-brand-100 transition-all active:scale-95 group/save">
                        SIMPAN PERUBAHAN
                        <svg class="h-4 w-4 ml-3 transition-transform group-hover/save:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Invoice Card -->
        <div class="relative group bg-slate-900 rounded-[2.5rem] shadow-2xl shadow-brand-200/50 overflow-hidden text-white p-1">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-600 via-brand-900 to-slate-900 opacity-90"></div>
            
            <div class="relative px-8 py-10">
                <div class="flex items-center gap-3 mb-8">
                    <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-white/10 border border-white/20 backdrop-blur-md">
                        <svg class="h-5 w-5 text-brand-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    </div>
                    <h2 class="text-lg font-black uppercase tracking-widest text-white/90">Sistem Invois</h2>
                </div>

                @if($order->invoice)
                    <div class="mb-10 text-center">
                        <span class="text-[10px] font-black text-brand-300 uppercase tracking-widest block mb-1 opacity-60">Status Invois: TERJANA</span>
                        <span class="text-3xl font-black tracking-tight leading-none italic text-white drop-shadow-sm">{{ $order->invoice->invoice_no }}</span>
                    </div>
                    <a href="{{ route('admin.invoices.show', $order->invoice) }}" target="_blank" class="w-full inline-flex items-center justify-center gap-3 rounded-2xl bg-white px-6 py-5 text-sm font-black text-slate-900 hover:bg-brand-50 transition-all border-b-4 border-slate-300 active:border-b-0 active:translate-y-1">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231a1.125 1.125 0 0 1-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
                        CETAK SEKARANG
                    </a>
                @else
                    <form method="post" action="{{ route('admin.invoices.store', $order) }}" class="space-y-6">
                        @csrf
                        <div class="group">
                            <label class="block text-[10px] font-black text-white/50 uppercase tracking-[0.2em] mb-3 pl-1">Nota Kaki Invois</label>
                            <textarea name="notes" rows="3" class="block w-full rounded-2xl border-0 py-4 px-5 bg-white/5 text-white font-bold tracking-wider ring-1 ring-inset ring-white/20 placeholder:text-white/20 focus:ring-2 focus:ring-white/40 text-xs leading-relaxed shadow-inner" placeholder="Tulis sesuatu untuk pelanggan..."></textarea>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-3 rounded-2xl bg-brand-500 px-6 py-5 text-sm font-black text-white shadow-2xl hover:bg-brand-400 transition-all active:scale-95">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            JANA INVOIS RASMI
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


