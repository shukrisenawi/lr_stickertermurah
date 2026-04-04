@extends('layouts.admin')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Total Order</div><div class="h4">{{ $totalOrders }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Order Aktif</div><div class="h4">{{ $pendingOrders }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Total Design</div><div class="h4">{{ $totalDesigns }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Kategori</div><div class="h4">{{ $totalCategories }}</div></div></div></div>
</div>

<div class="card">
    <div class="card-body">
        <h2 class="h6">Order Terkini</h2>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>No Order</th><th>Pelanggan</th><th>Status</th><th>Total</th><th></th></tr></thead>
                <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td>{{ $order->order_no }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->status }}</td>
                        <td>RM {{ number_format($order->total, 2) }}</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.orders.show', $order) }}">Lihat</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">Tiada data</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
