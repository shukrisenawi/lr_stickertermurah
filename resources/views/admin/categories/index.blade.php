@extends('layouts.admin')

@section('title', 'Pengurusan Kategori')

@section('content')
<div class="space-y-6">
<div class="admin-page-head">
    <div class="space-y-3">
        <div class="admin-title-block">
            <span class="admin-title-accent"></span>
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Pengurusan Kategori</h1>
                <p class="admin-page-copy">Susun produk dan design mengikut kategori yang jelas dengan paparan jadual berasaskan gaya dashboard.</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-3">
            <span class="admin-pill">{{ $categories->total() }} kategori</span>
        </div>
    </div>
    
    <div class="admin-page-actions">
        <a href="{{ route('admin.categories.create') }}" class="admin-btn-primary">Tambah Kategori</a>
    </div>
</div>

<div class="admin-table-card">
    <div class="admin-table-wrap min-h-[300px]">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nama Kategori</th>
                    <th class="text-center">Status Capaian</th>
                    <th class="text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="admin-icon-badge">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581a2.25 2.25 0 0 0 3.182 0l4.318-4.318a2.25 2.25 0 0 0 0-3.182L11.16 3.659A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                                </div>
                                <span class="font-semibold text-slate-900">
                                    {{ $category->name }}
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($category->is_active)
                                <span class="admin-status bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span class="admin-status bg-slate-200 text-slate-700">Dinyahaktif</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end items-center gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="admin-btn-secondary px-4 py-2 text-xs">Edit</a>
                                <form method="post" action="{{ route('admin.categories.destroy', $category) }}" class="inline-block">
                                    @csrf @method('delete')
                                    <button type="submit" class="admin-btn-secondary px-4 py-2 text-xs text-rose-600" onclick="return confirm('Padam kategori ini? Produk berkaitan mungkin terjejas.')">Padam</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6">
                            <div class="admin-table-empty">
                                <p class="admin-table-empty-title">Kategori belum dibina.</p>
                                <p class="admin-table-empty-copy">Mula susun inventori anda dengan menambah kategori pertama.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($categories->hasPages())
<div class="mt-8 px-2">
    {{ $categories->links() }}
</div>
@endif
</div>

@endsection


