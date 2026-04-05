@extends('layouts.admin')

@section('title', 'Koleksi Design Grafik')

@section('content')
<!-- Page Header -->
<div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
        <h2 class="text-3xl font-black text-slate-900 tracking-tight mb-2 uppercase">Koleksi Design Grafik</h2>
        <p class="text-slate-500 font-medium tracking-wide">Uruskan pustaka design yang boleh dipilih oleh pelanggan dengan visual yang menarik.</p>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.designs.create') }}" class="inline-flex items-center justify-center gap-3 rounded-2xl bg-brand-600 px-8 py-4 text-sm font-black text-white shadow-xl shadow-brand-100 hover:bg-brand-500 transition-all active:scale-95 group/btn">
            <svg class="h-5 w-5 transition-transform group-hover/btn:rotate-90" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            TAMBAH DESIGN BAHARU
        </a>
    </div>
</div>

<!-- Designs Grid -->
<div class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 animate-in fade-in slide-in-from-bottom-8 duration-700">
    @forelse($designs as $design)
        <div class="group relative bg-white rounded-[2rem] shadow-sm ring-1 ring-slate-200 overflow-hidden transition-all duration-500 hover:shadow-2xl hover:shadow-brand-500/10 hover:-translate-y-2">
            <div class="aspect-square w-full overflow-hidden bg-slate-100 relative">
                @if($design->image_path)
                    <img src="{{ asset('storage/'.$design->image_path) }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" alt="{{ $design->name }}">
                @else
                    <div class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-400">
                        <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                    </div>
                @endif
                
                <!-- Quick Actions Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/40 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col justify-end p-6">
                    <div class="flex flex-col gap-3 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                        <a href="{{ route('admin.designs.edit', $design) }}" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-3 text-xs font-black text-slate-900 hover:bg-brand-50 transition-colors shadow-lg">
                            KEMASKINI DESIGN
                        </a>
                        <form method="post" action="{{ route('admin.designs.destroy', $design) }}" class="block">
                            @csrf @method('delete')
                            <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl bg-rose-600/90 backdrop-blur-md px-4 py-3 text-xs font-black text-white hover:bg-rose-500 transition-colors shadow-lg" onclick="return confirm('Padam design ini? Tindakan ini tidak boleh diundur.')">
                                PADAM DESIGN
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                @if($design->category)
                    <div class="flex mb-3">
                        <span class="inline-flex items-center rounded-lg bg-brand-50/80 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-brand-700 ring-1 ring-inset ring-brand-200">
                            {{ $design->category->name }}
                        </span>
                    </div>
                @endif
                <h3 class="text-sm font-black text-slate-800 tracking-tight leading-snug group-hover:text-brand-600 transition-colors line-clamp-2 min-h-[2.5rem]">{{ $design->name }}</h3>
            </div>
        </div>
    @empty
        <div class="col-span-full py-32 text-center bg-white rounded-[3rem] ring-1 ring-slate-200 shadow-inner">
            <div class="inline-flex h-24 w-24 items-center justify-center rounded-[2rem] bg-slate-100 text-slate-400 mb-6">
                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
            </div>
            <h3 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tight">Koleksi Masih Kosong</h3>
            <p class="text-sm font-bold text-slate-500 italic">Mula memuat naik kraf grafik anda untuk tatapan pelanggan.</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($designs->hasPages())
<div class="mt-12 px-2">
    {{ $designs->links() }}
</div>
@endif

@endsection



