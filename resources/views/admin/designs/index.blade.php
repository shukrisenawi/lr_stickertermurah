@extends('layouts.admin')

@section('title', 'Koleksi Design Grafik')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight mb-1 uppercase">Koleksi Design Grafik</h2>
        <p class="text-xs font-medium text-slate-500 tracking-wide">Uruskan pustaka design yang boleh dipilih oleh pelanggan.</p>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.designs.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-6 py-3 text-xs font-black text-white shadow-lg shadow-brand-100 hover:bg-brand-500 transition-all active:scale-95 group/btn">
            <svg class="h-4 w-4 transition-transform group-hover/btn:rotate-90" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            TAMBAH DESIGN
        </a>
    </div>
</div>

<!-- Designs Grid -->
<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 animate-in fade-in slide-in-from-bottom-4 duration-500">
    @forelse($designs as $design)
        <div class="group relative bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-brand-500/10 hover:-translate-y-1">
            <div class="aspect-square w-full overflow-hidden bg-slate-100 relative">
                @if($design->image_path)
                    <img src="{{ asset('storage/'.$design->image_path) }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" alt="{{ $design->name }}">
                @else
                    <div class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-400">
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                    </div>
                @endif
                
                <!-- Quick Actions Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/40 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col justify-end p-4">
                    <div class="flex flex-col gap-2 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-500">
                        <a href="{{ route('admin.designs.edit', $design) }}" class="inline-flex items-center justify-center rounded-lg bg-white px-3 py-2 text-[10px] font-black text-slate-900 hover:bg-brand-50 transition-colors shadow-lg">
                            KEMASKINI
                        </a>
                        <form method="post" action="{{ route('admin.designs.destroy', $design) }}" class="block">
                            @csrf @method('delete')
                            <button type="submit" class="w-full inline-flex items-center justify-center rounded-lg bg-rose-600/90 backdrop-blur-md px-3 py-2 text-[10px] font-black text-white hover:bg-rose-500 transition-colors shadow-lg" onclick="return confirm('Padam design ini?')">
                                PADAM
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="p-4">
                @if($design->category)
                    <div class="flex mb-1.5">
                        <span class="inline-flex items-center rounded-md bg-brand-50/80 px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-brand-700 ring-1 ring-inset ring-brand-200">
                            {{ $design->category->name }}
                        </span>
                    </div>
                @endif
                <h3 class="text-xs font-black text-slate-800 tracking-tight leading-tight group-hover:text-brand-600 transition-colors line-clamp-2 min-h-[2rem]">{{ $design->name }}</h3>
            </div>
        </div>
    @empty
        <div class="col-span-full py-20 text-center bg-white rounded-3xl ring-1 ring-slate-200 shadow-inner">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-4">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
            </div>
            <h3 class="text-xl font-black text-slate-900 mb-1 uppercase tracking-tight">Koleksi Masih Kosong</h3>
            <p class="text-xs font-bold text-slate-500 italic">Mula memuat naik kraf grafik anda.</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($designs->hasPages())
<div class="mt-8 px-2">
    {{ $designs->links() }}
</div>
@endif

@endsection




