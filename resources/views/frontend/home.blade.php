@extends('layouts.frontend')

@section('title', 'Sticker Mirrorcote Custom Termurah')

@section('content')
<!-- Hero Section -->
<div class="relative isolate overflow-hidden bg-white rounded-[2.5rem] mb-20 border border-slate-200">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top_right,#e0e7ff,transparent)] opacity-40"></div>
    <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
        <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-10 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
    </div>

    <div class="px-8 py-16 sm:px-12 sm:py-24 lg:px-20 flex flex-col lg:flex-row items-center gap-16">
        <div class="flex-1 text-center lg:text-left">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 mb-6 animate-bounce">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                </span>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-700">Cetakan Berkualiti Tinggi</span>
            </div>
            <h1 class="text-5xl font-black tracking-tight text-slate-900 sm:text-7xl leading-[1.1]">
                Sticker Produk <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">Terbaik & Termurah</span>
            </h1>
            <p class="mt-8 text-lg leading-8 text-slate-600 max-w-xl mx-auto lg:mx-0">
                Tingkatkan imej jenama anda dengan sticker Mirrorcote premium (Kilat/Glossy). Cetakan tajam, gam kuat, dan harga yang tidak masuk akal murahnya!
            </p>
            <div class="mt-12 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                <a href="{{ route('orders.create') }}" class="w-full sm:w-auto rounded-2xl bg-indigo-600 px-10 py-5 text-sm font-black text-white shadow-xl shadow-indigo-500/25 hover:bg-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all transform hover:scale-105 active:scale-95">
                    Tempah Sekarang
                </a>
                <a href="{{ route('orders.lookup-form') }}" class="w-full sm:w-auto rounded-2xl bg-white px-10 py-5 text-sm font-black text-slate-900 ring-1 ring-slate-200 hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    Semak Status
                </a>
            </div>

            <div class="mt-12 flex items-center justify-center lg:justify-start gap-8 grayscale opacity-60">
                <div class="flex flex-col items-center lg:items-start">
                    <span class="text-2xl font-black text-slate-900">100%</span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Puas Hati</span>
                </div>
                <div class="w-px h-8 bg-slate-200"></div>
                <div class="flex flex-col items-center lg:items-start">
                    <span class="text-2xl font-black text-slate-900">24 Jam</span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Siap Cetak</span>
                </div>
                <div class="w-px h-8 bg-slate-200"></div>
                <div class="flex flex-col items-center lg:items-start">
                    <span class="text-2xl font-black text-slate-900">Mirrorcote</span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Premium</span>
                </div>
            </div>
        </div>
        
        <div class="flex-1 w-full relative">
            <div class="relative rounded-3xl overflow-hidden shadow-2xl rotate-2 transition-transform hover:rotate-0 duration-700 border-8 border-white">
                <div class="aspect-[4/3] bg-indigo-50 flex items-center justify-center">
                    <!-- Placeholder or dynamic image -->
                    <div class="flex flex-col items-center text-indigo-300">
                        <svg class="w-20 h-20 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        <span class="text-sm font-bold uppercase tracking-widest">Premium Sticker Showcase</span>
                    </div>
                </div>
            </div>
            <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-2xl shadow-xl border border-slate-100 -rotate-3 animate-pulse">
                <div class="flex items-center gap-3">
                    <div class="bg-emerald-100 text-emerald-600 p-2 rounded-lg">
                         <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Stok Terhad!</p>
                        <p class="text-sm font-black text-slate-900">Slot printing hari ini tinggal 12!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-24">
    <div class="p-8 bg-white border border-slate-200 rounded-3xl transition-all hover:border-indigo-200 hover:shadow-xl hover:shadow-indigo-500/5 group">
        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-lg font-black text-slate-900 mb-2">Penyiapan Pantas</h3>
        <p class="text-sm text-slate-500 leading-relaxed">Kebanyakan order siap diproses dalam masa 24 jam selepas pengesahan pembayaran.</p>
    </div>
    <div class="p-8 bg-white border border-slate-200 rounded-3xl transition-all hover:border-emerald-200 hover:shadow-xl hover:shadow-emerald-500/5 group">
        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-all">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-lg font-black text-slate-900 mb-2">Harga Termurah</h3>
        <p class="text-sm text-slate-500 leading-relaxed">Kami menawarkan harga borong terus dari kilang tanpa mediator. Lebih banyak anda tempah, lebih murah!</p>
    </div>
    <div class="p-8 bg-white border border-slate-200 rounded-3xl transition-all hover:border-violet-200 hover:shadow-xl hover:shadow-violet-500/5 group">
        <div class="w-12 h-12 bg-violet-50 text-violet-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-violet-600 group-hover:text-white transition-all">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <h3 class="text-lg font-black text-slate-900 mb-2">Visual Premium</h3>
        <p class="text-sm text-slate-500 leading-relaxed">Mirrorcote glossy yang memberikan impak visual berkilat dan profesional pada produk anda.</p>
    </div>
</div>

