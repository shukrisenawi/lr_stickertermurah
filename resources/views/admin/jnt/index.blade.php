@extends('layouts.admin')

@section('title', 'J&T Waybill & Tracking')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase leading-none">J&T Shipping Center</h2>
    <p class="text-xs font-medium text-slate-500 tracking-wide mt-1">Cipta waybill, semak tracking, dan lihat senarai waybill.</p>
</div>

<div class="mb-6 rounded-2xl bg-white ring-1 ring-slate-200 p-4" x-data="{ tab: '{{ $activeTab }}' }">
    <div class="flex flex-wrap gap-2 mb-4">
        <button type="button" @click="tab='create'" :class="tab==='create' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="rounded-xl px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all">Create Waybill</button>
        <button type="button" @click="tab='tracking'" :class="tab==='tracking' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="rounded-xl px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all">Check Tracking</button>
        <button type="button" @click="tab='list'" :class="tab==='list' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'" class="rounded-xl px-4 py-2 text-[11px] font-black uppercase tracking-widest transition-all">Senarai Waybill</button>
    </div>

    <div x-show="tab==='create'" x-cloak>
        <form method="get" action="{{ route('admin.jnt.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end mb-5">
            <input type="hidden" name="tab" value="create">
            <div class="md:col-span-2">
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Pilih Order (Opsyenal)</label>
                <select name="order_id" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold text-slate-800">
                    <option value="">-- Manual Input --</option>
                    @foreach($orders as $order)
                        <option value="{{ $order->id }}" {{ (int) request('order_id') === $order->id ? 'selected' : '' }}>
                            {{ $order->order_no }} - {{ $order->customer_name }} ({{ $order->customer_phone }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-[11px] font-black uppercase tracking-widest text-white hover:bg-brand-600 transition-all">Load Order</button>
        </form>

        <form method="post" action="{{ route('admin.jnt.waybill') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="order_id" value="{{ old('order_id', $selectedOrder?->id) }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">TxLogistic ID (Opsyenal)</label>
                    <input type="text" name="txlogistic_id" value="{{ old('txlogistic_id') }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold" placeholder="Auto jika kosong">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Express Type</label>
                    <select name="express_type" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold">
                        @foreach(['EZ','EX','FD','DO','JS'] as $type)
                            <option value="{{ $type }}" {{ old('express_type', 'EZ') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Service Type</label>
                    <select name="service_type" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold">
                        <option value="1" {{ old('service_type', '1') === '1' ? 'selected' : '' }}>1 - Pickup</option>
                        <option value="6" {{ old('service_type') === '6' ? 'selected' : '' }}>6 - Walk In</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Pay Type</label>
                    <select name="pay_type" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold">
                        @foreach(['PP_PM','PP_CASH','CC_CASH'] as $type)
                            <option value="{{ $type }}" {{ old('pay_type', 'PP_CASH') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Receiver Name</label>
                    <input type="text" name="receiver_name" value="{{ old('receiver_name', $selectedOrder?->customer_name) }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold" required>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Receiver Phone</label>
                    <input type="text" name="receiver_phone" value="{{ old('receiver_phone', $selectedOrder?->customer_phone) }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold" required>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Country Code</label>
                    <input type="text" name="receiver_country_code" value="{{ old('receiver_country_code', 'MYS') }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold uppercase" required>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Postcode</label>
                    <input type="text" name="receiver_postcode" value="{{ old('receiver_postcode') }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold" required>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Receiver Address</label>
                <textarea name="receiver_address" rows="2" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold" required>{{ old('receiver_address', $selectedOrder?->customer_address) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Item Name</label>
                    <input type="text" name="item_name" value="{{ old('item_name', 'Sticker Printing') }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold" required>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Item Qty</label>
                    <input type="number" step="1" min="1" name="item_quantity" value="{{ old('item_quantity', 1) }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold" required>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Goods Type</label>
                    <select name="goods_type" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold">
                        <option value="ITN2" {{ old('goods_type', 'ITN8') === 'ITN2' ? 'selected' : '' }}>ITN2</option>
                        <option value="ITN8" {{ old('goods_type', 'ITN8') === 'ITN8' ? 'selected' : '' }}>ITN8</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Item Weight (KG)</label>
                    <input type="number" step="0.01" min="0.01" name="item_weight" value="{{ old('item_weight', 1) }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold" required>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Item Value (MYR)</label>
                    <input type="number" step="0.01" min="0.01" name="item_value" value="{{ old('item_value', $selectedOrder?->total) }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold" required>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Package Qty</label>
                    <input type="number" step="1" min="1" name="package_quantity" value="{{ old('package_quantity', 1) }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold" required>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Package Weight (KG)</label>
                    <input type="number" step="0.01" min="0.01" name="package_weight" value="{{ old('package_weight', 1) }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold" required>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Package Value (MYR)</label>
                    <input type="number" step="0.01" min="0.01" name="package_value" value="{{ old('package_value', $selectedOrder?->total) }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold" required>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Remark</label>
                    <input type="text" name="remark" value="{{ old('remark') }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold">
                </div>
            </div>

            <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-[11px] font-black uppercase tracking-widest text-white hover:bg-brand-600 transition-all">Create Waybill</button>
        </form>

        @if($waybillResult)
            <div class="mt-4 rounded-2xl bg-white ring-1 ring-slate-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-emerald-50">
                    <h4 class="text-[11px] font-black uppercase tracking-widest text-emerald-700">Waybill Response</h4>
                </div>
                <pre class="p-4 text-[11px] text-slate-700 overflow-auto bg-slate-50">{{ json_encode($waybillResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        @endif
    </div>

    <div x-show="tab==='tracking'" x-cloak>
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-800">Check Tracking</h3>
            </div>
            <form method="post" action="{{ route('admin.jnt.tracking') }}" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Bill Code (AWB)</label>
                    <input type="text" name="bill_code" value="{{ old('bill_code') }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold" placeholder="Contoh: 630002864925">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">TxLogistic ID</label>
                    <input type="text" name="txlogistic_id" value="{{ old('txlogistic_id') }}" class="w-full rounded-xl border-0 bg-slate-50 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold" placeholder="Opsyenal jika Bill Code kosong">
                </div>
                <button type="submit" class="w-full rounded-xl bg-brand-600 px-4 py-3 text-[11px] font-black uppercase tracking-widest text-white hover:bg-brand-700 transition-all">Check Tracking</button>
            </form>
        </div>

        @if($trackingResult)
            <div class="mt-4 rounded-2xl bg-white ring-1 ring-slate-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-sky-50">
                    <h4 class="text-[11px] font-black uppercase tracking-widest text-sky-700">Tracking Response</h4>
                </div>
                <pre class="p-4 text-[11px] text-slate-700 overflow-auto bg-slate-50">{{ json_encode($trackingResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        @endif
    </div>

    <div x-show="tab==='list'" x-cloak>
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-800">Senarai Waybill</h3>
                <form method="get" action="{{ route('admin.jnt.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="list">
                    <input type="text" name="waybill_q" value="{{ $waybillSearch }}" placeholder="Cari waybill/order/pelanggan" class="rounded-xl border-0 bg-white ring-1 ring-slate-200 px-3 py-2 text-xs font-bold w-64">
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-[11px] font-black uppercase tracking-widest text-white">Cari</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-500">Waybill</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-500">Order</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-500">Pelanggan</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-500">Telefon</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-500">Tarikh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($waybills as $waybill)
                            <tr>
                                <td class="px-4 py-3 font-black text-slate-900">{{ $waybill->tracking_no }}</td>
                                <td class="px-4 py-3 font-bold text-slate-700">{{ $waybill->order_no }}</td>
                                <td class="px-4 py-3 font-bold text-slate-700">{{ $waybill->customer_name }}</td>
                                <td class="px-4 py-3 font-bold text-slate-700">{{ $waybill->customer_phone }}</td>
                                <td class="px-4 py-3 font-bold uppercase text-slate-600">{{ $waybill->status }}</td>
                                <td class="px-4 py-3 font-bold text-slate-600">{{ $waybill->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-xs font-bold text-slate-400">Tiada data waybill dijumpai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($waybills->hasPages())
                <div class="px-4 py-4 border-t border-slate-100">
                    {{ $waybills->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@if($errors->any())
    <div class="mt-6 rounded-2xl bg-rose-50 border border-rose-200 p-4">
        <h4 class="text-xs font-black uppercase tracking-widest text-rose-700 mb-2">Validation Error</h4>
        <ul class="text-xs font-bold text-rose-700 space-y-1">
            @foreach($errors->all() as $error)
                <li>- {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@endsection
