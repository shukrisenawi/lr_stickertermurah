@extends('layouts.admin')

@section('title', 'Pengurusan Kategori')

@section('content')
<!-- Page Header -->
<div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
        <h2 class="text-3xl font-black text-slate-900 tracking-tight mb-2 uppercase">Pengurusan Kategori</h2>
        <p class="text-slate-500 font-medium tracking-wide">Susun produk dan design anda mengikut kategori yang sesuai untuk carian pelanggan.</p>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center justify-center gap-3 rounded-2xl bg-indigo-600 px-8 py-4 text-sm font-black text-white shadow-xl shadow-indigo-100 hover:bg-indigo-500 transition-all active:scale-95 group/btn">
            <svg class="h-5 w-5 transition-transform group-hover/btn:rotate-90" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            TAMBAH KATEGORI
        </a>
    </div>
</div>

<!-- Categories Table Card -->
<div class="bg-white rounded-[2.5rem] shadow-sm ring-1 ring-slate-200 overflow-hidden animate-in fade-in slide-in-from-bottom-8 duration-700">
    <div class="overflow-x-auto min-h-[400px]">
        <table class="min-w-full border-separate border-spacing-0">
            <thead>
                <tr class="bg-slate-50">
                    <th scope="col" class="py-6 pl-10 pr-4 text-left text-xs font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Nama Kategori</th>
                    <th scope="col" class="px-4 py-6 text-center text-xs font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Status Capaian</th>
                    <th scope="col" class="relative py-6 pl-4 pr-10 border-b border-slate-100 text-right">
                        <span class="sr-only">Tindakan</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 bg-white">
                @forelse($categories as $category)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="whitespace-nowrap py-8 pl-10 pr-4">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581a2.25 2.25 0 0 0 3.182 0l4.318-4.318a2.25 2.25 0 0 0 0-3.182L11.16 3.659A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                                </div>
                                <span class="text-base font-black text-slate-800 group-hover:text-indigo-600 transition-colors leading-tight uppercase tracking-tight">
                                    {{ $category->name }}
                                </span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-8 text-center">
                            @if($category->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    AKTIF
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-slate-500 ring-1 ring-inset ring-slate-300/30">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    DINYAHAKTIF
                                </span>
                            @endif
                        </td>
                        <td class="relative whitespace-nowrap py-8 pl-4 pr-10 text-right text-sm font-medium">
                            <div class="flex justify-end items-center gap-3 transform group-hover:-translate-x-2 transition-transform duration-300">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 hover:ring-indigo-300 transition-all uppercase tracking-widest">
                                    <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                    EDIT
                                </a>
                                <form method="post" action="{{ route('admin.categories.destroy', $category) }}" class="inline-block">
                                    @csrf @method('delete')
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-xs font-black text-rose-600 shadow-sm ring-1 ring-rose-100 hover:bg-rose-50 hover:ring-rose-300 transition-all uppercase tracking-widest" onclick="return confirm('Padam kategori ini? Produk berkaitan mungkin terjejas.')">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 6m-4.77 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        PADAM
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-32 text-center">
                            <div class="inline-flex h-24 w-24 items-center justify-center rounded-[2rem] bg-slate-50 text-slate-200 mb-6">
                                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581a2.25 2.25 0 0 0 3.182 0l4.318-4.318a2.25 2.25 0 0 0 0-3.182L11.16 3.659A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                            </div>
                            <h3 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tight">Kategori Belum Dibina</h3>
                            <p class="text-sm font-bold text-slate-400 italic">Mula susun inventori anda dengan menambah kategori pertama.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($categories->hasPages())
<div class="mt-12 px-2">
    {{ $categories->links() }}
</div>
@endif

@endsection

