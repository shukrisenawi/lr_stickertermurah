@extends('layouts.admin')

@section('title', 'Perincian Tempahan: ' . $order->order_no)

@section('content')
<!-- Page Header -->
<div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6 border-b-2 border-slate-100 pb-8">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.orders.index') }}" class="h-12 w-12 flex items-center justify-center rounded-sm bg-white border-2 border-slate-900 text-slate-900 transition-all hover:bg-slate-900 hover:text-white">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0l7.5-7.5M3 12h18" /></svg>
        </a>
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase leading-none mb-1.5">Perincian Tempahan</h2>
            <div class="flex items-center gap-3">
                <span class="text-[11px] font-black text-white bg-brand px-2.5 py-1 rounded-sm uppercase tracking-widest border-b-2 border-brand-700">#{{ $order->order_no }}</span>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">{{ $order->created_at->format('d M Y, h:i A') }}</span>
            </div>
        </div>
    </div>
    
    <div class="flex items-center gap-2">
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
        <div class="inline-flex items-center gap-2 rounded-sm {{ $statusColors['bg'] }} px-5 py-2.5 border-2 {{ $statusColors['border'] }}">
            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
            <span class="text-[12px] font-black uppercase tracking-widest {{ $statusColors['text'] }}">{{ $order->status }}</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-8 animate-in fade-in duration-500">
        <!-- Customer Info Card -->
        <div class="flat-card !p-0 overflow-hidden">
            <div class="px-6 py-5 border-b-2 border-slate-100 bg-slate-50 flex items-center gap-4">
                <div class="h-10 w-10 rounded-sm bg-white border border-slate-200 flex items-center justify-center text-slate-900">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                </div>
                <div>
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest">Maklumat Pelanggan</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Identiti dan alamat</p>
                </div>
            </div>
            
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-6">
                        <div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Nama Penuh</span>
                            <p class="text-xl font-black text-slate-900 uppercase tracking-tighter leading-none">{{ $order->customer_name }}</p>
                        </div>
                        
                        <div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Nombor Telefon</span>
                            <div class="flex items-center gap-4">
                                <p class="text-lg font-black text-brand tracking-widest leading-none italic decoration-2 underline-offset-4">{{ $order->customer_phone }}</p>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->customer_phone) }}" target="_blank" class="h-8 w-8 rounded-sm bg-emerald-500 flex items-center justify-center text-white border-b-4 border-emerald-700 hover:translate-y-0.5 active:translate-y-1 transition-all">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" /></svg>
                                </a>
                            </div>
                        </div>
 
                        <div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Alamat Penghantaran</span>
                            <div class="p-5 bg-slate-50 border-2 border-slate-100 text-slate-800 font-bold text-xs leading-relaxed uppercase tracking-tight">
                                {{ $order->customer_address }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                             <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Material Pilihan</span>
                             <div class="inline-flex items-center gap-3 px-4 py-2 bg-slate-900 border-b-4 border-slate-700 rounded-sm">
                                <svg class="h-4 w-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-5.25v9" /></svg>
                                <span class="text-xs font-black text-white uppercase tracking-widest">{{ $order->material }}</span>
                             </div>
                        </div>
 
                        <div>
                             <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 block">Nota Khas</span>
                             <div class="p-5 bg-slate-50 border-2 border-slate-100 text-slate-600 font-bold text-xs italic min-h-[5rem]">
                                {{ $order->custom_request ?: 'Tiada permintaan khas.' }}
                             </div>
                        </div>
 
                        @if($order->payment_receipt_path)
                            <div class="pt-2">
                                <a href="{{ asset('storage/'.$order->payment_receipt_path) }}" target="_blank" class="flat-btn-primary !py-4 w-full flex justify-center">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    RESIT PEMBAYARAN
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items Section -->
        <div class="flat-card !p-0 overflow-hidden">
            <div class="px-6 py-5 border-b-2 border-slate-100 bg-slate-50">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-sm bg-white border border-slate-200 flex items-center justify-center text-slate-900">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest">Kandungan Tempahan</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Item & perincian harga</p>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr class="bg-white">
                            <th scope="col" class="py-5 pl-6 pr-4 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest">Penerangan Design</th>
                            <th scope="col" class="px-4 py-5 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest">Saiz</th>
                            <th scope="col" class="px-4 py-5 text-center text-[10px] font-black text-slate-900 uppercase tracking-widest">Kuantiti</th>
                            <th scope="col" class="px-4 py-5 text-right text-[10px] font-black text-slate-900 uppercase tracking-widest">Harga Unit</th>
                            <th scope="col" class="py-5 pl-4 pr-6 text-right text-[10px] font-black text-slate-900 uppercase tracking-widest">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        @foreach($order->items as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="whitespace-nowrap py-6 pl-6 pr-4 text-xs font-black text-slate-900 uppercase tracking-tight">{{ $item->design->name }}</td>
                            <td class="whitespace-nowrap px-4 py-6 text-xs font-bold text-slate-400 uppercase tracking-widest">{{ $item->size->name }}</td>
                            <td class="whitespace-nowrap px-4 py-6 text-center">
                                <span class="inline-flex items-center justify-center h-7 px-3 rounded-sm bg-brand border-b-2 border-brand-700 text-white text-xs font-black italic">{{ $item->quantity }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-6 text-xs text-slate-400 font-bold text-right tracking-widest italic">RM {{ number_format($item->unit_price, 2) }}</td>
                            <td class="whitespace-nowrap py-6 pl-4 pr-6 text-right">
                                <span class="text-sm font-black text-slate-900 italic">RM {{ number_format($item->line_total, 2) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-900 text-white">
                            <td colspan="4" class="py-8 pl-6 pr-4 text-right">
                                <span class="text-[12px] font-black uppercase tracking-widest text-slate-400 mr-4">Jumlah Keseluruhan (NETT)</span>
                            </td>
                            <td class="py-8 pl-4 pr-6 text-right">
                                <div class="flex flex-col items-end">
                                    <span class="text-4xl font-black text-brand leading-none tracking-tighter italic">RM {{ number_format($order->total, 2) }}</span>
                                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mt-2">SST & PENGHANTARAN TERMASUK</span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar Actions -->
    <div class="space-y-8 animate-in fade-in duration-500">
        <!-- Update Form Card -->
        <div class="flat-card !p-0 overflow-hidden">
            <div class="px-6 py-5 border-b-2 border-slate-100 bg-slate-50">
                <h2 class="text-xs font-black text-slate-900 uppercase tracking-widest">Tindakan Pantas</h2>
            </div>
            <div class="p-8">
                <form method="post" action="{{ route('admin.orders.update', $order) }}" class="space-y-8">
                    @csrf @method('put')
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5">Status Pesanan</label>
                        <select name="status" class="flat-input h-14 bg-slate-50 uppercase tracking-widest font-black text-[11px]" required>
                            @foreach(['pending','paid','processing','shipped','completed','cancelled'] as $s)
                                <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ strtoupper($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5">No Tracking Logistik</label>
                        <input type="text" name="tracking_no" value="{{ $order->tracking_no }}" class="flat-input h-14 uppercase tracking-widest font-black text-[11px]" placeholder="MASUKKAN NO TRACKING">
                    </div>

                    <button type="submit" class="flat-btn-primary w-full !py-5 font-black tracking-widest">
                        SAHKAN PERUBAHAN
                    </button>
                </form>
            </div>
        </div>

        <!-- Invoice Card -->
        <div class="flat-card border-slate-900 border-2 !p-0 overflow-hidden bg-slate-900 text-white">
            <div class="p-8">
                <div class="flex items-center gap-4 mb-8">
                    <div class="h-10 w-10 flex items-center justify-center rounded-sm bg-white/10 border border-white/20">
                        <svg class="h-5 w-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-xs font-black uppercase tracking-widest text-white/90">Sistem Invois</h2>
                        <p class="text-[9px] font-bold text-white/40 uppercase tracking-widest mt-0.5">Penjanaan automatik</p>
                    </div>
                </div>

                @if($order->invoice)
                    <div class="mb-8 p-6 bg-white/5 border border-white/10 rounded-sm text-center">
                        <span class="text-[9px] font-black text-brand uppercase tracking-[0.3em] block mb-2 opacity-80">INVOIS DIJANA</span>
                        <span class="text-2xl font-black tracking-tighter italic text-white">{{ $order->invoice->invoice_no }}</span>
                    </div>
                    <a href="{{ route('admin.invoices.show', $order->invoice) }}" target="_blank" class="w-full h-14 flex items-center justify-center gap-3 rounded-sm bg-white text-slate-900 font-black text-[11px] uppercase tracking-widest border-b-4 border-slate-300 transition-all hover:translate-y-0.5 active:translate-y-1">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231a1.125 1.125 0 0 1-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
                        CETAK SALINAN
                    </a>
                @else
                    <form method="post" action="{{ route('admin.invoices.store', $order) }}" class="space-y-6">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-black text-white/50 uppercase tracking-widest mb-2.5">Nota Invois (Pilihan)</label>
                            <textarea name="notes" rows="2" class="block w-full rounded-sm border-0 py-4 px-5 bg-white/5 text-white font-bold tracking-tight ring-1 ring-inset ring-white/20 placeholder:text-white/20 focus:ring-2 focus:ring-brand text-[11px]" placeholder="Tulis catatan..."></textarea>
                        </div>
                        <button type="submit" class="w-full h-15 inline-flex items-center justify-center gap-3 rounded-sm bg-brand border-b-4 border-brand-700 text-[11px] font-black uppercase tracking-widest text-white transition-all hover:translate-y-0.5 active:translate-y-1">
                            JANA INVOIS SKARANG
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


