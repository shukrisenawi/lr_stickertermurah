@extends('layouts.frontend')

@section('title', 'Buat Tempahan Sticker')

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Header with Progress -->
    <div class="mb-12">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-8">
            <div>
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-xs font-bold uppercase tracking-widest text-slate-400">
                        <li><a href="{{ route('home') }}" class="hover:text-brand-600 transition-colors">Home</a></li>
                        <li><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9 5l7 7-7 7" stroke-width="3" /></svg></li>
                        <li class="text-brand-600">Tempahan Baru</li>
                    </ol>
                </nav>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">Borang Tempahan</h1>
                <p class="mt-2 text-slate-500 font-medium">Lengkapkan maklumat di bawah untuk cetakan Mirrorcote Glossy.</p>
            </div>
            <div class="flex items-center gap-4 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex -space-x-2">
                    <div class="w-8 h-8 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center text-[10px] text-white font-bold">1</div>
                    <div class="w-8 h-8 rounded-full bg-slate-200 border-2 border-white flex items-center justify-center text-[10px] text-slate-500 font-bold">2</div>
                    <div class="w-8 h-8 rounded-full bg-slate-200 border-2 border-white flex items-center justify-center text-[10px] text-slate-500 font-bold">3</div>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Langkah 1/3</span>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-10 rounded-[2rem] bg-rose-50 p-6 border border-rose-100 shadow-xl shadow-rose-500/5 animate-in shake-in">
            <div class="flex gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-rose-800 uppercase tracking-widest mb-2">Terdapat Ralat Input</h3>
                    <ul class="text-sm text-rose-700 space-y-1 font-medium">
                        @foreach($errors->all() as $error)
                            <li class="flex items-center gap-2">
                                <span class="w-1 h-1 rounded-full bg-rose-400"></span>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="post" action="{{ route('orders.store') }}" enctype="multipart/form-data" id="orderForm" class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        @csrf
        @if($repeatOrder)
            <input type="hidden" name="repeat_from_order_id" value="{{ $repeatOrder->id }}">
        @endif

        <div class="lg:col-span-2 space-y-10">
            <!-- Customer Info Section -->
            <section class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900">Maklumat Pelanggan</h2>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Langkah 1</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Nama Penuh</label>
                            <input type="text" name="customer_name" value="{{ old('customer_name', $repeatOrder?->customer_name) }}" required
                                class="block w-full rounded-2xl border-slate-200 bg-slate-50 py-4 px-5 text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-300 focus:ring-2 focus:ring-brand-600 sm:text-sm font-bold transition-all"
                                placeholder="Masukkan nama penuh anda">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">No. Telefon (WhatsApp)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 font-bold text-sm">+60</span>
                                <input type="text" name="customer_phone" value="{{ old('customer_phone', $repeatOrder?->customer_phone) }}" required
                                    class="block w-full rounded-2xl border-slate-200 bg-slate-50 py-4 pl-14 pr-5 text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-300 focus:ring-2 focus:ring-brand-600 sm:text-sm font-bold transition-all"
                                    placeholder="123456789">
                            </div>
                        </div>
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Alamat Pengeposan</label>
                            <textarea name="customer_address" rows="3" required
                                class="block w-full rounded-2xl border-slate-200 bg-slate-50 py-4 px-5 text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-300 focus:ring-2 focus:ring-brand-600 sm:text-sm font-bold transition-all"
                                placeholder="Sila masukkan alamat lengkap untuk penghantaran">{{ old('customer_address', $repeatOrder?->customer_address) }}</textarea>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Resit Pembayaran <span class="text-slate-300 italic font-normal ml-1">(Optional)</span></label>
                        <div class="relative group">
                            <input id="payment_receipt" type="file" name="payment_receipt" accept=".jpg,.jpeg,.png,.pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="updateFileName(this, 'receiptName')" />
                            <div class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-200 border-dashed rounded-[2rem] bg-slate-50 group-hover:bg-slate-100 group-hover:border-brand-300 transition-all">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-2 text-slate-300 group-hover:text-brand-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    <p id="receiptName" class="text-xs font-black text-slate-500 uppercase tracking-widest group-hover:text-brand-600 transition-colors text-center px-4">Muat naik resit jika sudah bayar</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Order Items Section -->
            <section class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900">Perincian Item</h2>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Langkah 2</p>
                        </div>
                    </div>
                    <button type="button" id="addItemBtn" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-brand-600 hover:text-white hover:bg-brand-600 px-5 py-2.5 rounded-xl border border-brand-100 transition-all">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Sticker
                    </button>
                </div>

                <div class="p-8 space-y-6">
                    <div id="itemsWrap" class="space-y-6"></div>

                    <div class="pt-6 border-t border-slate-100">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Nota Tambahan</label>
                        <textarea name="custom_request" rows="2" placeholder="Tulis rujukan tambahan di sini (cth: warna, font, dll)"
                            class="block w-full rounded-2xl border-slate-200 bg-slate-50 py-4 px-5 text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-300 focus:ring-2 focus:ring-brand-600 sm:text-sm font-bold transition-all mt-2">{{ old('custom_request') }}</textarea>
                    </div>
                </div>
            </section>
        </div>

        <!-- Sidebar Summary -->
        <div class="lg:col-span-1">
            <div class="sticky top-24 space-y-6">
                <div class="bg-brand-600 rounded-[2.5rem] shadow-2xl shadow-brand-500/30 p-10 text-white relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-brand-900/40 rounded-full blur-3xl"></div>
                    
                    <div class="relative z-10">
                        <h3 class="text-xs font-black uppercase tracking-[0.3em] text-brand-200 mb-6">Ringkasan Tempahan</h3>
                        
                        <div class="space-y-4 mb-10 pb-8 border-b border-brand-400/30">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-bold text-brand-100">Jumlah Item</span>
                                <span id="itemCountText" class="font-black">0</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-bold text-brand-100">Bahan</span>
                                <span class="font-black italic">Mirrorcote</span>
                            </div>
                        </div>

                        <div class="mb-10 text-center sm:text-left">
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-200">Jumlah Keseluruhan</span>
                            <div class="mt-2 flex items-baseline justify-center sm:justify-start gap-2">
                                <span class="text-xl font-bold text-brand-200">RM</span>
                                <span id="totalText" class="text-6xl font-black tracking-tight">0.00</span>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-white text-brand-900 py-5 rounded-[1.5rem] font-black text-sm uppercase tracking-widest shadow-xl hover:bg-slate-900 hover:text-white transition-all transform hover:scale-105 active:scale-95 flex items-center justify-center gap-3">
                            Hantar Order
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7-7 7M3 12h18" />
                            </svg>
                        </button>
                        
                        <p class="mt-6 text-[10px] text-center text-brand-200 font-bold leading-relaxed">
                            Dengan menekan butang di atas, anda bersetuju untuk pihak kami memproses tempahan anda mengikut terma & syarat.
                        </p>
                    </div>
                </div>

                <!-- Support Box -->
                <div class="bg-emerald-50 border border-emerald-100 rounded-3xl p-6 flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/20">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-1">Ada Masalah?</p>
                        <p class="text-xs font-bold text-emerald-800">Hubungi Team Graphic Kami di WhatsApp.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@php
    $designPayload = $designs->map(function ($d) {
        return [
            'id' => $d->id,
            'name' => $d->name,
            'category' => $d->category?->name,
        ];
    })->values()->all();

    $sizePayload = $sizes->map(function ($s) {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'price' => (float) $s->price,
            'default' => (bool) $s->is_default,
        ];
    })->values()->all();

    $repeatItems = $repeatOrder?->items?->map(function ($i) {
        return [
            'sticker_design_id' => $i->sticker_design_id,
            'sticker_size_id' => $i->sticker_size_id,
            'quantity' => $i->quantity,
        ];
    })->values()->all() ?? [];

    $oldItemsPayload = old('items', $repeatItems);
