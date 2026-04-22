@extends('layouts.admin')

@section('title', 'Saiz & Harga Sticker')

@section('content')
@php
    $sizeCollection = $sizes->getCollection()->values();
    $chunkSize = max((int) ceil($sizeCollection->count() / 3), 1);
    $sizeChunks = $sizeCollection->chunk($chunkSize);
@endphp

<div class="space-y-6">
<div class="admin-page-head">
    <div class="space-y-3">
        <div class="admin-title-block">
            <span class="admin-title-accent"></span>
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Saiz & Kos Produksi</h1>
                <p class="admin-page-copy">Tetapkan parameter saiz dan konfigurasi harga jualan dengan jadual yang sekeluarga dengan dashboard.</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-3">
            <span class="admin-pill">{{ $sizes->total() }} saiz tersedia</span>
        </div>
    </div>

    <div class="admin-page-actions">
        <a href="{{ route('admin.sizes.create') }}" class="admin-btn-primary">Tambah Saiz</a>
    </div>
</div>

<div class="grid grid-cols-1 2xl:grid-cols-3 gap-4">
    @for($cardIndex = 0; $cardIndex < 3; $cardIndex++)
        @php $chunk = $sizeChunks->get($cardIndex, collect()); @endphp

        <div class="admin-table-card">
            <div class="border-b border-slate-200 px-5 py-4">
                <p class="admin-mini-label">Kumpulan {{ $cardIndex + 1 }}</p>
                <p class="mt-1 text-sm text-slate-500">Senarai saiz untuk pengurusan harga dan status aktif.</p>
            </div>

            <div class="admin-table-wrap min-h-[300px]">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Pilihan Saiz</th>
                            <th>Seunit</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($chunk as $size)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <div class="admin-icon-badge h-9 w-9 rounded-xl">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" /></svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-slate-900">
                                                {{ $size->name }}
                                            </span>
                                            @if($size->is_default)
                                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-600">Utama</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="font-semibold text-brand-600">RM {{ number_format($size->price, 2) }}</td>

                                <td class="text-center">
                                    @if($size->is_active)
                                        <span class="admin-status bg-emerald-100 text-emerald-700">Aktif</span>
                                    @else
                                        <span class="admin-status bg-slate-200 text-slate-700">Tidak Aktif</span>
                                    @endif
                                </td>

                                <td class="text-right">
                                    <div class="flex justify-end items-center gap-1.5">
                                        <a href="{{ route('admin.sizes.edit', $size) }}" class="admin-btn-secondary px-4 py-2 text-xs">Edit</a>
                                        <form method="post" action="{{ route('admin.sizes.destroy', $size) }}" class="inline-block">
                                            @csrf @method('delete')
                                            <button type="submit" class="admin-btn-secondary px-4 py-2 text-xs text-rose-600" onclick="return confirm('Padam saiz ini?')">Padam</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6">
                                    <div class="admin-table-empty">
                                        <p class="admin-table-empty-title">Tiada data untuk kumpulan ini.</p>
                                        <p class="admin-table-empty-copy">Saiz akan dipaparkan semula apabila rekod baru tersedia.</p>
                                    </div>
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
<div class="mt-8 px-2">
    {{ $sizes->links() }}
</div>
@endif
</div>

@endsection
