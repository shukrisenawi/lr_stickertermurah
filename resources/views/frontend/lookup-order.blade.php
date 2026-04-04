@extends('layouts.frontend')

@section('title', 'Semak / Repeat Order')

@section('content')
<h1 class="h4 mb-3">Semak / Repeat Order</h1>
<form method="post" action="{{ route('orders.lookup') }}" class="card card-body mb-4">
    @csrf
    <label class="form-label">Masukkan No HP pelanggan</label>
    <div class="input-group">
        <input class="form-control" name="customer_phone" value="{{ old('customer_phone', $customerPhone ?? '') }}" required>
        <button class="btn btn-primary">Cari Order</button>
    </div>
</form>

@if(isset($orders))
    @if($orders->isEmpty())
        <div class="alert alert-warning">Tiada order ditemui untuk no hp ini.</div>
    @else
        @foreach($orders as $order)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <div>
                            <h2 class="h6 mb-1">{{ $order->order_no }}</h2>
                            <div class="small text-muted">Tarikh: {{ $order->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div>
                            <span class="badge text-bg-info text-uppercase">{{ $order->status }}</span>
                        </div>
                    </div>
                    <p class="mb-1">Tracking No: <strong>{{ $order->tracking_no ?: '-' }}</strong></p>
                    <p class="mb-2">Jumlah: <strong>RM {{ number_format($order->total, 2) }}</strong></p>
                    @if($order->invoice)
                        <p class="mb-2 small">Invoice: {{ $order->invoice->invoice_no }}</p>
                    @endif
                    <a href="{{ route('orders.repeat', $order) }}" class="btn btn-outline-primary btn-sm">Repeat Order Ini</a>
                </div>
            </div>
        @endforeach
    @endif
@endif
@endsection