@endphp
<script>
const designs = @json($designPayload);
const sizes = @json($sizePayload);
const oldItems = @json($oldItemsPayload);
let itemIndex = 0;

function updateFileName(input, id) {
    if (input.files && input.files[0]) {
        document.getElementById(id).innerText = input.files[0].name;
    }
}

function itemRow(item = {}) {
    const idx = itemIndex++;
    const designOptions = designs.map(d => `<option value="${d.id}" ${Number(item.sticker_design_id) === d.id ? 'selected' : ''}>${d.name} (${d.category ?? 'Tiada Kategori'})</option>`).join('');
    const sizeOptions = sizes.map(s => `<option value="${s.id}" data-price="${s.price}" ${(Number(item.sticker_size_id) === s.id || (!item.sticker_size_id && s.default)) ? 'selected' : ''}>${s.name} - RM ${s.price.toFixed(2)}</option>`).join('');

    return `
    <div class="relative group p-8 rounded-3xl bg-slate-50 border border-slate-200 transition-all hover:border-brand-200 hover:bg-white hover:shadow-xl hover:shadow-brand-500/5 item-row animate-in slide-in-from-bottom-2">
        <button type="button" class="absolute -top-3 -right-3 h-10 w-10 flex items-center justify-center rounded-2xl bg-white text-slate-300 transition-all hover:bg-rose-500 hover:text-white remove-item shadow-lg active:scale-95 border border-slate-100">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
        
        <div class="flex items-center gap-3 mb-6">
            <div class="text-[10px] font-black text-brand-500 uppercase tracking-[0.2em] bg-brand-50 px-3 py-1 rounded-lg">Item #${itemIndex}</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <div class="md:col-span-2 space-y-2">
                <label class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 block px-1">Pilihan Design</label>
                <select name="items[${idx}][sticker_design_id]" required 
                    class="block w-full rounded-xl border-0 py-3.5 px-4 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-brand-600 text-sm font-bold bg-white transition-all appearance-none cursor-pointer">
                    ${designOptions}
                </select>
            </div>
            <div class="md:col-span-2 space-y-2">
                <label class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 block px-1">Pilih Saiz</label>
                <select name="items[${idx}][sticker_size_id]" required 
                    class="block w-full rounded-xl border-0 py-3.5 px-4 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-brand-600 text-sm font-bold bg-white transition-all appearance-none cursor-pointer size-select">
                    ${sizeOptions}
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 block px-1 text-center">Kuantiti</label>
                <div class="flex items-center bg-white rounded-xl ring-1 ring-slate-200 overflow-hidden focus-within:ring-2 focus-within:ring-brand-600 transition-all">
                    <input type="number" name="items[${idx}][quantity]" value="${item.quantity ?? 1}" min="1" required 
                        class="block w-full border-0 py-3.5 px-3 text-sm font-black text-center focus:outline-none qty-input">
                </div>
            </div>
        </div>
    </div>`;
}

