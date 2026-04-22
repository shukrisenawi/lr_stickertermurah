@extends('layouts.admin')

@section('title', 'Pengurusan Tempahan')

@section('content')
<div class="space-y-6">
<div class="admin-page-head">
    <div class="space-y-3">
        <div class="admin-title-block">
            <span class="admin-title-accent"></span>
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Pusat Kawalan Tempahan</h1>
                <p class="admin-page-copy">Pantau pesanan, bayaran, dan logistik secara masa nyata dalam susun atur yang selari dengan dashboard.</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-3">
            <span class="admin-pill">Senarai tempahan aktif</span>
            <span class="admin-pill">{{ $orders->total() }} rekod</span>
        </div>
    </div>
    
    <div class="admin-page-actions">
        <a href="{{ route('admin.dashboard') }}" class="admin-btn-secondary">Dashboard</a>
    </div>
</div>

<div class="admin-toolbar-card">
    <div>
        <p class="admin-mini-label">Tapisan</p>
        <p class="mt-2 text-sm text-slate-500">Tukar status untuk kecilkan senarai dan fokus pada tindakan semasa.</p>
    </div>

    <form method="get" class="admin-search-form">
        <select name="status" class="w-full md:w-52">
            <option value="">Semua status</option>
                    @foreach(['pending','paid','processing','shipped','completed','cancelled'] as $s)
                        <option value="{{ $s }}" {{ ($status ?? '') === $s ? 'selected' : '' }}>{{ strtoupper($s) }}</option>
                    @endforeach
        </select>
        <button type="submit" class="admin-btn-primary px-5 py-2.5 text-xs">Tapis</button>
    </form>
</div>

<div class="admin-table-card">
    <div class="admin-table-wrap min-h-[400px]">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>Status</th>
                    <th>Logistik</th>
                    <th>Jumlah</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <div class="space-y-1">
                                <p class="font-semibold text-slate-900">ST-{{ $order->order_no }}</p>
                                <p class="text-xs text-slate-500">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="admin-icon-badge">
                                    {{ strtoupper(substr($order->customer_name, 0, 1)) }}
                                </div>
                                <div class="space-y-1">
                                    <p class="font-semibold text-slate-900">{{ $order->customer_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $order->customer_phone }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $statusClasses = match($order->status) {
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'paid' => 'bg-emerald-100 text-emerald-700',
                                    'processing' => 'bg-sky-100 text-sky-700',
                                    'shipped' => 'bg-rose-100 text-rose-700',
                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-slate-200 text-slate-700',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <span class="admin-status {{ $statusClasses }}">{{ $order->status }}</span>
                        </td>
                        <td>
                            @if($order->tracking_no)
                                <span class="admin-soft-badge">{{ $order->tracking_no }}</span>
                            @else
                                <span class="text-xs text-slate-400">Belum ada tracking</span>
                            @endif
                        </td>
                        <td class="font-semibold text-slate-900">RM {{ number_format($order->total, 2) }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="admin-btn-secondary px-4 py-2 text-xs">Lihat</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6">
                            <div class="admin-table-empty">
                                <p class="admin-table-empty-title">Belum ada tempahan.</p>
                                <p class="admin-table-empty-copy">Rekod baharu akan muncul di sini sebaik sahaja pelanggan membuat pesanan.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($orders->hasPages())
<div class="mt-8 px-2">
    {{ $orders->links() }}
</div>
@endif
</div>

@endsection

