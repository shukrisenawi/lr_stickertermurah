@extends('layouts.admin')

@section('title', 'Create Invoice')

@section('content')
<div class="space-y-6">
    <div class="admin-page-head">
        <div class="space-y-3">
            <div class="admin-title-block">
                <span class="admin-title-accent"></span>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Create Invoice Customer</h1>
                    <p class="admin-page-copy">Pilih order pelanggan yang belum ada invois, kemudian jana terus dari jadual ini.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="admin-pill">{{ $orders->total() }} order menunggu invois</span>
            </div>
        </div>

        <div class="admin-page-actions">
            <a href="{{ route('admin.invoices.manual.create') }}" class="admin-btn-secondary">
                    Add Invoice Manual
            </a>
        </div>
    </div>

    <div class="admin-toolbar-card">
        <div>
            <p class="admin-mini-label">Carian order</p>
            <p class="mt-2 text-sm text-slate-500">Cari order no, nama pelanggan, telefon, atau emel sebelum jana invois.</p>
        </div>
        <form method="get" class="admin-search-form">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari order no / nama / hp / email"
                    class="w-full md:w-80"
                >
                <button type="submit" class="admin-btn-primary px-5 py-2.5 text-xs">
                    Cari
                </button>
            </form>
    </div>

    <div class="admin-table-card">
        <div class="admin-table-wrap min-h-[420px]">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Jumlah</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <p class="font-semibold text-slate-900">{{ $order->order_no }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $order->created_at?->format('d M Y, h:i A') }}</p>
                            </td>
                            <td>
                                <p class="font-semibold text-slate-900">{{ $order->customer_name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $order->customer_phone }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $order->user?->email ?? '-' }}</p>
                            </td>
                            <td class="font-semibold text-slate-900">RM {{ number_format((float) $order->total, 2) }}</td>
                            <td>
                                <form method="post" action="{{ route('admin.invoices.store-from-menu') }}" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                                    <textarea
                                        name="notes"
                                        rows="2"
                                        placeholder="Nota invoice (optional)"
                                        class="w-full min-w-[220px]"
                                    ></textarea>
                                    <button type="submit" class="admin-btn-primary px-4 py-2 text-xs">
                                        Create Invoice
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6">
                                <div class="admin-table-empty">
                                    <p class="admin-table-empty-title">Tiada order untuk invoice.</p>
                                    <p class="admin-table-empty-copy">Semua order sudah ada invois atau tiada data padanan dengan carian semasa.</p>
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
