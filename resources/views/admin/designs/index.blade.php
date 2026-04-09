@extends('layouts.admin')

@section('title', 'Koleksi Design Grafik')

@section('content')
<!-- Page Header -->
<div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6 border-b-2 border-slate-100 pb-8">
    <div>
        <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase leading-none mb-2">Pustaka Grafik</h2>
        <p class="text-[11px] font-black text-slate-400 tracking-widest uppercase">Uruskan pustaka design yang boleh dipilih oleh pelanggan.</p>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.designs.create') }}" class="flat-btn-primary !h-12 px-8 text-[11px] font-black uppercase tracking-widest flex items-center gap-3">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            TAMBAH DESIGN BARU
        </a>
    </div>
</div>

<!-- Designs Grid -->
<div class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 animate-in fade-in duration-500">
    @forelse($designs as $design)
        <div class="group relative bg-white border-2 border-slate-100 hover:border-slate-900 transition-all duration-300 rounded-sm overflow-hidden">
            <div class="aspect-square w-full overflow-hidden bg-slate-50 relative border-b-2 border-slate-100 group-hover:border-slate-900 transition-all">
                @if($design->image_path)
                    <img src="{{ asset('storage/'.$design->image_path) }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" alt="{{ $design->name }}">
                @else
                    <div class="flex h-full w-full items-center justify-center text-slate-200">
                        <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                    </div>
                @endif
                
                <!-- Action Overlay (Solid Flat Style) -->
                <div class="absolute inset-x-0 bottom-0 bg-slate-900 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300 z-10">
                    <div class="flex flex-col">
                        <a href="{{ route('admin.designs.edit', $design) }}" class="w-full py-3 text-center text-[10px] font-black text-white hover:bg-slate-800 transition-colors uppercase tracking-[0.2em] border-b border-slate-800">
                            KEMASKINI
                        </a>
                        <form method="post" action="{{ route('admin.designs.destroy', $design) }}" class="block">
                            @csrf @method('delete')
                            <button type="submit" class="w-full py-3 text-center text-[10px] font-black text-rose-500 hover:bg-rose-500 hover:text-white transition-all uppercase tracking-[0.2em]" onclick="return confirm('Padam design ini?')">
                                PADAM
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="p-4 bg-white">
                @if($design->category)
                    <div class="mb-2">
                        <span class="inline-block text-[9px] font-black uppercase tracking-[0.25em] text-brand italic">
                            {{ $design->category->name }}
                        </span>
                    </div>
                @endif
                <h3 class="text-[11px] font-black text-slate-900 tracking-tight leading-tight uppercase line-clamp-2 min-h-[2.5rem]">{{ $design->name }}</h3>
            </div>
        </div>
    @empty
        <div class="col-span-full flat-card !py-24 flex flex-col items-center justify-center text-center">
            <div class="h-20 w-20 rounded-sm bg-slate-50 border-2 border-slate-100 flex items-center justify-center text-slate-200 mb-6">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
            </div>
            <h3 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tighter italic">KOLEKSI MASIH KOSONG</h3>
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest italic">Mula memuat naik kraf grafik anda.</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($designs->hasPages())
<div class="mt-12">
    {{ $designs->links() }}
</div>
@endif

@endsection




