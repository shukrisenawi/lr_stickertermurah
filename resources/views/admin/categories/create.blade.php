@extends('layouts.admin')

@section('title', 'Tambah Kategori Baharu')

@section('content')
<div class="max-w-2xl">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Tambah Kategori</h1>
        <p class="mt-1 text-sm text-slate-500 font-medium">Lengkapkan maklumat di bawah untuk menambah kategori produk.</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <form method="post" action="{{ route('admin.categories.store') }}" class="p-8 space-y-6">
            @csrf
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 pl-1">Nama Kategori</label>
                <input type="text" name="name" value="{{ old('name') }}" class="block w-full rounded-2xl border-0 py-3 px-4 text-slate-900 font-bold tracking-wider ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-brand-600 text-sm leading-6 shadow-sm" placeholder="Contoh: Sticker Kereta" required autofocus>
            </div>

            <div class="flex items-center gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <div class="flex h-6 items-center">
                    <input id="is_active" name="is_active" type="checkbox" value="1" checked class="h-5 w-5 rounded-lg border-slate-300 text-brand-600 focus:ring-brand-600 cursor-pointer">
                </div>
                <div class="text-sm leading-6">
                    <label for="is_active" class="font-bold text-slate-900 cursor-pointer">Kategori Aktif</label>
                    <p class="text-slate-500 text-xs">Aktifkan untuk memaparkan kategori ini di bahagian pelanggan.</p>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('admin.categories.index') }}" class="text-xs font-black text-slate-500 hover:text-slate-700 uppercase tracking-widest px-4 py-2 transition-colors">Batal</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-600 px-8 py-3.5 text-sm font-black text-white shadow-lg shadow-brand-100 hover:bg-brand-500 transition-all border-b-4 border-brand-800 active:border-b-0 active:translate-y-1">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


