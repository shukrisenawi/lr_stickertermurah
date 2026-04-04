@extends('layouts.admin')

@section('title', 'Tambah Saiz & Harga')

@section('content')
<div class="max-w-3xl">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Tambah Saiz & Harga</h1>
            <p class="mt-1 text-sm text-slate-500 font-medium">Tetapkan dimensi dan harga unit untuk produk sticker.</p>
        </div>
        <a href="{{ route('admin.sizes.index') }}" class="text-xs font-black text-slate-500 hover:text-slate-700 uppercase tracking-widest transition-colors">Kembali</a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm ring-1 ring-slate-200 overflow-hidden text-sm">
        <form method="post" action="{{ route('admin.sizes.store') }}" class="p-8 space-y-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 pl-1">Nama Pilihan (cth: Saiz A4 / Custom)</label>
                    <input type="text" name="name" class="block w-full rounded-2xl border-0 py-3.5 px-4 text-slate-900 font-bold tracking-wider ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-600 shadow-sm" placeholder="Masukkan nama label saiz..." required autofocus>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 pl-1">Lebar (cm)</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="width_cm" class="block w-full rounded-2xl border-0 py-3.5 pl-4 pr-12 text-slate-900 font-bold tracking-wider ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-600 shadow-sm" placeholder="0.00">
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                            <span class="text-xs font-black text-slate-300">CM</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 pl-1">Tinggi (cm)</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="height_cm" class="block w-full rounded-2xl border-0 py-3.5 pl-4 pr-12 text-slate-900 font-bold tracking-wider ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-600 shadow-sm" placeholder="0.00">
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                            <span class="text-xs font-black text-slate-300">CM</span>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 pl-1">Harga Seunit (RM)</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <span class="text-xs font-black text-slate-400 pr-2 border-r border-slate-100">RM</span>
                        </div>
                        <input type="number" step="0.01" name="price" class="block w-full rounded-2xl border-0 py-3.5 pl-16 pr-4 text-indigo-600 font-black tracking-wider ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-600 shadow-sm" placeholder="0.00" required>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100 group transition-colors hover:border-amber-200 hover:bg-amber-50/30">
                    <div class="flex h-6 items-center">
                        <input id="is_default" name="is_default" type="checkbox" value="1" class="h-5 w-5 rounded-lg border-slate-300 text-amber-600 focus:ring-amber-600 cursor-pointer">
                    </div>
                    <div>
                        <label for="is_default" class="font-bold text-slate-900 cursor-pointer">Set Utama (Default)</label>
                        <p class="text-slate-500 text-[11px]">Saiz ini akan dipilih secara automatik di borang.</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100 group transition-colors hover:border-emerald-200 hover:bg-emerald-50/30">
                    <div class="flex h-6 items-center">
                        <input id="is_active" name="is_active" type="checkbox" value="1" checked class="h-5 w-5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-600 cursor-pointer">
                    </div>
                    <div>
                        <label for="is_active" class="font-bold text-slate-900 cursor-pointer">Status Aktif</label>
                        <p class="text-slate-500 text-[11px]">Aktifkan untuk memaparkan pilihan ini.</p>
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-8 py-4 text-sm font-black text-white shadow-xl hover:bg-slate-800 transition-all border-b-4 border-slate-700 active:border-b-0 active:translate-y-1">
                    Simpan Tetapan Saiz
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

