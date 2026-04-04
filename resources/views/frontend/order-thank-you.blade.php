@extends('layouts.frontend')

@section('title', 'Order Berjaya')

@section('content')
<div class="alert alert-success">
    Order berjaya dihantar. Simpan nombor ini: <strong>{{ $order->order_no }}</strong>
</div>

<div class="card">
    <div class="card-body">
        <h1 class="h5">Ringkasan Order</h1>
        <p>Status: <span class="badge text-bg-warning text-uppercase">{{ $order->status }}</span></p>
        <p>Tracking No: {{ $order->tracking_no ?: '-' }}</p>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Design</th><th>Saiz</th><th>Qty</th><th>Jumlah (RM)</th></tr></thead>
                <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->design->name }}</td>
                        <td>{{ $item->size->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot><tr><th colspan="3">Total</th><th>{{ number_format($order->total, 2) }}</th></tr></tfoot>
            </table>
        </div>
        <a href="{{ route('orders.lookup-form') }}" class="btn btn-outline-primary btn-sm">Semak Order</a>
    </div>
</div>
@endsection
