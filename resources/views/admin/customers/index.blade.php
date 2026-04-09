@extends('layouts.admin')

@section('title', 'Senarai Pelanggan')

@section('content')
<div class="mb-6 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight mb-1 uppercase leading-none">Senarai Pelanggan</h2>
        <p class="text-xs font-medium text-slate-500 tracking-wide">Pantau ahli, alamat terbaru, dan nilai pembelian mereka.</p>
    </div>

    <form method="get" class="flex items-center gap-2 p-1.5 bg-white rounded-2xl shadow-sm ring-1 ring-slate-200">
        <input
            type="text"
            name="q"
            value="{{ $search }}"
            placeholder="Cari nama, emel, atau telefon"
            class="w-64 rounded-xl border-0 bg-slate-50 px-4 py-2 text-xs font-bold text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-brand-600"
        >
        <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2 text-[10px] font-black uppercase tracking-widest text-white shadow-md hover:bg-brand-600 transition-all active:scale-95">
            Cari
        </button>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm">
        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">Jumlah Pelanggan</p>
        <p class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ number_format($totalCustomers) }}</p>
    </div>
    <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm">
        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">Pelanggan Aktif</p>
        <p class="mt-2 text-3xl font-black tracking-tight text-emerald-600">{{ number_format($customersWithOrders) }}</p>
    </div>
    <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm">
        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">Dengan Alamat Tersimpan</p>
        <p class="mt-2 text-3xl font-black tracking-tight text-brand-600">{{ number_format($customersWithAddresses) }}</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
    <div class="overflow-x-auto custom-scrollbar min-h-[420px]">
        <table class="min-w-full border-separate border-spacing-0">
            <thead>
                <tr class="bg-slate-50/80">
                    <th class="py-4 pl-6 pr-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] border-b border-slate-100">Pelanggan</th>
                    <th class="px-4 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] border-b border-slate-100">HP</th>
                    <th class="px-4 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] border-b border-slate-100">Alamat Latest</th>
                    <th class="px-4 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] border-b border-slate-100">Order</th>
                    <th class="px-4 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] border-b border-slate-100">Jumlah Belian</th>
                    <th class="px-4 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] border-b border-slate-100">Order Terakhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 bg-white">
                @forelse($customers as $customer)
                    <tr class="hover:bg-brand-50/20 transition-all">
                        <td class="py-4 pl-6 pr-4">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-brand-600 font-black text-sm ring-1 ring-slate-100">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-800 uppercase tracking-tight leading-none">{{ $customer->name }}</p>
                                    <p class="mt-1 text-[10px] font-bold text-slate-500">{{ $customer->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-[11px] font-black text-slate-700 tracking-wide">
                            {{ $customer->defaultCustomerAddress?->no_hp ?? '-' }}
                        </td>
                        <td class="px-4 py-4 text-[11px] font-bold text-slate-600 max-w-[320px]">
                            {{ $customer->defaultCustomerAddress?->address ? \Illuminate\Support\Str::limit($customer->defaultCustomerAddress->address, 95) : '-' }}
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-black bg-slate-100 text-slate-700 ring-1 ring-slate-200">
                                {{ $customer->orders_count }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm font-black text-slate-900 tracking-tight">
                            RM {{ number_format((float) ($customer->orders_sum_total ?? 0), 2) }}
                        </td>
                        <td class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">
                            {{ $customer->latestOrder?->created_at?->format('d M Y, h:i A') ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-20 text-center">
                            <p class="text-xl font-black text-slate-900 mb-1 uppercase tracking-tight">Tiada Pelanggan Dijumpai</p>
                            <p class="text-slate-400 font-bold uppercase tracking-widest text-[9px]">Cuba ubah kata carian anda.</p>
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
@endsection



