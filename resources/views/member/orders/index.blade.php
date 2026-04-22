@extends('layouts.frontend')

@section('title', 'Senarai Order Ahli')

@section('content')
<div class="space-y-6">
    <section class="space-y-4">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="frontend-section-head">
                <span class="frontend-section-accent"></span>
                <div>
                    <h1 class="frontend-title">Semua Order Saya</h1>
                    <p class="frontend-copy">Lihat order lepas, repeat order, dan semak invoice dalam satu paparan yang lebih kemas.</p>
                </div>
            </div>
            <a href="{{ route('orders.create') }}" class="frontend-btn-primary">Buat Tempahan Baru</a>
        </div>
    </section>

    <div class="frontend-flat-card overflow-hidden">
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>No Order</th>
                        <th>Tarikh</th>
                        <th>Status</th>
                        <th>Jumlah</th>
                        <th class="text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="font-semibold text-slate-900">{{ $order->order_no }}</td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                            <td>{{ ucfirst($order->status) }}</td>
                            <td class="font-semibold text-brand-600">RM {{ number_format($order->total, 2) }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('member.orders.show', $order) }}" class="frontend-btn-secondary px-3 py-2 text-xs">Detail</a>
                                    @if($order->invoice)
                                        <a href="{{ route('member.invoices.show', $order->invoice) }}" class="rounded-xl bg-emerald-100 px-3 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">Invoice</a>
                                    @endif
                                    <form method="post" action="{{ route('member.orders.repeat', $order) }}">
                                        @csrf
                                        <button type="submit" class="rounded-xl bg-brand-600 px-3 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-white">Repeat</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">Belum ada order direkodkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
