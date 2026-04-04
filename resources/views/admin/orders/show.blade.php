@extends('layouts.admin')

@section('content')
<h1 class="h5 mb-3">Detail Order: {{ $order->order_no }}</h1>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <h2 class="h6">Maklumat Pelanggan</h2>
                <p class="mb-1"><strong>{{ $order->customer_name }}</strong> ({{ $order->customer_phone }})</p>
                <p class="mb-2">{{ $order->customer_address }}</p>
                <p class="mb-2">Material: {{ $order->material }}</p>
                <p class="mb-2">Custom Request: {{ $order->custom_request ?: '-' }}</p>
                @if($order->payment_receipt_path)
                    <a href="{{ asset('storage/'.$order->payment_receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat Resit</a>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h2 class="h6">Item</h2>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Design</th><th>Saiz</th><th>Qty</th><th>Harga</th><th>Jumlah</th></tr></thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->design->name }}</td>
                                    <td>{{ $item->size->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>RM {{ number_format($item->unit_price, 2) }}</td>
                                    <td>RM {{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr><th colspan="4">Total</th><th>RM {{ number_format($order->total, 2) }}</th></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-body">
                <h2 class="h6">Update Status / Tracking</h2>
                <form method="post" action="{{ route('admin.orders.update', $order) }}">
                    @csrf @method('put')
                    <div class="mb-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            @foreach(['pending','paid','processing','shipped','completed','cancelled'] as $s)
                                <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ strtoupper($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tracking No</label>
                        <input class="form-control" name="tracking_no" value="{{ $order->tracking_no }}">
                    </div>
                    <button class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h2 class="h6">Invoice</h2>
                @if($order->invoice)
                    <p class="mb-2">Invoice: <strong>{{ $order->invoice->invoice_no }}</strong></p>
                    <a href="{{ route('admin.invoices.show', $order->invoice) }}" target="_blank" class="btn btn-outline-dark btn-sm">Lihat / Cetak Invoice</a>
                @else
                    <form method="post" action="{{ route('admin.invoices.store', $order) }}">
                        @csrf
                        <label class="form-label">Nota (optional)</label>
                        <textarea class="form-control mb-2" name="notes" rows="2"></textarea>
                        <button class="btn btn-dark btn-sm">Create Invoice</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
