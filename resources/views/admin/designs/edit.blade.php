@extends('layouts.admin')

@section('title', 'Kemaskini Design: ' . $design->name)

@section('content')
<div class="max-w-4xl">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Design</h1>
            <p class="mt-1 text-sm text-slate-500 font-medium">Kemaskini maklumat dan visual untuk design <span class="text-brand-600 font-bold">"{{ $design->name }}"</span>.</p>
        </div>
        <a href="{{ route('admin.designs.index') }}" class="text-xs font-black text-slate-500 hover:text-slate-700 uppercase tracking-widest transition-colors">Kembali</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Section -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <form method="post" action="{{ route('admin.designs.update', $design) }}" enctype="multipart/form-data" class="p-8 space-y-6">
                    @csrf @method('put')
                    
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 pl-1">Kategori Produk</label>
                            <select name="category_id" class="block w-full rounded-2xl border-0 py-3 pl-4 pr-10 text-slate-900 font-bold ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-brand-600 text-sm leading-6 cursor-pointer" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $design->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 pl-1">Nama Design</label>
                            <input type="text" name="name" value="{{ old('name', $design->name) }}" class="block w-full rounded-2xl border-0 py-3 px-4 text-slate-900 font-bold tracking-wider ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-brand-600 text-sm leading-6 shadow-sm" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 pl-1">Deskripsi Design</label>
                        <textarea name="description" rows="3" class="block w-full rounded-2xl border-0 py-3 px-4 text-slate-900 font-bold tracking-wide ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-brand-600 text-sm leading-6 shadow-sm">{{ old('description', $design->description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 pl-1">Tukar Gambar (Opsional)</label>
                        <div class="mt-2 flex justify-center rounded-2xl border border-dashed border-slate-300 px-6 py-8 bg-slate-50/50">
                            <div class="text-center">
                                <svg class="mx-auto h-10 w-10 text-slate-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                </svg>
                                <div class="mt-4 flex text-sm leading-6 text-slate-600">
                                    <label for="image" class="relative cursor-pointer rounded-md bg-transparent font-bold text-brand-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-brand-600 focus-within:ring-offset-2 hover:text-brand-500 transition-colors">
                                        <span>Pilih fail baharu</span>
                                        <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                                    </label>
                                </div>
                                <p class="text-xs leading-5 text-slate-500 italic">Biarkan kosong jika tidak mahu menukar gambar</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <div class="flex h-6 items-center">
                            <input id="is_active" name="is_active" type="checkbox" value="1" class="h-5 w-5 rounded-lg border-slate-300 text-brand-600 focus:ring-brand-600 cursor-pointer" {{ old('is_active', $design->is_active) ? 'checked' : '' }}>
                        </div>
                        <div class="text-sm leading-6">
                            <label for="is_active" class="font-bold text-slate-900 cursor-pointer">Design Aktif</label>
                            <p class="text-slate-500 text-xs">Pelanggan hanya boleh memilih design yang aktif.</p>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-8 py-4 text-sm font-black text-white shadow-xl hover:bg-slate-800 transition-all border-b-4 border-slate-700 active:border-b-0 active:translate-y-1">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Section -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-3xl shadow-sm ring-1 ring-slate-200 overflow-hidden sticky top-8">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-100">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Visual Semasa</h3>
                </div>
                <div class="p-6 text-center">
                    @if($design->image_path)
                        <div class="aspect-square w-full rounded-2xl overflow-hidden bg-slate-100 ring-1 ring-slate-200">
                            <img src="{{ asset('storage/'.$design->image_path) }}" class="h-full w-full object-cover" alt="{{ $design->name }}">
                        </div>
                        <div class="mt-4">
                            <p class="text-xs font-bold text-slate-400">Dimuat naik pada</p>
                            <p class="text-sm font-black text-slate-700 tracking-tight">{{ $design->updated_at->format('d M Y, h:i A') }}</p>
                        </div>
                    @else
                        <div class="aspect-square w-full rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 ring-1 ring-slate-200 border-2 border-dashed border-slate-200">
                            <p class="text-xs font-bold font-mono">TIADA GAMBAR</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