function recalc() {
    let total = 0;
    const items = document.querySelectorAll('.item-row');
    items.forEach(row => {
        const size = row.querySelector('.size-select');
        const qty = Number(row.querySelector('.qty-input').value || 0);
        const price = Number(size.selectedOptions[0]?.dataset.price || 0);
        total += qty * price;
    });

    document.getElementById('totalText').innerText = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('itemCountText').innerText = items.length;
}

function bindRowEvents(row) {
    row.querySelector('.remove-item').addEventListener('click', () => {
        if(document.querySelectorAll('.item-row').length > 1) {
            row.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                row.remove();
                recalc();
            }, 200);
        } else {
            alert('Sekurang-kurangnya satu item diperlukan.');
        }
    });

    row.querySelector('.size-select').addEventListener('change', recalc);
    row.querySelector('.qty-input').addEventListener('input', recalc);
}

function addRow(item = {}) {
    const wrap = document.getElementById('itemsWrap');
    wrap.insertAdjacentHTML('beforeend', itemRow(item));
    const newRow = wrap.lastElementChild;
    bindRowEvents(newRow);
    recalc();
}

document.getElementById('addItemBtn').addEventListener('click', () => addRow());

if (oldItems.length) {
    oldItems.forEach(addRow);
} else {
    addRow();
}
</script>
@endpush


