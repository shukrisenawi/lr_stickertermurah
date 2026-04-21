@extends('layouts.frontend')

@section('title', 'Sticker Termurah | Professional Printing Services')

@php
    $categoryCards = collect([
        [
            'title' => 'Sticker Label',
            'description' => 'Sesuai untuk produk makanan, kosmetik, dan pembungkusan yang mahu nampak lebih premium.',
            'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDxdDkeLmaciUAPqZR45bGEgslJO3HM1UcPoLzxN8JBZYPKugL-Tx8m_WB4KF9wwHflzXPBfSDRoBJW24YoZByqMFbsdCOJM3XZV1Z9r7WvqSpR4crUZEY9GvE7P_vQt8dTSkH_cmTtueasEt3TEn3HEpuGUqszsYyQzo-DaT1F0jnYpmcp97F5u83CBuntYNHhlOqfacheSc-ijSRY3UQQvmwJD24M0ZaiiN2BBjGDHf3RASiNXcmtsiRGP2qSMcQfKAmZtK_83hK1',
            'tone' => 'primary',
        ],
        [
            'title' => 'Banner & Bunting',
            'description' => 'Promosi besar untuk event, booth, kedai, dan kempen jualan yang perlukan impak segera.',
            'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCBWv47JNXiOCrmRVulkhTurJz5qQAzXO6yrun6gA9sIYbabRHmRrKUKXFzIJ6UiSBwssXnklMFkJw2hzYaHw1ms_-DsElbkV0-8py1REAa6AGh4bW9F8YzXSk5CsC9Z_PPpo3qyNf2Pb6LbqDkFekSO7PY6RN3t9xQmGVCXce7uctUgjYUj64hu755HURofG1dCNBi8aCENJLT4dUcStRMWYmU6PsJAsoD3s4cBMltQcjJ6-aafUdIOd0VgEJtWhOAMxhqwPNh8_Dd',
            'tone' => 'neutral',
        ],
        [
            'title' => 'Business Card',
            'description' => 'Kad nama berkualiti tinggi untuk jenama yang mahu tampil kemas dan meyakinkan.',
            'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDM0XRqBUqtgFmxecLhLzUiJ0-tC0sZMHEcZabgdvIdzLnEPUePOevZQh6quW1brjQiVHuiy7iYUyo-8IxOgAHxvPQ-XlySx2u1-kA17irIZUeCeVsflnSykAjQRSn5ieESDgeUsn_Q0jwWR7axG7ZEfogsakwu_INNWeN8P1DZRBR-LHvmT7p67xivbLwwIKgPb7ELPmtN8p7LJhLMywx-d_cwEwx3sbiiix3YXJHov5pU_Q-8XcFxMQTjIeYSNuoGA2SHyMiq-xtn',
            'tone' => 'neutral',
        ],
        [
            'title' => 'Packaging Design',
            'description' => 'Kotak dan pembungkusan custom untuk naikkan identiti produk dari rak ke tangan pelanggan.',
            'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuChR0E-1HXbHDayZO71tkEGZBPjJcOvSfOxECw4G1RL6J1t27Dt7YIP7aZ923jlzMi_aHQQxE42paOZW8DrW566Kh6IbiW0Q0vZ7-xq3pP2UA4UFcKUlnlT63UQAV8-3ChVECQc0qYR6GUaGfZRpZVHkQ4aRTPe825pHUY9C37a4ZfcgdNKH2_4plfBRU6AX7CDPx4oQpjnN7RLKC8W_BI3IzR27j9iduzRBQJFJ6kOJAyTa3f5eKp7BqVwmwDqWjBjNQ4TYikmjSOi',
            'tone' => 'tertiary',
        ],
    ]);
    $featuredCategories = $categories->take(4)->values();
@endphp

