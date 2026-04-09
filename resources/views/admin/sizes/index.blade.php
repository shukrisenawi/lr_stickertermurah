@extends('layouts.admin')

@section('title', 'Saiz & Harga Sticker')

@section('content')
@php
    $sizeCollection = $sizes->getCollection()->values();
    $chunkSize = max((int) ceil($sizeCollection->count() / 3), 1);
    $sizeChunks = $sizeCollection->chunk($chunkSize);
@endphp

<!-- Page Header -->
<div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6 border-b-2 border-slate-100 pb-8">
    <div>
        <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase leading-none mb-2">Saiz & Kos Produksi</h2>
        <p class="text-[11px] font-black text-slate-400 tracking-widest uppercase">Tetapkan parameter saiz dan konfigurasi harga jualan.</p>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.sizes.create') }}" class="flat-btn-primary !h-12 px-8 text-[11px] font-black uppercase tracking-widest flex items-center gap-3">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            TAMBAH SAIZ BARU
        </a>
    </div>
</div>

<div class="grid grid-cols-1 2xl:grid-cols-3 gap-8">
    @for($cardIndex = 0; $cardIndex < 3; $cardIndex++)
        @php $chunk = $sizeChunks->get($cardIndex, collect()); @endphp

        <div class="flat-card !p-0 overflow-hidden animate-in fade-in duration-500">
            <div class="overflow-x-auto min-h-[300px]">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead>
                        <tr class="bg-slate-50">
                            <th scope="col" class="py-4 pl-6 pr-3 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest">PILIHAN SAIZ</th>
                            <th scope="col" class="px-3 py-4 text-left text-[10px] font-black text-slate-900 uppercase tracking-widest">HARGA (RM)</th>
                            <th scope="col" class="w-10 px-2 py-4 text-center"></th>
                            <th scope="col" class="py-4 pl-2 pr-6 text-right text-[10px] font-black text-slate-900 uppercase tracking-widest">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        @forelse($chunk as $size)
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                    <div class="flex items-center gap-4">
                                        <div class="h-9 w-9 rounded-sm bg-slate-900 flex items-center justify-center text-brand border-b-2 border-slate-700">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" /></svg>
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[11px] font-black text-slate-900 uppercase tracking-tight">
                                                {{ $size->name }}
                                            </span>
                                            @if($size->is_default)
                                                <span class="text-[9px] font-black text-brand uppercase tracking-[0.2em] italic">UTAMA</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-3 py-4">
                                    <span class="text-sm font-black text-slate-900 italic tracking-tighter">RM {{ number_format($size->price, 2) }}</span>
                                </td>

                                <td class="whitespace-nowrap px-2 py-4 text-center">
                                    @if($size->is_active)
                                        <div class="inline-flex h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]" title="Aktif"></div>
                                    @else
                                        <div class="inline-flex h-2 w-2 rounded-full bg-slate-300" title="Tidak Aktif"></div>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap py-4 pl-2 pr-6 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        <a href="{{ route('admin.sizes.edit', $size) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-sm bg-white text-slate-900 border-2 border-slate-900 transition-all hover:bg-slate-900 hover:text-white" title="Edit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                        </a>
                                        <form method="post" action="{{ route('admin.sizes.destroy', $size) }}" class="inline-block">
                                            @csrf @method('delete')
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-sm bg-white text-rose-600 border-2 border-rose-200 transition-all hover:bg-rose-600 hover:text-white hover:border-rose-600" onclick="return confirm('Padam saiz ini?')" title="Padam">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 6m-4.77 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-20 text-center text-[10px] font-black text-slate-300 uppercase tracking-widest italic">
                                    TIADA REKOD DALAM KAD INI
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endfor
</div>

@if($sizes->hasPages())
<div class="mt-10">
    {{ $sizes->links() }}
</div>
@endif

@endsection
