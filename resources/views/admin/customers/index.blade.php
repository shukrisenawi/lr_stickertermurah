@extends('layouts.admin')

@section('title', 'Senarai Pelanggan')

@section('content')
<!-- Page Header & Search -->
<div class="mb-10 flex flex-col xl:flex-row xl:items-end justify-between gap-8 border-b-2 border-slate-100 pb-8">
    <div>
        <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase leading-none mb-2">Pangkalan Pelanggan</h2>
        <p class="text-[11px] font-black text-slate-400 tracking-widest uppercase">Pantau ahli, alamat terbaru, dan nilai pembelian mereka dalam satu paparan tumpu.</p>
    </div>

    <form method="get" class="flex items-center gap-0 group">
        <div class="relative">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="CARI NAMA, EMEL, TELEFON..."
                class="flat-input !h-12 w-80 !bg-white !border-r-0 !rounded-r-none focus:!border-slate-900 text-[10px] font-black tracking-widest uppercase placeholder:text-slate-300"
            >
        </div>
        <button type="submit" class="flat-btn-primary !h-12 px-8 !rounded-l-none text-[11px] font-black uppercase tracking-widest">
            CARI
        </button>
        @if($search)
            <a href="{{ route('admin.customers.index') }}" class="h-12 flex items-center px-4 text-[10px] font-black text-slate-400 hover:text-rose-600 transition-colors uppercase tracking-widest">Reset</a>
        @endif
    </form>
</div>

<!-- Stats Dashboard -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-8 mb-10">
    <div class="flat-card !border-slate-900 bg-slate-900 !p-8 group hover:bg-slate-800 transition-colors">
        <p class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400 mb-4 flex items-center gap-2">
            <span class="h-1.5 w-1.5 bg-brand rounded-full"></span>
            JUMLAH PELANGGAN
        </p>
        <div class="flex items-baseline gap-2">
            <p class="text-5xl font-black tracking-tighter text-white italic">{{ number_format($totalCustomers) }}</p>
            <span class="text-[10px] font-black text-slate-500 uppercase">AHLI</span>
        </div>
    </div>
    <div class="flat-card !p-8 group hover:border-emerald-600 transition-colors">
        <p class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400 mb-4 flex items-center gap-2">
            <span class="h-1.5 w-1.5 bg-emerald-500 rounded-full"></span>
            PELANGGAN AKTIF
        </p>
        <div class="flex items-baseline gap-2">
            <p class="text-5xl font-black tracking-tighter text-slate-900 italic">{{ number_format($customersWithOrders) }}</p>
            <span class="text-[10px] font-black text-slate-300 uppercase">BERTRANSAKSI</span>
        </div>
    </div>
    <div class="flat-card !p-8 group hover:border-brand transition-colors">
        <p class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400 mb-4 flex items-center gap-2">
            <span class="h-1.5 w-1.5 bg-brand rounded-full"></span>
            ADA ALAMAT
        </p>
        <div class="flex items-baseline gap-2">
            <p class="text-5xl font-black tracking-tighter text-slate-900 italic">{{ number_format($customersWithAddresses) }}</p>
            <span class="text-[10px] font-black text-slate-300 uppercase">TERSALUR</span>
        </div>
    </div>
</div>

<!-- Main Table -->
<div class="flat-card !p-0 overflow-hidden">
    <div class="overflow-x-auto custom-scrollbar min-h-[420px]">
        <table class="min-w-full divide-y divide-slate-100">
            <thead>
                <tr class="bg-slate-50">
                    <th class="py-5 px-8 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest">MAKLUMAT PELANGGAN</th>
                    <th class="px-4 py-5 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest">TELEFON / HP</th>
                    <th class="px-4 py-5 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest">ALAMAT TERKINI</th>
                    <th class="px-4 py-5 text-center text-[10px] font-black text-slate-900 uppercase tracking-widest">TRX</th>
                    <th class="px-4 py-5 text-right text-[10px] font-black text-slate-900 uppercase tracking-widest">NILAI BELIAN</th>
                    <th class="py-5 px-8 text-right text-[10px] font-black text-slate-900 uppercase tracking-widest">AKTIVITI TERKINI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 bg-white">
                @forelse($customers as $customer)
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="py-5 px-8">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-sm bg-slate-900 flex items-center justify-center text-brand font-black text-sm border-b-2 border-slate-700">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div class="flex flex-col gap-0.5">
                                    <p class="text-[11px] font-black text-slate-900 uppercase tracking-tight leading-none group-hover:text-brand transition-colors">{{ $customer->name }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 tracking-tight">{{ $customer->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-5">
                            <span class="text-[11px] font-black text-slate-600 tracking-widest uppercase italic">{{ $customer->defaultCustomerAddress?->no_hp ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-5">
                            <p class="text-[10px] font-bold text-slate-500 max-w-[280px] leading-relaxed uppercase tracking-tight">
                                {{ $customer->defaultCustomerAddress?->address ? \Illuminate\Support\Str::limit($customer->defaultCustomerAddress->address, 80) : '-' }}
                            </p>
                        </td>
                        <td class="px-4 py-5 text-center">
                            <span class="inline-flex h-7 w-7 items-center justify-center bg-slate-100 text-[10px] font-black text-slate-900 rounded-sm border border-slate-200 uppercase tracking-tighter">
                                {{ $customer->orders_count }}
                            </span>
                        </td>
                        <td class="px-4 py-5 text-right">
                            <span class="text-sm font-black text-slate-900 italic tracking-tighter">RM {{ number_format((float) ($customer->orders_sum_total ?? 0), 2) }}</span>
                        </td>
                        <td class="py-5 px-8 text-right">
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest italic group-hover:text-brand transition-all">
                                    {{ $customer->latestOrder?->created_at?->diffForHumans() ?? 'TIADA REKOD' }}
                                </span>
                                <span class="text-[9px] font-bold text-slate-300 uppercase tracking-widest">
                                    {{ $customer->latestOrder?->created_at?->format('d/m/Y') ?? '' }}
                                </span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-32 text-center">
                            <div class="h-20 w-20 rounded-sm bg-slate-50 border-2 border-slate-100 flex items-center justify-center text-slate-200 mx-auto mb-6">
                                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-2.124-7.521 4.125 4.125 0 0 0-3.375 5.874l-2.25 2.25Zm-3 1.122v-4.5m0 4.5a3 3 0 0 1-3-3m3 3a3 3 0 0 0 3-3m-3 3V12" /></svg>
                            </div>
                            <h3 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tighter italic">TIADA PELANGGAN DIJUMPA</h3>
                            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest italic">Cuba ubah kata carian anda atau reset tapisan.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($customers->hasPages())
<div class="mt-12">
    {{ $customers->links() }}
</div>
@endif
@endsection




