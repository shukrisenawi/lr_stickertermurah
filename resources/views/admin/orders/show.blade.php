@extends('layouts.admin')

@section('title', 'Perincian Tempahan: ' . $order->order_no)

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.orders.index') }}" class="group h-10 w-10 flex items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-slate-300 hover:ring-brand-300 hover:bg-slate-50 transition-all">
            <svg class="h-4 w-4 text-slate-500 group-hover:text-brand-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0l7.5-7.5M3 12h18" /></svg>
        </a>
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase leading-none mb-1">Perincian Tempahan</h2>
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-black text-brand-600 italic tracking-widest bg-brand-50 px-2 py-0.5 rounded-full ring-1 ring-brand-200">#{{ $order->order_no }}</span>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">{{ $order->created_at->format('d M Y, h:i A') }}</span>
            </div>
        </div>
    </div>
    
    <div class="flex items-center gap-2">
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
        <div class="inline-flex items-center gap-2 rounded-xl {{ $statusColors['bg'] }} px-4 py-2 shadow-sm ring-1 ring-inset ring-white/20">
            <span class="h-1.5 w-1.5 rounded-full {{ $statusColors['dot'] }} animate-pulse"></span>
            <span class="text-[11px] font-black uppercase tracking-widest {{ $statusColors['text'] }}">{{ $order->status }}</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6 animate-in fade-in slide-in-from-left-4 duration-500">
        <!-- Customer Info Card -->
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-white shadow-sm flex items-center justify-center text-brand-600 ring-1 ring-slate-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                </div>
                <div>
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-tight">Maklumat Pelanggan</h2>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Identiti dan alamat</p>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <div class="group">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block group-hover:text-brand-500 transition-colors">Nama Penuh</span>
                            <p class="text-lg font-black text-slate-900 capitalize tracking-tight leading-none">{{ $order->customer_name }}</p>
                        </div>
                        
                        <div class="group">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block group-hover:text-brand-500 transition-colors">Nombor Telefon</span>
                            <div class="flex items-center gap-3">
                                <p class="text-base font-black text-brand-600 tracking-wider leading-none italic">{{ $order->customer_phone }}</p>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->customer_phone) }}" target="_blank" class="h-6 w-6 rounded-md bg-emerald-500 flex items-center justify-center text-white shadow-md shadow-emerald-100 hover:scale-110 active:scale-95 transition-all">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" /></svg>
                                </a>
                            </div>
                        </div>
 
                        <div class="group">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block group-hover:text-brand-500 transition-colors">Alamat</span>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-slate-600 font-bold text-xs leading-relaxed italic relative overflow-hidden group-hover:shadow-sm transition-all">
                                {{ $order->customer_address }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="group">
                             <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Material</span>
                             <div class="inline-flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-xl border border-slate-100">
                                <svg class="h-4 w-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-5.25v9" /></svg>
                                <span class="text-sm font-black text-slate-800 tracking-tight">{{ $order->material }}</span>
                             </div>
                        </div>
 
                        <div class="group">
                             <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Nota Khas</span>
                             <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 text-slate-600 font-medium text-xs italic min-h-[4rem] relative">
                                {{ $order->custom_request ?: 'Tiada permintaan khas.' }}
                             </div>
                        </div>
 
                        @if($order->payment_receipt_path)
                            <div class="pt-2">
                                <a href="{{ asset('storage/'.$order->payment_receipt_path) }}" target="_blank" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-xs font-black text-white hover:bg-brand-600 transition-all shadow-md shadow-slate-200 group/receipt">
                                    <svg class="h-4 w-4 transition-transform group-hover/receipt:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    RESIT PEMBAYARAN
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items Section -->
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-white shadow-sm flex items-center justify-center text-brand-600 ring-1 ring-slate-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-tight">Kandungan Tempahan</h2>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Item & harga</p>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto text-xs">
                <table class="min-w-full divide-y divide-slate-50">
                    <thead>
                        <tr class="bg-white">
                            <th scope="col" class="py-4 pl-6 pr-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Penerangan Design</th>
                            <th scope="col" class="px-3 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Saiz</th>
                            <th scope="col" class="px-3 py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Kuantiti</th>
                            <th scope="col" class="px-3 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Harga</th>
                            <th scope="col" class="py-4 pl-3 pr-6 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        @foreach($order->items as $item)
                        <tr class="group/row hover:bg-slate-50/50 transition-all">
                            <td class="whitespace-nowrap py-4 pl-6 pr-3 text-xs font-black text-slate-800">{{ $item->design->name }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-xs font-bold text-slate-500 uppercase">{{ $item->size->name }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-xs text-center">
                                <span class="inline-flex items-center justify-center h-6 w-9 rounded-md bg-brand-50 text-brand-700 font-black italic border border-brand-100">{{ $item->quantity }}</span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-xs text-slate-400 font-bold text-right italic">RM {{ number_format($item->unit_price, 2) }}</td>
                            <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right">
                                <span class="text-sm font-black text-slate-900 italic">RM {{ number_format($item->line_total, 2) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50">
                            <td colspan="4" class="py-6 pl-6 pr-3 text-right">
                                <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest mr-2">Jumlah NETT</span>
                            </td>
                            <td class="py-6 pl-3 pr-6 text-right">
                                <div class="inline-flex flex-col items-end">
                                    <span class="text-3xl font-black text-brand-600 leading-none italic tracking-tighter">RM {{ number_format($order->total, 2) }}</span>
                                    <span class="text-[8px] font-black text-brand-400 uppercase tracking-widest mt-1">SST & Penghantaran Termasuk</span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar Actions -->
    <div class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
        <!-- Update Form Card -->
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="px-6 py-3 border-b border-slate-50 bg-slate-50/50">
                <h2 class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Tindakan Pantas</h2>
            </div>
            <div class="p-6">
                <form method="post" action="{{ route('admin.orders.update', $order) }}" class="space-y-6">
                    @csrf @method('put')
                    <div class="group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1 group-hover:text-brand-600 transition-colors">Status Pesanan</label>
                        <div class="relative">
                            <select name="status" class="block w-full rounded-xl border-0 py-3 pl-4 pr-10 text-slate-900 font-black uppercase tracking-widest ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-brand-600 shadow-sm appearance-none cursor-pointer text-[11px]" required>
                                @foreach(['pending','paid','processing','shipped','completed','cancelled'] as $s)
                                    <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ strtoupper($s) }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1 group-hover:text-brand-600 transition-colors">No Tracking</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-300">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.129-1.125V11.25c0-1.58-1.282-2.812-2.82-2.863l-2.008-.066a2.25 2.25 0 0 0-1.898 1.144l-1.642 2.736a2.25 2.25 0 0 1-1.928 1.091H10.5" /></svg>
                            </div>
                            <input type="text" name="tracking_no" value="{{ $order->tracking_no }}" class="block w-full rounded-xl border-0 py-3 pl-10 px-3 text-slate-900 font-black tracking-widest ring-1 ring-inset ring-slate-200 placeholder:text-slate-300 focus:ring-2 focus:ring-brand-600 text-[11px] shadow-inner" placeholder="NO TRACKING">
                        </div>
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-3.5 text-[11px] font-black text-white shadow-lg shadow-slate-100 hover:bg-brand-600 transition-all active:scale-95 group/save">
                        SIMPAN PERUBAHAN
                    </button>
                </form>
            </div>
        </div>

        <!-- Invoice Card -->
        <div class="relative group bg-slate-900 rounded-2xl shadow-xl overflow-hidden text-white">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-600 via-brand-900 to-slate-900 opacity-90"></div>
            
            <div class="relative px-6 py-8">
                <div class="flex items-center gap-2 mb-6">
                    <div class="h-8 w-8 flex items-center justify-center rounded-lg bg-white/10 border border-white/20 backdrop-blur-md">
                        <svg class="h-4 w-4 text-brand-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    </div>
                    <h2 class="text-[11px] font-black uppercase tracking-widest text-white/90">Sistem Invois</h2>
                </div>

                @if($order->invoice)
                    <div class="mb-6 text-center">
                        <span class="text-[8px] font-black text-brand-300 uppercase tracking-widest block mb-1 opacity-60">TERJANA</span>
                        <span class="text-xl font-black tracking-tight leading-none italic text-white">{{ $order->invoice->invoice_no }}</span>
                    </div>
                    <a href="{{ route('admin.invoices.show', $order->invoice) }}" target="_blank" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-3.5 text-[11px] font-black text-slate-900 hover:bg-brand-50 transition-all border-b-2 border-slate-300 active:border-b-0 active:translate-y-0.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231a1.125 1.125 0 0 1-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
                        CETAK INVOIS
                    </a>
                @else
                    <form method="post" action="{{ route('admin.invoices.store', $order) }}" class="space-y-4">
                        @csrf
                        <div class="group">
                            <label class="block text-[8px] font-black text-white/50 uppercase tracking-widest mb-2 pl-1">Nota Invois</label>
                            <textarea name="notes" rows="2" class="block w-full rounded-xl border-0 py-3 px-4 bg-white/5 text-white font-bold tracking-wider ring-1 ring-inset ring-white/20 placeholder:text-white/20 focus:ring-2 focus:ring-white/40 text-[11px] shadow-inner" placeholder="Nota kaki..."></textarea>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3.5 text-[11px] font-black text-white shadow-xl hover:bg-brand-400 transition-all active:scale-95">
                            JANA INVOIS
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


