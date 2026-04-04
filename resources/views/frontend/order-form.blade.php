@extends('layouts.frontend')

@section('title', 'Buat Order')

@section('content')
<h1 class="h4 mb-3">Borang Order Sticker</h1>
<p class="text-muted">Material: <strong>Mirrorcote sahaja</strong></p>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="post" action="{{ route('orders.store') }}" enctype="multipart/form-data" id="orderForm">
    @csrf
    @if($repeatOrder)
        <input type="hidden" name="repeat_from_order_id" value="{{ $repeatOrder->id }}">
    @endif
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Nama</label>
            <input class="form-control" name="customer_name" value="{{ old('customer_name', $repeatOrder?->customer_name) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">No HP</label>
            <input class="form-control" name="customer_phone" value="{{ old('customer_phone', $repeatOrder?->customer_phone) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Resit Pembayaran (optional)</label>
            <input type="file" class="form-control" name="payment_receipt" accept=".jpg,.jpeg,.png,.pdf">
        </div>
        <div class="col-12">
            <label class="form-label">Alamat</label>
            <textarea class="form-control" name="customer_address" rows="2" required>{{ old('customer_address', $repeatOrder?->customer_address) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label">Request Sticker (optional)</label>
            <textarea class="form-control" name="custom_request" rows="3" placeholder="Contoh: nak warna merah, tambah tulisan...">{{ old('custom_request') }}</textarea>
        </div>
    </div>

    <hr>
    <h2 class="h5">Item Order</h2>
    <div id="itemsWrap"></div>
    <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">+ Tambah Item</button>

    <div class="mt-3 alert alert-warning">
        Anggaran jumlah: RM <strong id="totalText">0.00</strong>
    </div>

    <button class="btn btn-primary">Hantar Order</button>
</form>
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

function itemRow(item = {}) {
    const idx = itemIndex++;
    const designOptions = designs.map(d => `<option value="${d.id}" ${Number(item.sticker_design_id) === d.id ? 'selected' : ''}>${d.name} (${d.category ?? 'Tanpa kategori'})</option>`).join('');
    const sizeOptions = sizes.map(s => `<option value="${s.id}" data-price="${s.price}" ${(Number(item.sticker_size_id) === s.id || (!item.sticker_size_id && s.default)) ? 'selected' : ''}>${s.name} - RM ${s.price.toFixed(2)}</option>`).join('');

    return `
    <div class="card mb-2 item-row">
        <div class="card-body row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Design</label>
                <select class="form-select" name="items[${idx}][sticker_design_id]" required>${designOptions}</select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Saiz</label>
                <select class="form-select size-select" name="items[${idx}][sticker_size_id]" required>${sizeOptions}</select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Kuantiti</label>
                <input type="number" min="1" class="form-control qty-input" name="items[${idx}][quantity]" value="${item.quantity ?? 1}" required>
            </div>
            <div class="col-md-1 d-grid">
                <button type="button" class="btn btn-outline-danger remove-item">X</button>
            </div>
        </div>
    </div>`;
}

function recalc() {
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const size = row.querySelector('.size-select');
        const qty = Number(row.querySelector('.qty-input').value || 0);
        const price = Number(size.selectedOptions[0]?.dataset.price || 0);
        total += qty * price;
    });

    document.getElementById('totalText').innerText = total.toFixed(2);
}

function bindRowEvents(row) {
    row.querySelector('.remove-item').addEventListener('click', () => {
        row.remove();
        recalc();
    });

    row.querySelector('.size-select').addEventListener('change', recalc);
    row.querySelector('.qty-input').addEventListener('input', recalc);
}

function addRow(item = {}) {
    const wrap = document.getElementById('itemsWrap');
    wrap.insertAdjacentHTML('beforeend', itemRow(item));
    bindRowEvents(wrap.lastElementChild);
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
