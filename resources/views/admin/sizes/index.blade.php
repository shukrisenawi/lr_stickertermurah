@extends('layouts.admin')

@section('title', 'Saiz & Harga Sticker')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight mb-1 uppercase">Saiz & Kos Produksi</h2>
        <p class="text-xs font-medium text-slate-500 tracking-wide">Tetapkan parameter saiz dan konfigurasi harga jualan.</p>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.sizes.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-6 py-3 text-xs font-black text-white shadow-lg shadow-brand-100 hover:bg-brand-500 transition-all active:scale-95 group/btn">
            <svg class="h-4 w-4 transition-transform group-hover/btn:rotate-90" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            TAMBAH SAIZ
        </a>
    </div>
</div>

<!-- Sizes Table Card -->
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="overflow-x-auto min-h-[300px]">
        <table class="min-w-full border-separate border-spacing-0">
            <thead>
                <tr class="bg-slate-50">
                    <th scope="col" class="py-4 pl-6 pr-4 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Pilihan Saiz</th>

                    <th scope="col" class="px-4 py-4 text-left text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Harga Seunit</th>
                    <th scope="col" class="px-4 py-4 text-center text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Status</th>
                    <th scope="col" class="relative py-4 pl-4 pr-6 border-b border-slate-100 text-right">
                        <span class="sr-only">Tindakan</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 bg-white">
                @forelse($sizes as $size)
                    <tr class="hover:bg-brand-50/30 transition-colors group">
                        <td class="whitespace-nowrap py-4 pl-6 pr-4">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-brand-600 group-hover:text-white transition-all duration-300">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" /></svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-800 group-hover:text-brand-600 transition-colors leading-tight uppercase tracking-tight">
                                        {{ $size->name }}
                                    </span>
                                    @if($size->is_default)
                                        <span class="text-[8px] font-black text-amber-600 uppercase tracking-widest">UTAMA</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-4 py-4">
                            <div class="flex flex-col">
                                <span class="text-base font-black text-brand-600 leading-none">RM {{ number_format($size->price, 2) }}</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-center">
                            @if($size->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    AKTIF
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-slate-500 ring-1 ring-inset ring-slate-300/30">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    OFF
                                </span>
                            @endif
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-4 pr-6 text-right text-sm font-medium">
                            <div class="flex justify-end items-center gap-2 transform group-hover:-translate-x-1 transition-transform duration-300">
                                <a href="{{ route('admin.sizes.edit', $size) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-[11px] font-black text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 hover:ring-brand-300 transition-all uppercase tracking-widest">
                                    <svg class="h-3.5 w-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                    EDIT
                                </a>
                                <form method="post" action="{{ route('admin.sizes.destroy', $size) }}" class="inline-block">
                                    @csrf @method('delete')
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-[11px] font-black text-rose-600 shadow-sm ring-1 ring-rose-100 hover:bg-rose-50 hover:ring-rose-300 transition-all uppercase tracking-widest" onclick="return confirm('Padam saiz ini?')">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 6m-4.77 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        PADAM
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-20 text-center">
                            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 text-slate-200 mb-4">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" /></svg>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 mb-1 uppercase tracking-tight">Data Saiz Kosong</h3>
                            <p class="text-[11px] font-bold text-slate-400 italic">Mula buat konfigurasi harga.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($sizes->hasPages())
<div class="mt-8 px-2">
    {{ $sizes->links() }}
</div>
@endif

@endsection



