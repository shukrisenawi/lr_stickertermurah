@extends('layouts.admin')

@section('title', 'Senarai Pelanggan')

@section('content')
<div class="space-y-6">
<div class="admin-page-head">
    <div class="space-y-3">
        <div class="admin-title-block">
            <span class="admin-title-accent"></span>
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Senarai Pelanggan</h1>
                <p class="admin-page-copy">Pantau ahli, alamat terbaru, dan nilai pembelian mereka dengan gaya paparan yang seragam.</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-3">
            <span class="admin-pill">{{ number_format($totalCustomers) }} pelanggan</span>
            <span class="admin-pill">{{ number_format($customersWithOrders) }} pernah order</span>
        </div>
    </div>

    <form method="get" class="admin-search-form">
        <input
            type="text"
            name="q"
            value="{{ $search }}"
            placeholder="Cari nama, emel, atau telefon"
            class="w-full md:w-72"
        >
        <button type="submit" class="admin-btn-primary px-5 py-2.5 text-xs">
            Cari
        </button>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <article class="admin-kpi-card">
        <p class="admin-kpi-label">Jumlah Pelanggan</p>
        <p class="admin-kpi-value">{{ number_format($totalCustomers) }}</p>
    </article>
    <article class="admin-kpi-card">
        <p class="admin-kpi-label">Pelanggan Aktif</p>
        <p class="admin-kpi-value text-emerald-600">{{ number_format($customersWithOrders) }}</p>
    </article>
    <article class="admin-kpi-card">
        <p class="admin-kpi-label">Alamat Tersimpan</p>
        <p class="admin-kpi-value text-brand-600">{{ number_format($customersWithAddresses) }}</p>
    </article>
</div>

<div class="admin-table-card">
    <div class="admin-table-wrap min-h-[420px]">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>HP</th>
                    <th>Alamat Latest</th>
                    <th>Order</th>
                    <th>Jumlah Belian</th>
                    <th>Order Terakhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="admin-icon-badge">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $customer->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $customer->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            {{ $customer->defaultCustomerAddress?->no_hp ?? '-' }}
                        </td>
                        <td class="max-w-[320px]">
                            {{ $customer->defaultCustomerAddress?->address ? \Illuminate\Support\Str::limit($customer->defaultCustomerAddress->address, 95) : '-' }}
                        </td>
                        <td>
                            <span class="admin-soft-badge">
                                {{ $customer->orders_count }}
                            </span>
                        </td>
                        <td class="font-semibold text-slate-900">
                            RM {{ number_format((float) ($customer->orders_sum_total ?? 0), 2) }}
                        </td>
                        <td class="text-xs text-slate-500">
                            {{ $customer->latestOrder?->created_at?->format('d M Y, h:i A') ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6">
                            <div class="admin-table-empty">
                                <p class="admin-table-empty-title">Tiada pelanggan dijumpai.</p>
                                <p class="admin-table-empty-copy">Cuba ubah kata carian anda atau semak semula data pelanggan yang telah didaftarkan.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($customers->hasPages())
<div class="mt-8 px-2">
    {{ $customers->links() }}
</div>
@endif
</div>
@endsection


