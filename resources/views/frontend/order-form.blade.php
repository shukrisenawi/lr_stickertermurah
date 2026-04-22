@extends('layouts.frontend')

@section('title', 'Buat Tempahan Sticker')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <section class="space-y-4">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="space-y-3">
                <div class="frontend-section-head">
                    <span class="frontend-section-accent"></span>
                    <div>
                        <h1 class="frontend-title">Borang Tempahan</h1>
                        <p class="frontend-copy">Lengkapkan maklumat pelanggan, item, dan ringkasan tempahan tanpa ubah logik sedia ada.</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <span class="frontend-badge">Langkah 1 / 3</span>
                    <span class="frontend-badge">Akaun: {{ auth()->user()?->email }}</span>
                </div>
            </div>
        </div>
    </section>

    @if($errors->any())
        <div class="frontend-flat-card border-rose-200 bg-rose-50 p-5">
            <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-rose-700">Terdapat ralat input</h3>
            <ul class="mt-3 space-y-1 text-sm text-rose-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="frontend-flat-card border-blue-200 bg-blue-50 p-5">
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-blue-700">Akaun Ahli Aktif</p>
        <p class="mt-2 text-sm text-blue-900">Anda log masuk sebagai <span class="font-semibold">{{ auth()->user()?->email }}</span>. Tempahan ini akan terus disimpan dalam rekod ahli anda.</p>
    </div>

    <form method="post" action="{{ route('orders.store') }}" enctype="multipart/form-data" id="orderForm" class="grid grid-cols-1 gap-8 xl:grid-cols-3">
        @csrf
        @if($repeatOrder)
            <input type="hidden" name="repeat_from_order_id" value="{{ $repeatOrder->id }}">
        @endif

        <div class="space-y-8 xl:col-span-2">
            <section class="frontend-flat-card overflow-hidden">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="frontend-section-head">
                        <span class="frontend-section-accent"></span>
                        <div>
                            <h2 class="text-xl font-bold tracking-tight text-slate-900">Maklumat Pelanggan</h2>
                            <p class="text-sm text-slate-500">Data asas untuk rekod dan penghantaran.</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">
                    <div>
                        <label>Nama Penuh</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name', $repeatOrder?->customer_name) }}" required placeholder="Masukkan nama penuh anda">
                    </div>
                    <div>
                        <label>No. Telefon (WhatsApp)</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone', $repeatOrder?->customer_phone) }}" required placeholder="Contoh: 0111223344">
                    </div>
                    <div class="md:col-span-2">
                        @php
                            $defaultAddressValue = old('customer_address', $repeatOrder?->customer_address ?? $latestCustomerAddress);
                        @endphp
                        <label>Alamat Tersimpan</label>
                        <select id="savedAddressSelect">
                            <option value="">Gunakan alamat baru</option>
                            @foreach($customerAddresses as $savedAddress)
                                <option value="{{ $savedAddress->address }}" @selected($savedAddress->address === $defaultAddressValue)>
                                    {{ \Illuminate\Support\Str::limit($savedAddress->address, 90) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label>Alamat Pengeposan</label>
                        <textarea id="customer_address" name="customer_address" rows="3" required placeholder="Sila masukkan alamat lengkap untuk penghantaran">{{ $defaultAddressValue }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label>Resit Pembayaran</label>
                        <input id="payment_receipt" type="file" name="payment_receipt" accept=".jpg,.jpeg,.png,.pdf" onchange="updateFileName(this, 'receiptName')">
                        <p id="receiptName" class="mt-2 text-sm text-slate-500">Muat naik resit jika sudah bayar</p>
                    </div>
                </div>
            </section>

            <section class="frontend-flat-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                    <div class="frontend-section-head">
                        <span class="frontend-section-accent"></span>
                        <div>
                            <h2 class="text-xl font-bold tracking-tight text-slate-900">Perincian Item</h2>
                            <p class="text-sm text-slate-500">Tambah item design dan pilih saiz untuk setiap tempahan.</p>
                        </div>
                    </div>
                    <button type="button" id="addItemBtn" class="frontend-btn-secondary px-4 py-2 text-xs">Tambah Sticker</button>
                </div>
                <div class="space-y-6 p-6">
                    <div id="itemsWrap" class="space-y-4"></div>
                    <div>
                        <label>Nota Tambahan</label>
                        <textarea name="custom_request" rows="2" placeholder="Tulis rujukan tambahan di sini">{{ old('custom_request') }}</textarea>
                    </div>
                </div>
            </section>
        </div>

        <div class="xl:col-span-1">
            <div class="sticky top-24 space-y-6">
                <div class="overflow-hidden rounded-[1.75rem] bg-slate-900 p-8 text-white shadow-xl">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Ringkasan Tempahan</p>
                    <div class="mt-6 space-y-3 border-b border-slate-700 pb-6 text-sm">
                        <div class="flex items-center justify-between"><span class="text-slate-400">Jumlah Item</span><span id="itemCountText" class="font-semibold">0</span></div>
                        <div class="flex items-center justify-between"><span class="text-slate-400">Bahan</span><span class="font-semibold">Mirrorcote</span></div>
                    </div>
                    <div class="mt-6">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Jumlah Keseluruhan</p>
                        <p class="mt-2 text-5xl font-bold tracking-tight"><span class="text-xl">RM</span> <span id="totalText">0.00</span></p>
                    </div>
                    <button type="submit" class="mt-8 flex w-full items-center justify-center rounded-xl bg-brand-600 px-6 py-4 text-sm font-semibold text-white transition hover:bg-brand-500">Hantar Order</button>
                    <p class="mt-4 text-xs leading-6 text-slate-400">Dengan menghantar borang ini, anda bersetuju untuk kami memproses tempahan mengikut maklumat yang diberikan.</p>
                </div>

                <div class="frontend-flat-card border-emerald-200 bg-emerald-50 p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700">Perlu bantuan?</p>
                    <p class="mt-2 text-sm text-emerald-900">Hubungi team graphic kami melalui WhatsApp untuk pengesahan atau semakan design.</p>
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

    if (empty($oldItemsPayload) && $selectedDesignId > 0) {
        $oldItemsPayload = [[
            'sticker_design_id' => $selectedDesignId,
            'quantity' => 1,
        ]];
    }
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
    <div class="item-row rounded-2xl border border-slate-200 bg-slate-50/80 p-5">
        <div class="mb-4 flex items-center justify-between">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Item #${itemIndex}</p>
            <button type="button" class="remove-item rounded-xl bg-white px-3 py-2 text-xs font-semibold text-rose-600 border border-slate-200">Buang</button>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div class="md:col-span-2">
                <label>Pilihan Design</label>
                <select name="items[${idx}][sticker_design_id]" required>${designOptions}</select>
            </div>
            <div class="md:col-span-2">
                <label>Pilih Saiz</label>
                <select name="items[${idx}][sticker_size_id]" required class="size-select">${sizeOptions}</select>
            </div>
            <div>
                <label>Kuantiti</label>
                <input type="number" name="items[${idx}][quantity]" value="${item.quantity ?? 1}" min="1" required class="qty-input">
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
        if (document.querySelectorAll('.item-row').length > 1) {
            row.remove();
            recalc();
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

const savedAddressSelect = document.getElementById('savedAddressSelect');
const customerAddressField = document.getElementById('customer_address');

if (savedAddressSelect && customerAddressField) {
    savedAddressSelect.addEventListener('change', (event) => {
        const selectedAddress = event.target.value.trim();
        if (selectedAddress !== '') {
            customerAddressField.value = selectedAddress;
        }
    });
}
</script>
@endpush