<!-- Pricing Section -->
<section class="mb-24">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 text-center md:text-left px-4">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Kadar Harga Sticker</h2>
            <p class="text-slate-500 mt-2 font-medium">Bahan: <span class="text-indigo-600 font-bold uppercase tracking-widest text-xs">Mirrorcote Glossy Premium</span> (Siap Potong / Kiss Cut)</p>
        </div>
        <div class="hidden md:block">
            <div class="h-1 w-24 bg-indigo-600 rounded-full"></div>
        </div>
    </div>

    <div class="overflow-hidden bg-white shadow-sm ring-1 ring-slate-200 rounded-[2rem]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th scope="col" class="py-5 pl-8 pr-3 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Pilihan Saiz</th>
                        <th scope="col" class="px-6 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Dimensi (cm)</th>
                        <th scope="col" class="px-6 py-5 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Harga Seunit</th>
                        <th scope="col" class="px-6 py-5 text-right text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 pr-8">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($sizes as $size)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="whitespace-nowrap py-5 pl-8 pr-3 text-sm font-black text-slate-900">
                                {{ $size->name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-500 font-medium">
                                <span class="bg-slate-100 px-2.5 py-1 rounded-lg text-slate-600">
                                    {{ $size->width_cm && $size->height_cm ? $size->width_cm.' x '.$size->height_cm : '-' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-5">
                                <div class="text-sm font-black text-indigo-600 italic">RM {{ number_format($size->price, 2) }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-5 text-right pr-8">
                                @if($size->is_default)
                                    <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-indigo-700 ring-1 ring-inset ring-indigo-700/10">Default</span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-200 inline-block"></span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-16 text-center text-sm text-slate-400 italic">Senarai harga sedang dikemaskinikan. Sila semak semula nanti.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Designs Gallery -->
<div class="mb-24">
    <div class="text-center max-w-2xl mx-auto mb-16">
        <h2 class="text-3xl font-black text-slate-900 tracking-tight mb-4">Galeri Koleksi Design</h2>
        <p class="text-slate-500 text-sm leading-relaxed">Pilih daripada ratusan design sedia ada kami atau hantar design kustom anda sendiri tanpa sebarang caj tambahan!</p>
    </div>

    @foreach($categories as $category)
        <div class="mb-20 last:mb-0">
            <div class="flex items-center gap-6 mb-10">
                <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ $category->name }}</h3>
                <div class="h-[2px] flex-1 bg-gradient-to-r from-slate-200 to-transparent"></div>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $category->designs->count() }} Designs</span>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 sm:gap-8">
                @forelse($category->designs as $design)
                    <div class="group relative flex flex-col overflow-hidden rounded-[2rem] bg-white border border-slate-200 hover:border-indigo-400 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 transform hover:-translate-y-2">
                        <div class="aspect-square bg-slate-50 flex items-center justify-center overflow-hidden relative">
                            @if($design->image_path)
                                <img src="{{ asset('storage/'.$design->image_path) }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" alt="{{ $design->name }}">
                            @else
                                <div class="flex flex-col items-center text-slate-300">
                                    <svg class="h-12 w-12 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                    <span class="text-[10px] font-bold uppercase tracking-widest">Tiada Pratonton</span>
                                </div>
                            @endif
                            <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                                <p class="text-[10px] font-black text-white uppercase tracking-widest">{{ $category->name }}</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <h4 class="text-sm font-black text-slate-900 mb-1 line-clamp-1 group-hover:text-indigo-600 transition-colors">{{ $design->name }}</h4>
                            <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed mb-4 h-8">{{ $design->description ?: 'Penerangan design sedang dikemaskinikan.' }}</p>
                            <a href="{{ route('orders.create', ['design_id' => $design->id]) }}" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-50 px-4 py-2.5 text-xs font-black text-slate-700 hover:bg-indigo-600 hover:text-white transition-all ring-1 ring-slate-200 group-hover:ring-indigo-600 shadow-sm active:translate-y-0.5">
                                Pilih Design
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 rounded-3xl border-4 border-dashed border-slate-100 flex flex-col items-center justify-center text-slate-300">
                        <svg class="w-16 h-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                        </svg>
                        <p class="text-sm font-bold uppercase tracking-widest italic">Koleksi sedang dikemaskinikan</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

<!-- CTA Section -->
<div class="bg-slate-900 rounded-[2.5rem] p-12 md:p-20 text-center relative overflow-hidden shadow-2xl">
    <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 bg-indigo-600 opacity-20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-violet-600 opacity-20 rounded-full blur-3xl"></div>
    
    <div class="relative z-10 max-w-3xl mx-auto">
        <h2 class="text-4xl font-black text-white tracking-tight mb-8">Dah Ada Design Sendiri?</h2>
        <p class="text-indigo-200 text-lg mb-12">Jangan risau, kami menerima sebarang format file (PNG, JPG, PDF, AI). Hantar sekarang dan kami akan semak kualiti file anda secara percuma!</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
            <a href="{{ route('orders.create') }}" class="w-full sm:w-auto rounded-2xl bg-indigo-600 px-12 py-5 text-sm font-black text-white hover:bg-white hover:text-slate-900 transition-all shadow-xl shadow-indigo-600/20 active:scale-95">
                Mula Tempah Sekarang
            </a>
            <a href="https://wa.me/60123456789" target="_blank" class="w-full sm:w-auto rounded-2xl bg-white/5 px-12 py-5 text-sm font-black text-white ring-1 ring-white/10 hover:bg-white/10 transition-all">
                Hubungi Kami (WhatsApp)
            </a>
        </div>
    </div>
</div>
@endsection

