@extends('layouts.admin')

@section('title', 'Add Invoice Manual')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Add Invoice Manual</h2>
                <p class="mt-2 text-xs font-medium text-slate-500">Cipta invoice secara manual untuk customer berdasarkan order yang belum ada invoice.</p>
            </div>
            <a href="{{ route('admin.invoices.create') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-5 py-2 text-xs font-black uppercase tracking-widest text-white hover:bg-brand-600 transition-all">
                Kembali Senarai
            </a>
        </div>
    </div>

    <div class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm p-6">
        <form method="post" action="{{ route('admin.invoices.manual.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Pilih Order</label>
                <select name="order_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-brand-600" required>
                    <option value="">-- Pilih Order --</option>
                    @foreach($orders as $order)
                        <option value="{{ $order->id }}" @selected(old('order_id') == $order->id)>
                            {{ $order->order_no }} | {{ $order->customer_name }} | {{ $order->customer_phone }} | RM {{ number_format((float) $order->total, 2) }}
                        </option>
                    @endforeach
                </select>
                @error('order_id')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">No Invoice (Optional)</label>
                    <input type="text" name="invoice_no" value="{{ old('invoice_no') }}" placeholder="Auto generate jika kosong" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-brand-600">
                    @error('invoice_no')
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Tarikh Invoice</label>
                    <input type="date" name="issue_date" value="{{ old('issue_date', now()->toDateString()) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-brand-600" required>
                    @error('issue_date')
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Jumlah (RM)</label>
                    <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-brand-600" required>
                    @error('amount')
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Nota</label>
                <textarea name="notes" rows="4" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-brand-600" placeholder="Nota invoice (optional)">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-3 text-xs font-black uppercase tracking-widest text-white hover:bg-emerald-700 transition-all">
                    Simpan Invoice Manual
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
