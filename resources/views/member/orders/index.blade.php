@extends('layouts.frontend')

@section('title', 'Senarai Order Ahli')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900">Semua Order Saya</h1>
            <p class="mt-2 text-sm text-slate-500 font-medium">Lihat order lepas, repeat order dan semak invoice.</p>
        </div>
        <a href="{{ route('orders.create') }}" class="inline-flex rounded-2xl bg-brand-600 px-5 py-3 text-xs font-black uppercase tracking-widest text-white hover:bg-brand-700">Buat Tempahan Baru</a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">No Order</th>
                        <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Tarikh</th>
                        <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                        <th class="px-5 py-4 text-left text-[10px] font-black uppercase tracking-widest text-slate-400">Jumlah</th>
                        <th class="px-5 py-4 text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr>
                            <td class="px-5 py-4 text-sm font-black text-slate-900">{{ $order->order_no }}</td>
                            <td class="px-5 py-4 text-sm font-semibold text-slate-600">{{ $order->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-4 text-sm font-semibold text-slate-600">{{ ucfirst($order->status) }}</td>
                            <td class="px-5 py-4 text-sm font-black text-brand-600">RM {{ number_format($order->total, 2) }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('member.orders.show', $order) }}" class="rounded-xl bg-slate-100 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-slate-700">Detail</a>
                                    @if($order->invoice)
                                        <a href="{{ route('member.invoices.show', $order->invoice) }}" class="rounded-xl bg-emerald-100 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-emerald-700">Invoice</a>
                                    @endif
                                    <form method="post" action="{{ route('member.orders.repeat', $order) }}">
                                        @csrf
                                        <button type="submit" class="rounded-xl bg-brand-600 px-3 py-2 text-[10px] font-black uppercase tracking-widest text-white">Repeat</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm font-medium text-slate-500">Belum ada order direkodkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