@section('content')
<section class="relative overflow-hidden bg-[#f6f6f9] pb-16 pt-10 sm:pt-14 lg:pb-24 lg:pt-16">
    <div class="absolute inset-x-0 top-0 h-[42rem] bg-[radial-gradient(circle_at_top_right,_rgba(178,0,105,0.16),_transparent_34%),radial-gradient(circle_at_bottom_left,_rgba(253,212,0,0.18),_transparent_26%)]"></div>
    <div class="mx-auto grid max-w-7xl grid-cols-1 items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
        <div class="relative z-10">
            <span class="inline-flex rounded-full bg-[#ff6dae]/20 px-4 py-2 text-sm font-extrabold uppercase tracking-[0.18em] text-[#7a0047]">
                Premium Printing Services
            </span>
            <h1 class="mt-6 text-5xl font-extrabold leading-[1.02] tracking-[-0.05em] text-[#2d2f31] sm:text-6xl lg:text-7xl">
                Cetak Sticker &
                <span class="block text-[#b20069]">Produk Rekaan</span>
                Harga Termurah
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-8 text-slate-600">
                Kualiti premium dengan penghantaran pantas ke seluruh Malaysia. Sesuai untuk perniagaan kecil, korporat, dan penggunaan peribadi yang perlukan cetakan kemas tanpa drama.
            </p>
            <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                <a href="{{ auth()->check() ? route('orders.create') : route('member.register') }}" class="inline-flex items-center justify-center rounded-2xl bg-[#fdd400] px-8 py-4 text-base font-black text-[#594a00] shadow-xl shadow-[#fdd400]/25 transition-all hover:-translate-y-0.5 hover:bg-[#edc600]">
                    Start Order
                </a>
                <a href="#kategori" class="inline-flex items-center justify-center rounded-2xl border-2 border-[#dbdde1] bg-white px-8 py-4 text-base font-extrabold text-[#2d2f31] transition-all hover:bg-[#e7e8ec]">
                    Browse Catalog
                </a>
            </div>
            <div class="mt-12 grid max-w-xl grid-cols-3 gap-3 rounded-[2rem] border border-white/70 bg-white/80 p-4 shadow-[0_30px_70px_-40px_rgba(45,47,49,0.45)] backdrop-blur">
                <div class="rounded-[1.4rem] bg-[#f6f6f9] p-4">
                    <p class="text-2xl font-black text-[#b20069]">24H</p>
                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.22em] text-slate-500">Fast Turnaround</p>
                </div>
                <div class="rounded-[1.4rem] bg-[#f6f6f9] p-4">
                    <p class="text-2xl font-black text-[#4c4fb7]">{{ $categories->count() ?: '4+' }}</p>
                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.22em] text-slate-500">Kategori Aktif</p>
                </div>
                <div class="rounded-[1.4rem] bg-[#f6f6f9] p-4">
                    <p class="text-2xl font-black text-[#6d5a00]">{{ $sizes->count() ?: '10+' }}</p>
                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.22em] text-slate-500">Saiz Pilihan</p>
                </div>
            </div>
        </div>

        <div class="relative lg:block">
            <div class="absolute -right-10 -top-8 h-72 w-72 rounded-full bg-[#ff6dae]/20 blur-3xl"></div>
            <div class="absolute -bottom-8 -left-10 h-72 w-72 rounded-full bg-[#fdd400]/20 blur-3xl"></div>
            <div class="relative overflow-hidden rounded-[2rem] shadow-[0_50px_100px_-35px_rgba(45,47,49,0.45)] transition-transform duration-700 hover:rotate-0 lg:rotate-3">
                <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAOKnxb7BOLUylXyWgO9xpyoWMUhqQqzAvCmBirmRSiLy0vWyIEv2u35AbgBVyfzNPCkUHLakQCIaacNbfRgMQgmeMkZqXC82HeJsMK4Bkc5UmOuIscHjTgAssbij8b4utp74X2nzvAdBBesRb9r9O4u5betQADii3GThEkjP1b-UHnsgPpjjpUvFHtNQnnQ9XYLmNjSHT2q9vMfIlPAwBlA6IuLftkkR6yaoXjipi-JmFIXEO2vLIUCDmIvwqFEm_W2fUSIz64GJlo" alt="Premium Stickers" class="h-[32rem] w-full object-cover sm:h-[36rem]">
                <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-black/10 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 flex items-end justify-between gap-4 p-6 text-white sm:p-8">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-white/70">Hot Seller</p>
                        <p class="mt-2 text-2xl font-black tracking-tight">Sticker label, logo cut, die cut, packaging</p>
                    </div>
                    <div class="hidden rounded-2xl bg-white/15 px-4 py-3 backdrop-blur sm:block">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-white/70">Malaysia Wide</p>
                        <p class="text-lg font-black">Courier Ready</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="kategori" class="bg-[#f0f0f4] py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-16 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-4xl font-extrabold tracking-[-0.04em] text-[#2d2f31]">Pilihan Design Mengikut Kategori</h2>
                <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">Pelbagai pilihan produk percetakan mengikut keperluan anda. Saya padankan seksyen ini dengan gaya bento dari design rujukan, sambil kekalkan kategori sebenar dari database.</p>
            </div>
            <a href="{{ auth()->check() ? route('orders.create') : route('member.register') }}" class="inline-flex items-center gap-2 self-start rounded-2xl bg-white px-5 py-3 text-sm font-extrabold text-[#b20069] shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-lg">
                Terus Tempah
                <span class="material-symbols-outlined text-lg">arrow_forward</span>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
            <div class="group relative overflow-hidden rounded-[2rem] bg-white p-4 shadow-sm transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_35px_70px_-40px_rgba(178,0,105,0.5)] md:col-span-2 md:row-span-2">
                <div class="mb-6 aspect-square overflow-hidden rounded-[1.5rem]">
                    <img src="{{ $categoryCards[0]['image'] }}" alt="{{ $categoryCards[0]['title'] }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                </div>
                <div class="px-2 pb-2">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500">{{ optional($featuredCategories->get(0))->name ?? 'Best Seller' }}</p>
                    <h3 class="mt-2 text-2xl font-extrabold text-[#2d2f31]">{{ $categoryCards[0]['title'] }}</h3>
                    <p class="mt-3 max-w-lg text-sm leading-7 text-slate-600">{{ $categoryCards[0]['description'] }}</p>
                    <a href="{{ auth()->check() ? route('orders.create') : route('member.register') }}" class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-[#b20069] px-6 py-4 text-sm font-black text-white transition hover:bg-[#9d005c]">
                        View Designs
                    </a>
                </div>
            </div>

            @foreach($categoryCards->slice(1, 2) as $card)
                <div class="group rounded-[2rem] bg-white p-4 shadow-sm transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_35px_70px_-42px_rgba(45,47,49,0.45)]">
                    <div class="mb-6 aspect-video overflow-hidden rounded-[1.5rem]">
                        <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </div>
                    <div class="px-2 pb-2">
                        <h3 class="text-xl font-extrabold text-[#2d2f31]">{{ $card['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $card['description'] }}</p>
                        <a href="{{ auth()->check() ? route('orders.create') : route('member.register') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-black text-[#b20069] transition-all group-hover:gap-4">
                            View Designs
                            <span class="material-symbols-outlined text-lg">arrow_forward</span>
                        </a>
                    </div>
                </div>
            @endforeach

            <div class="group flex flex-col gap-6 rounded-[2rem] bg-white p-4 shadow-sm transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_35px_70px_-42px_rgba(76,79,183,0.45)] md:col-span-2 md:flex-row">
                <div class="overflow-hidden rounded-[1.5rem] md:w-1/2">
                    <img src="{{ $categoryCards[3]['image'] }}" alt="{{ $categoryCards[3]['title'] }}" class="h-full min-h-64 w-full object-cover transition-transform duration-700 group-hover:scale-105">
                </div>
                <div class="flex flex-1 flex-col justify-center px-2 py-4">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#4c4fb7]">{{ optional($featuredCategories->get(1))->name ?? 'Creative Packaging' }}</p>
                    <h3 class="mt-2 text-2xl font-extrabold text-[#2d2f31]">{{ $categoryCards[3]['title'] }}</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $categoryCards[3]['description'] }}</p>
                    <a href="{{ auth()->check() ? route('orders.create') : route('member.register') }}" class="mt-6 inline-flex self-start rounded-2xl bg-[#4c4fb7] px-6 py-3 text-sm font-black text-white transition hover:bg-[#4042aa]">
                        Explore More
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bg-[#f6f6f9] py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-4xl font-extrabold tracking-[-0.04em] text-[#2d2f31]">Kadar Harga & Saiz Popular</h2>
                <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Jadual harga sedia ada saya kekalkan, tetapi dibentangkan semula dengan rasa lebih editorial dan mudah scan.</p>
            </div>
            <div class="rounded-full bg-white px-4 py-2 text-xs font-extrabold uppercase tracking-[0.2em] text-[#6d5a00] shadow-sm ring-1 ring-slate-200">
                Mirrorcote Glossy
            </div>
        </div>

        <div class="overflow-hidden rounded-[2rem] bg-white shadow-[0_32px_70px_-45px_rgba(45,47,49,0.45)] ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-[#f6f6f9]">
                        <tr>
                            <th class="px-6 py-5 text-left text-[11px] font-extrabold uppercase tracking-[0.22em] text-slate-500">Pilihan Saiz</th>
                            <th class="px-6 py-5 text-left text-[11px] font-extrabold uppercase tracking-[0.22em] text-slate-500">Dimensi</th>
                            <th class="px-6 py-5 text-left text-[11px] font-extrabold uppercase tracking-[0.22em] text-slate-500">Harga</th>
                            <th class="px-6 py-5 text-right text-[11px] font-extrabold uppercase tracking-[0.22em] text-slate-500">Label</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($sizes as $size)
                            <tr class="transition hover:bg-[#fbfbfd]">
                                <td class="px-6 py-5 text-sm font-extrabold text-[#2d2f31]">{{ $size->name }}</td>
                                <td class="px-6 py-5 text-sm font-medium text-slate-600">{{ $size->width_cm && $size->height_cm ? $size->width_cm . ' x ' . $size->height_cm . ' cm' : '-' }}</td>
                                <td class="px-6 py-5 text-sm font-black text-[#b20069]">RM {{ number_format($size->price, 2) }}</td>
                                <td class="px-6 py-5 text-right">
                                    @if($size->is_default)
                                        <span class="inline-flex rounded-full bg-[#ff6dae]/20 px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.2em] text-[#7a0047]">Popular</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.2em] text-slate-500">Ready</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center text-sm font-medium italic text-slate-500">Senarai harga sedang dikemaskini. Sila semak semula sebentar lagi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<section id="custom-order" class="bg-[#f6f6f9] py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-[2.5rem] bg-[linear-gradient(135deg,rgba(255,109,174,0.14),rgba(166,169,255,0.2))] p-8 shadow-[0_40px_90px_-50px_rgba(45,47,49,0.45)] sm:p-12 lg:p-16">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                <div>
                    <h2 class="text-4xl font-extrabold leading-tight tracking-[-0.04em] text-[#2d2f31] sm:text-5xl">Tempahan Design Custom</h2>
                    <p class="mt-6 max-w-xl text-lg leading-8 text-slate-600">
                        Tiada design? Jangan risau. Pereka grafik kami sedia bantu dari idea awal sampai fail akhir yang betul-betul sedia cetak.
                    </p>
                    <div class="mt-8 space-y-4">
                        <div class="flex items-center gap-4 text-sm font-bold text-[#2d2f31]">
                            <span class="material-symbols-outlined rounded-full bg-[#b20069] p-1 text-base text-white">check</span>
                            Sesi konsultasi percuma sebelum mula kerja
                        </div>
                        <div class="flex items-center gap-4 text-sm font-bold text-[#2d2f31]">
                            <span class="material-symbols-outlined rounded-full bg-[#b20069] p-1 text-base text-white">check</span>
                            Hingga 3 kali semakan design
                        </div>
                        <div class="flex items-center gap-4 text-sm font-bold text-[#2d2f31]">
                            <span class="material-symbols-outlined rounded-full bg-[#b20069] p-1 text-base text-white">check</span>
                            Fail high resolution untuk kegunaan cetak
                        </div>
                    </div>

                    <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-[1.75rem] bg-white/70 p-5 backdrop-blur">
                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-slate-500">Best untuk</p>
                            <p class="mt-2 text-lg font-extrabold text-[#2d2f31]">Produk baharu, rebranding, launch event</p>
                        </div>
                        <div class="rounded-[1.75rem] bg-white/70 p-5 backdrop-blur">
                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-slate-500">Respons pantas</p>
                            <p class="mt-2 text-lg font-extrabold text-[#2d2f31]">Balasan awal melalui WhatsApp atau emel</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[2rem] bg-white p-8 shadow-[0_30px_80px_-48px_rgba(45,47,49,0.5)] sm:p-10">
                    <form class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-500">Nama Penuh</label>
                                <input type="text" placeholder="John Doe" class="w-full rounded-2xl border-0 bg-[#f0f0f4] px-5 py-4 text-sm text-[#2d2f31] ring-1 ring-transparent transition focus:ring-2 focus:ring-[#b20069]">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-500">Emel</label>
                                <input type="email" placeholder="hello@domain.com" class="w-full rounded-2xl border-0 bg-[#f0f0f4] px-5 py-4 text-sm text-[#2d2f31] ring-1 ring-transparent transition focus:ring-2 focus:ring-[#b20069]">
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-500">Upload Draft / Inspirasi</label>
                            <div class="rounded-[1.5rem] border-2 border-dashed border-slate-300 bg-[#fafafd] px-6 py-10 text-center transition hover:border-[#b20069] hover:bg-[#fff7fb]">
                                <span class="material-symbols-outlined text-4xl text-slate-400">cloud_upload</span>
                                <p class="mt-3 text-sm font-medium text-slate-500">Klik untuk upload atau drag & drop fail di sini</p>
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-500">Penerangan Project</label>
                            <textarea rows="4" placeholder="Ceritakan sedikit tentang design yang anda mahukan..." class="w-full rounded-2xl border-0 bg-[#f0f0f4] px-5 py-4 text-sm text-[#2d2f31] ring-1 ring-transparent transition focus:ring-2 focus:ring-[#b20069]"></textarea>
                        </div>
                        <a href="{{ auth()->check() ? route('orders.create') : route('member.register') }}" class="inline-flex w-full items-center justify-center rounded-[1.25rem] bg-[#b20069] px-6 py-5 text-base font-black text-white shadow-xl shadow-[#b20069]/20 transition hover:bg-[#9d005c]">
                            Submit Request
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bg-[#f0f0f4] py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-14 text-center">
            <h2 class="text-4xl font-extrabold tracking-[-0.04em] text-[#2d2f31]">Galeri Design Terkini</h2>
            <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-600">Saya kekalkan data design sebenar daripada kategori aktif supaya frontpage baru masih terus hidup dengan kandungan admin panel anda.</p>
        </div>

        @forelse($categories as $category)
            <div class="mb-16 last:mb-0">
                <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#b20069]">Kategori</p>
                        <h3 class="mt-2 text-2xl font-extrabold text-[#2d2f31]">{{ $category->name }}</h3>
                    </div>
                    <span class="text-sm font-bold text-slate-500">{{ $category->designs->count() }} design tersedia</span>
                </div>

                <div class="grid grid-cols-2 gap-5 md:grid-cols-3 xl:grid-cols-4">
                    @forelse($category->designs->take(4) as $design)
                        <article class="group overflow-hidden rounded-[1.8rem] bg-white shadow-sm ring-1 ring-slate-200 transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_30px_70px_-42px_rgba(178,0,105,0.45)]">
                            <div class="aspect-square overflow-hidden bg-[#f0f0f4]">
                                @if($design->image_path)
                                    <img src="{{ asset('storage/' . $design->image_path) }}" alt="{{ $design->name }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                                @else
                                    <div class="flex h-full items-center justify-center">
                                        <span class="material-symbols-outlined text-5xl text-slate-300">image</span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-5">
                                <h4 class="text-base font-extrabold text-[#2d2f31]">{{ $design->name }}</h4>
                                <p class="mt-2 line-clamp-2 text-sm leading-7 text-slate-600">{{ $design->description ?: 'Penerangan design akan dikemaskini tidak lama lagi.' }}</p>
                                <a href="{{ route('orders.create', ['design_id' => $design->id]) }}" class="mt-5 inline-flex items-center gap-2 text-sm font-black text-[#b20069] transition-all group-hover:gap-3">
                                    Pilih Design
                                    <span class="material-symbols-outlined text-lg">arrow_forward</span>
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-[1.8rem] border-2 border-dashed border-slate-300 px-6 py-16 text-center text-sm font-medium italic text-slate-500">
                            Koleksi sedang dikemaskini untuk kategori ini.
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="rounded-[2rem] border-2 border-dashed border-slate-300 px-6 py-20 text-center">
                <p class="text-lg font-bold text-slate-500">Tiada kategori aktif buat masa ini.</p>
            </div>
        @endforelse
    </div>
</section>

<section id="testimoni" class="bg-[#f0f0f4] py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-16 text-center">
            <h2 class="text-4xl font-extrabold tracking-[-0.04em] text-[#2d2f31]">Testimoni Pelanggan</h2>
            <div class="mt-4 flex items-center justify-center gap-1 text-[#fdd400]">
                @for($i = 0; $i < 5; $i++)
                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1,'wght' 600,'GRAD' 0,'opsz' 24;">star</span>
                @endfor
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            <div class="rounded-[2rem] bg-white p-8 shadow-sm transition-transform duration-300 hover:-translate-y-1">
                <div class="mb-6 flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#ff6dae]/20 text-xl font-black text-[#7a0047]">AF</div>
                    <div>
                        <h4 class="font-extrabold text-[#2d2f31]">Ahmad Firdaus</h4>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Business Owner</p>
                    </div>
                </div>
                <p class="text-sm italic leading-8 text-slate-600">"Kualiti cetakan sangat tajam dan warna memang ngam. Penghantaran pun laju. Design baru frontpage macam ni memang sepadan dengan servis premium mereka."</p>
            </div>
            <div class="rounded-[2rem] border-t-4 border-[#b20069] bg-white p-8 shadow-sm transition-transform duration-300 hover:-translate-y-1">
                <div class="mb-6 flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#fdd400]/30 text-xl font-black text-[#6d5a00]">SN</div>
                    <div>
                        <h4 class="font-extrabold text-[#2d2f31]">Siti Nurhaliza</h4>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Home Baker</p>
                    </div>
                </div>
                <p class="text-sm italic leading-8 text-slate-600">"Sticker label memang tahan dan kemas bila tampal pada botol sejuk. Mudah untuk order semula sebab semua nampak jelas terus dari homepage."</p>
            </div>
            <div class="rounded-[2rem] bg-white p-8 shadow-sm transition-transform duration-300 hover:-translate-y-1">
                <div class="mb-6 flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#a6a9ff]/30 text-xl font-black text-[#1e1d8a]">KL</div>
                    <div>
                        <h4 class="font-extrabold text-[#2d2f31]">Kevin Lau</h4>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Event Planner</p>
                    </div>
                </div>
                <p class="text-sm italic leading-8 text-slate-600">"Custom order sangat smooth. Pasukan faham apa yang dimahukan dan hasil akhir memang nampak profesional. Harga pula masih competitive."</p>
            </div>
        </div>
    </div>
</section>

<section id="hubungi-kami" class="bg-[#f6f6f9] py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-16 lg:grid-cols-2">
            <div>
                <h2 class="text-4xl font-extrabold tracking-[-0.04em] text-[#2d2f31]">Hubungi Kami</h2>
                <p class="mt-5 max-w-md text-base leading-7 text-slate-600">Ada sebarang pertanyaan? Hubungi kami melalui borang di bawah atau terus ke saluran pantas untuk sebut harga dan semakan order.</p>
                <div class="mt-12 space-y-8">
                    <div class="flex items-start gap-5">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#b20069]/10 text-[#b20069]">
                            <span class="material-symbols-outlined">call</span>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-[#2d2f31]">WhatsApp</h4>
                            <p class="mt-1 text-slate-600">011-69409606</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-5">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#b20069]/10 text-[#b20069]">
                            <span class="material-symbols-outlined">public</span>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-[#2d2f31]">Social Media</h4>
                            <p class="mt-1 text-slate-600">@stickertermurah</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-5">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#b20069]/10 text-[#b20069]">
                            <span class="material-symbols-outlined">location_on</span>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-[#2d2f31]">HQ Studio</h4>
                            <p class="mt-1 text-slate-600">Bandar Baru Bangi, Selangor Darul Ehsan</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] bg-[#f0f0f4] p-8 sm:p-10">
                <form class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-500">Nama</label>
                        <input type="text" class="w-full rounded-2xl border-0 bg-white px-5 py-4 text-sm text-[#2d2f31] ring-1 ring-transparent transition focus:ring-2 focus:ring-[#b20069]">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-500">Topik Pertanyaan</label>
                        <select class="w-full rounded-2xl border-0 bg-white px-5 py-4 text-sm text-[#2d2f31] ring-1 ring-transparent transition focus:ring-2 focus:ring-[#b20069]">
                            <option>Harga Cetakan</option>
                            <option>Status Order</option>
                            <option>Masalah Design</option>
                            <option>Lain-lain</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-500">Mesej</label>
                        <textarea rows="4" class="w-full rounded-2xl border-0 bg-white px-5 py-4 text-sm text-[#2d2f31] ring-1 ring-transparent transition focus:ring-2 focus:ring-[#b20069]"></textarea>
                    </div>
                    <a href="https://wa.me/601169409606" target="_blank" class="inline-flex w-full items-center justify-center rounded-[1.25rem] bg-[#4c4fb7] px-6 py-5 text-base font-black text-white shadow-xl shadow-[#4c4fb7]/20 transition hover:bg-[#4042aa]">
                        Kirim Mesej
                    </a>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@section('page_footer')
<footer class="bg-[#f6f6f9] pt-20 pb-10">
    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-12 px-4 text-sm leading-7 sm:px-6 md:grid-cols-4 lg:px-8">
        <div>
            <div class="mb-6 flex items-center gap-3">
                <img src="{{ asset('images/logo-baru.png') }}" alt="StickerTermurah" class="h-12 w-auto">
                <div>
                    <p class="text-lg font-extrabold uppercase tracking-[0.2em] text-[#4c4fb7]">Sticker Termurah</p>
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500">Printing Studio</p>
                </div>
            </div>
            <p class="max-w-xs text-slate-500">Penyelesaian percetakan editorial kelas atasan untuk semua keperluan pemasaran dan perniagaan anda.</p>
        </div>
        <div>
            <h5 class="mb-6 font-bold text-[#b20069]">Quick Links</h5>
            <ul class="space-y-3 text-slate-500">
                <li><a href="{{ route('home') }}" class="transition-colors hover:text-[#6d5a00]">Home</a></li>
                <li><a href="{{ auth()->check() ? route('orders.create') : route('member.register') }}" class="transition-colors hover:text-[#6d5a00]">Order Now</a></li>
                <li><a href="{{ route('orders.lookup-form') }}" class="transition-colors hover:text-[#6d5a00]">Track Delivery</a></li>
                <li><a href="#kategori" class="transition-colors hover:text-[#6d5a00]">Bulk Pricing</a></li>
            </ul>
        </div>
        <div>
            <h5 class="mb-6 font-bold text-[#b20069]">Business Info</h5>
            <ul class="space-y-3 text-slate-500">
                <li><a href="#custom-order" class="transition-colors hover:text-[#6d5a00]">About Us</a></li>
                <li><a href="#kategori" class="transition-colors hover:text-[#6d5a00]">Print Guide</a></li>
                <li><a href="#testimoni" class="transition-colors hover:text-[#6d5a00]">Testimonials</a></li>
                <li><a href="#hubungi-kami" class="transition-colors hover:text-[#6d5a00]">Contact</a></li>
            </ul>
        </div>
        <div>
            <h5 class="mb-6 font-bold text-[#b20069]">Legal & Policy</h5>
            <ul class="space-y-3 text-slate-500">
                <li><a href="#" class="transition-colors hover:text-[#6d5a00]">Privacy Policy</a></li>
                <li><a href="#" class="transition-colors hover:text-[#6d5a00]">Terms of Service</a></li>
                <li><a href="#" class="transition-colors hover:text-[#6d5a00]">Refund Policy</a></li>
            </ul>
        </div>
    </div>
    <div class="mx-auto mt-16 flex max-w-7xl flex-col items-center justify-between gap-6 border-t border-slate-200 px-4 pt-8 text-sm text-slate-500 sm:px-6 md:flex-row lg:px-8">
        <p>&copy; {{ date('Y') }} Sticker Termurah. High-end editorial printing services.</p>
        <div class="flex gap-4">
            <div class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-[#e7e8ec] text-slate-500 transition-all hover:bg-[#b20069] hover:text-white">
                <span class="material-symbols-outlined text-xl">social_leaderboard</span>
            </div>
            <div class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-[#e7e8ec] text-slate-500 transition-all hover:bg-[#b20069] hover:text-white">
                <span class="material-symbols-outlined text-xl">photo_camera</span>
            </div>
            <div class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-[#e7e8ec] text-slate-500 transition-all hover:bg-[#b20069] hover:text-white">
                <span class="material-symbols-outlined text-xl">share</span>
            </div>
        </div>
    </div>
</footer>
@endsection
