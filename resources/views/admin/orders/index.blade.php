@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Senarai Order</h1>
    <form method="get" class="d-flex gap-2">
        <select name="status" class="form-select form-select-sm">
            <option value="">Semua status</option>
            @foreach(['pending','paid','processing','shipped','completed','cancelled'] as $s)
                <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ strtoupper($s) }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-outline-secondary">Tapis</button>
    </form>
</div>

<div class="card"><div class="table-responsive"><table class="table table-sm mb-0">
    <thead><tr><th>No</th><th>Pelanggan</th><th>Status</th><th>Tracking</th><th>Total</th><th></th></tr></thead>
    <tbody>
    @forelse($orders as $order)
        <tr>
            <td>{{ $order->order_no }}</td>
            <td>{{ $order->customer_name }}<br><small>{{ $order->customer_phone }}</small></td>
            <td>{{ strtoupper($order->status) }}</td>
            <td>{{ $order->tracking_no ?: '-' }}</td>
            <td>RM {{ number_format($order->total, 2) }}</td>
            <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.orders.show', $order) }}">Detail</a></td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-muted">Tiada order</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-2">{{ $orders->links() }}</div>
@endsection
