@extends('layouts.frontend')

@section('title', 'Print Sticker Mirrorcote | Sticker Termurah')

@section('content')

{{-- Hero Section --}}
<section class="relative overflow-hidden bg-gradient-to-br from-pink-50 via-white to-blue-50 pb-16 pt-8 lg:pt-12">
    {{-- Decorative blobs --}}
    <div class="absolute -right-20 top-20 h-72 w-72 rounded-full bg-pink-200/30 blur-3xl"></div>
    <div class="absolute -left-20 bottom-0 h-64 w-64 rounded-full bg-blue-200/30 blur-3xl"></div>

    <div class="relative mx-auto max-w-[1280px] px-4 lg:px-8">
        <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2">
            {{-- Left Content --}}
            <div class="relative z-10">
                <span class="inline-flex rounded-full bg-brand-600 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-white">
                    100% MIRRORCOTE PREMIUM
                </span>
                <h1 class="mt-5 text-4xl font-extrabold leading-[1.1] tracking-tight text-slate-900 sm:text-5xl lg:text-[3.5rem]">
                    PRINT STICKER<br>
                    <span class="text-brand-600">MIRRORCOTE</span>
                </h1>
                <p class="mt-4 text-lg font-bold text-slate-800">Berkilat, Tahan Lama, Warna Lebih Menarik</p>

                <ul class="mt-6 space-y-2.5">
                    <li class="flex items-center gap-2.5 text-sm text-slate-700">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-600 text-white">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        Kualiti cetakan premium
                    </li>
                    <li class="flex items-center gap-2.5 text-sm text-slate-700">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-600 text-white">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        Warna tajam & tidak mudah pudar
                    </li>
                    <li class="flex items-center gap-2.5 text-sm text-slate-700">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-600 text-white">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        Tahan air, calar & cuaca
                    </li>
                    <li class="flex items-center gap-2.5 text-sm text-slate-700">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-600 text-white">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        Sesuai untuk semua jenis penggunaan
                    </li>
                </ul>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="#pilih-design" class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-7 py-3.5 text-sm font-bold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700">
                        Pilih Design Sekarang
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    <a href="#hubungi-kami" class="inline-flex items-center gap-2 rounded-full border-2 border-slate-300 bg-white px-7 py-3.5 text-sm font-bold text-slate-700 transition hover:border-brand-300 hover:text-brand-600">
                        Hubungi Kami
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                    </a>
                </div>
            </div>

            {{-- Right Visual --}}
            <div class="relative flex items-center justify-center">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="h-72 w-72 rounded-full bg-gradient-to-br from-pink-200/40 to-blue-200/40 blur-2xl"></div>
                </div>
                {{-- Floating stickers composition --}}
                <div class="relative grid grid-cols-2 gap-4 p-4 sm:grid-cols-3">
                    @php
                        $heroImages = [
                            ['color' => 'from-amber-900 to-amber-700', 'text' => "D'BROWNIE\nPremium Quality", 'sub' => ''],
                            ['color' => 'from-pink-300 to-pink-200', 'text' => "Thank\nYou", 'sub' => ''],
                            ['color' => 'from-red-600 to-orange-500', 'text' => "SAMBAL\nNYET!", 'sub' => ''],
                            ['color' => 'from-cyan-100 to-white', 'text' => "Bubble\nTea", 'sub' => ''],
                            ['color' => 'from-amber-700 to-amber-600', 'text' => "COFFEE\nTIME", 'sub' => ''],
                            ['color' => 'from-blue-600 to-blue-500', 'text' => "Warna\nLebih Tajam", 'sub' => ''],
                        ];
                    @endphp
                    @foreach($heroImages as $index => $img)
                        <div class="group relative flex aspect-square w-28 items-center justify-center rounded-full bg-gradient-to-br {{ $img['color'] }} p-4 text-center text-white shadow-xl transition duration-500 hover:-translate-y-1 hover:scale-105 sm:w-32 lg:w-36 {{ $index % 2 === 0 ? 'sm:mt-4' : 'sm:-mt-2' }}">
                            <div class="text-xs font-extrabold leading-tight sm:text-sm">
                                {!! nl2br(e($img['text'])) !!}
                            </div>
                            @if($img['sub'])
                                <div class="absolute -bottom-1 text-[10px] font-semibold text-white/90">{{ $img['sub'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Features Bar --}}
<section class="mx-auto max-w-[1280px] px-4 pb-10 lg:px-8">
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-lg shadow-slate-200/50">
        <div class="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-5">
            @php
                $features = [
                    ['icon' => 'shield', 'title' => 'Kualiti Premium', 'desc' => 'Material mirrorcote berkualiti tinggi', 'color' => 'text-blue-600 bg-blue-50'],
                    ['icon' => 'droplet', 'title' => 'Tahan Lama', 'desc' => 'Tidak mudah pudar', 'color' => 'text-purple-600 bg-purple-50'],
                    ['icon' => 'palette', 'title' => 'Warna Menarik', 'desc' => 'Cetakan warna lebih hidup & jelas', 'color' => 'text-emerald-600 bg-emerald-50'],
                    ['icon' => 'truck', 'title' => 'Penghantaran Pantas', 'desc' => 'Proses cepat & pos laju ke seluruh Malaysia', 'color' => 'text-orange-600 bg-orange-50'],
                    ['icon' => 'heart', 'title' => '100% Kepuasan', 'desc' => 'Kami utamakan kualiti & servis terbaik', 'color' => 'text-rose-600 bg-rose-50'],
                ];
            @endphp
            @foreach($features as $f)
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $f['color'] }}">
                        @if($f['icon'] === 'shield')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                        @elseif($f['icon'] === 'droplet')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 11.25l-3-3m0 0l-3 3m3-3v7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($f['icon'] === 'palette')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.077-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597l-5.814 3.876a15.994 15.994 0 00-4.648 4.764m0 0a3 3 0 00-1.62 3.388m0 0a15.998 15.998 0 01-3.395 1.622m0 0a15.998 15.998 0 01-1.62 3.388m0 0a15.998 15.998 0 00-3.388 1.62m0 0a15.998 15.998 0 01-1.622 3.395"/></svg>
                        @elseif($f['icon'] === 'truck')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-17.25 4.5h1.125c.621 0 1.125-.504 1.125-1.125V14.25m0-10.5V6.375A1.125 1.125 0 003.375 5.25H15a1.125 1.125 0 011.125 1.125v8.625m-12 0h9.75m-9.75 0a1.125 1.125 0 00-1.125 1.125v1.5a1.125 1.125 0 001.125 1.125m0 0h1.5m-1.5 0a1.125 1.125 0 00-1.125 1.125v1.5a1.125 1.125 0 001.125 1.125m9.75-4.5H15a1.125 1.125 0 01-1.125-1.125v-1.5a1.125 1.125 0 011.125-1.125m0 0h1.5m-1.5 0a1.125 1.125 0 00-1.125-1.125v-1.5a1.125 1.125 0 011.125-1.125m0 0h1.5"/></svg>
                        @elseif($f['icon'] === 'heart')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">{{ $f['title'] }}</p>
                        <p class="mt-0.5 text-xs leading-relaxed text-slate-500">{{ $f['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Pilih Design Section --}}
<section id="pilih-design" class="bg-slate-50 py-16 lg:py-20">
    <div class="mx-auto max-w-[1280px] px-4 lg:px-8">
        <div class="text-center">
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">PILIH DESIGN</h2>
            <p class="mt-2 text-sm text-slate-500">Pilih design yang anda suka atau hantar design sendiri!</p>
        </div>

        {{-- Tabs --}}
        <div class="mt-8 flex flex-wrap items-center justify-center gap-2" x-data="{ activeTab: 'all' }">
            @php
                $tabs = [
                    ['id' => 'all', 'label' => 'Semua'],
                    ['id' => 'food', 'label' => 'Makanan & Minuman'],
                    ['id' => 'beauty', 'label' => 'Kecantikan'],
                    ['id' => 'product', 'label' => 'Produk'],
                    ['id' => 'thankyou', 'label' => 'Terima Kasih'],
                    ['id' => 'business', 'label' => 'Business'],
                    ['id' => 'others', 'label' => 'Lain-lain'],
                ];
            @endphp
            @foreach($tabs as $tab)
                <button
                    @click="activeTab = '{{ $tab['id'] }}'"
                    :class="activeTab === '{{ $tab['id'] }}' ? 'bg-brand-600 text-white shadow-md shadow-brand-600/20' : 'bg-white text-slate-600 hover:text-brand-600 border border-slate-200'"
                    class="rounded-full px-4 py-2 text-xs font-semibold transition"
                >
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>

        {{-- Design Cards --}}
        <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6" x-data="{ activeTab: 'all' }">
            @forelse($categories as $category)
                @foreach($category->designs->take(6) as $design)
                    <a href="{{ route('orders.create', ['design_id' => $design->id]) }}" class="group block overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="aspect-square overflow-hidden bg-slate-100">
                            @if($design->image_path)
                                <img src="{{ asset('storage/' . $design->image_path) }}" alt="{{ $design->name }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                            @else
                                <div class="flex h-full items-center justify-center">
                                    <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18.75 3.75H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 18V9a2.25 2.25 0 00-2.25-2.25z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-3">
                            <p class="truncate text-xs font-semibold text-slate-800">{{ $design->name }}</p>
                            <p class="mt-0.5 text-[10px] text-slate-500">{{ $category->name }}</p>
                        </div>
                    </a>
                @endforeach
            @empty
                @for($i = 0; $i < 6; $i++)
                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="aspect-square bg-gradient-to-br from-pink-100 to-purple-100 flex items-center justify-center">
                            <div class="text-center text-slate-400">
                                <svg class="mx-auto h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18.75 3.75H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 18V9a2.25 2.25 0 00-2.25-2.25z"/></svg>
                                <p class="mt-2 text-xs">Design {{ $i + 1 }}</p>
                            </div>
                        </div>
                        <div class="p-3">
                            <p class="text-xs font-semibold text-slate-800">Sample Design {{ $i + 1 }}</p>
                            <p class="mt-0.5 text-[10px] text-slate-500">Kategori</p>
                        </div>
                    </div>
                @endfor
            @endforelse
        </div>

        <div class="mt-10 text-center">
            <a href="#pilih-design" class="inline-flex items-center gap-2 rounded-full border-2 border-brand-600 px-6 py-3 text-sm font-bold text-brand-600 transition hover:bg-brand-600 hover:text-white">
                Lihat Lebih Banyak Design
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- Testimoni Section --}}
<section id="testimoni" class="bg-white py-16 lg:py-20">
    <div class="mx-auto max-w-[1280px] px-4 lg:px-8">
        <h2 class="text-center text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">TESTIMONI PELANGGAN</h2>

        <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-3">
            @php
                $testimonials = [
                    [
                        'name' => 'Nurul Atiqah',
                        'role' => '(Perniagaan Kek)',
                        'text' => 'Sticker sangat berkualiti! Warna sangat cantik & berkilat. Customer saya suka sangat.',
                        'image' => 'NA',
                        'color' => 'bg-amber-100 text-amber-700',
                    ],
                    [
                        'name' => 'Hafiz Rahman',
                        'role' => '(Produk Sambal)',
                        'text' => 'Servis cepat, respon pantas dan hasil memuaskan. Akan repeat order lagi!',
                        'image' => 'HR',
                        'color' => 'bg-blue-100 text-blue-700',
                    ],
                    [
                        'name' => 'Siti Aisyah',
                        'role' => '(Produk Kecantikan)',
                        'text' => 'Sticker tahan air & tak mudah luntur. Sangat sesuai untuk produk kami.',
                        'image' => 'SA',
                        'color' => 'bg-rose-100 text-rose-700',
                    ],
                ];
            @endphp
            @foreach($testimonials as $t)
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-lg shadow-slate-200/40 transition hover:-translate-y-1">
                    <div class="flex items-center gap-0.5 text-amber-400">
                        @for($s = 0; $s < 5; $s++)
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600">"{{ $t['text'] }}"</p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $t['color'] }} text-xs font-bold">
                            {{ $t['image'] }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">- {{ $t['name'] }}</p>
                            <p class="text-xs text-slate-500">{{ $t['role'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <a href="#testimoni" class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brand-600/20 transition hover:bg-brand-700">
                Lihat Lebih Banyak Testimoni
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- Cara Tempah Section --}}
<section id="cara-tempah" class="bg-gradient-to-b from-sky-50 to-white py-16 lg:py-20">
    <div class="mx-auto max-w-[1280px] px-4 lg:px-8">
        <h2 class="text-center text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">CARA TEMPAH</h2>

        <div class="mt-12 grid grid-cols-1 items-start gap-8 lg:grid-cols-4">
            {{-- Steps --}}
            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-lg shadow-slate-200/40">
                    <div class="grid grid-cols-2 gap-6 md:grid-cols-5">
                        @php
                            $steps = [
                                ['num' => '01', 'title' => 'Pilih Design', 'desc' => 'Pilih design yang anda suka', 'icon' => 'image', 'color' => 'text-rose-500 bg-rose-50'],
                                ['num' => '02', 'title' => 'Pilih Kuantiti', 'desc' => 'Pilih saiz & kuantiti yang diperlukan', 'icon' => 'cart', 'color' => 'text-blue-500 bg-blue-50'],
                                ['num' => '03', 'title' => 'Sahkan Order', 'desc' => 'Semak maklumat & buat bayaran', 'icon' => 'clipboard', 'color' => 'text-purple-500 bg-purple-50'],
                                ['num' => '04', 'title' => 'Proses Cetakan', 'desc' => 'Kami proses & cetak dengan kualiti terbaik', 'icon' => 'printer', 'color' => 'text-emerald-500 bg-emerald-50'],
                                ['num' => '05', 'title' => 'Penghantaran', 'desc' => 'Pos laju ke seluruh Malaysia', 'icon' => 'truck', 'color' => 'text-orange-500 bg-orange-50'],
                            ];
                        @endphp
                        @foreach($steps as $step)
                            <div class="relative text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl {{ $step['color'] }}">
                                    @if($step['icon'] === 'image')
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18.75 3.75H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 18V9a2.25 2.25 0 00-2.25-2.25z"/></svg>
                                    @elseif($step['icon'] === 'cart')
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                                    @elseif($step['icon'] === 'clipboard')
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V19.5a2.25 2.25 0 002.25 2.25h.75m0-3H12"/></svg>
                                    @elseif($step['icon'] === 'printer')
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.227 1.536c.09.606-.36 1.14-.96 1.14H7.26c-.6 0-1.05-.534-.96-1.14L6.48 18m10.98 0l-.84-3.36a1.125 1.125 0 00-1.092-.84H8.472a1.125 1.125 0 00-1.092.84l-.84 3.36m13.44 0h-3.84m-7.68 0H3.6M12 8.25V3.75m0 0l-1.5 1.5M12 3.75l1.5 1.5"/></svg>
                                    @elseif($step['icon'] === 'truck')
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-17.25 4.5h1.125c.621 0 1.125-.504 1.125-1.125V14.25m0-10.5V6.375A1.125 1.125 0 003.375 5.25H15a1.125 1.125 0 011.125 1.125v8.625m-12 0h9.75m-9.75 0a1.125 1.125 0 00-1.125 1.125v1.5a1.125 1.125 0 001.125 1.125m0 0h1.5m-1.5 0a1.125 1.125 0 00-1.125 1.125v1.5a1.125 1.125 0 001.125 1.125m9.75-4.5H15a1.125 1.125 0 01-1.125-1.125v-1.5a1.125 1.125 0 011.125-1.125m0 0h1.5m-1.5 0a1.125 1.125 0 00-1.125-1.125v-1.5a1.125 1.125 0 011.125-1.125m0 0h1.5"/></svg>
                                    @endif
                                </div>
                                <p class="mt-3 text-xs font-bold text-brand-600">{{ $step['num'] }}</p>
                                <p class="mt-1 text-sm font-bold text-slate-900">{{ $step['title'] }}</p>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $step['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Design Sendiri Box --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-lg shadow-slate-200/40">
                <h3 class="text-lg font-bold text-slate-900">Design Sendiri?</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">Hantar design anda sendiri, kami boleh bantu cetak!</p>
                <a href="https://wa.me/601169409606" target="_blank" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-full border-2 border-brand-600 px-5 py-3 text-sm font-bold text-brand-600 transition hover:bg-brand-600 hover:text-white">
                    Hantar Design
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                </a>
                <div class="mt-4 flex justify-center">
                    <div class="rounded-xl bg-slate-100 p-3">
                        <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Contact Bar --}}
<section id="hubungi-kami" class="bg-gradient-to-r from-brand-600 to-fuchsia-700 py-8">
    <div class="mx-auto max-w-[1280px] px-4 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white/20 text-white">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.82.49 3.53 1.35 5.01L2 22l5.09-1.33A9.961 9.961 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm0 18c-1.64 0-3.17-.49-4.46-1.33l-.32-.19-2.98.78.8-2.9-.21-.33A7.96 7.96 0 014 12c0-4.41 3.59-8 8-8s8 3.59 8 8-3.59 8-8 8zm4.41-5.89c-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18-.71-.63-1.19-1.41-1.33-1.65-.14-.24-.02-.37.1-.49.1-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.2-.46-.4-.4-.54-.41-.14 0-.3-.02-.46-.02-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.59 4.12 3.63.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.46-.07 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-white">Ada soalan? Kami sedia membantu!</p>
                    <p class="text-sm text-white/80">Hubungi kami sekarang untuk maklumat lanjut.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="https://wa.me/601169409606" target="_blank" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-brand-700 transition hover:bg-brand-50">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.82.49 3.53 1.35 5.01L2 22l5.09-1.33A9.961 9.961 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2z"/></svg>
                    011-69409606
                </a>
                <a href="mailto:stickertermurah@gmail.com" class="inline-flex items-center gap-2 rounded-full bg-white/10 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-white/20">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Email Kami
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
