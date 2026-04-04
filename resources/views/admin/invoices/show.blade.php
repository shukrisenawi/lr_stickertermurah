<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invois #{{ $invoice->invoice_no }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Outfit', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; margin: 0 !important; background: white !important; }
            .print-shadow-none { box-shadow: none !important; ring: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen p-4 md:p-12 antialiased">
    <div class="max-w-4xl mx-auto">
        <!-- Top Bar / Print Button -->
        <div class="no-print flex items-center justify-between mb-8">
            <a href="{{ route('admin.orders.show', $invoice->order) }}" class="inline-flex items-center gap-2 text-xs font-black text-slate-500 hover:text-slate-900 uppercase tracking-widest transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke Pesanan
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-3 rounded-2xl bg-white px-6 py-3 text-xs font-black text-slate-900 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 transition-all active:scale-95">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231a1.125 1.125 0 0 1-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
                CETAK INVOIS
            </button>
        </div>

        <!-- Invoice Container -->
        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/50 ring-1 ring-slate-200 overflow-hidden print-shadow-none">
            <!-- Invoice Header -->
            <div class="p-10 md:p-16 border-b border-slate-100 flex flex-col md:flex-row justify-between gap-12 bg-slate-50/30">
                <div class="space-y-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-slate-900 text-white shadow-xl">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                    </div>
                    <div>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tight leading-none uppercase italic">Invois Rasmi</h1>
                        <p class="mt-2 text-xs font-bold text-slate-400 uppercase tracking-[0.3em]">No. Rujukan: <span class="text-indigo-600">#{{ $invoice->invoice_no }}</span></p>
                    </div>
                </div>

                <div class="text-left md:text-right flex flex-col justify-end space-y-4">
                    <div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Tarikh Terbit</span>
                        <span class="text-lg font-black text-slate-900 tracking-tight">{{ $invoice->issue_date->format('d F Y') }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Status Pembayaran</span>
                        <span class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-widest ring-1 ring-emerald-200">
                             <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                             BERJAYA (PAID)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Billing Details -->
            <div class="p-10 md:p-16 grid grid-cols-1 md:grid-cols-2 gap-16">
                <div>
                    <span class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] block mb-6 px-1">Daripada (Vendor)</span>
                    <div class="space-y-4">
                        <p class="text-2xl font-black text-slate-900 tracking-tight leading-none italic uppercase">StickerTermurah</p>
                        <div class="text-sm font-bold text-slate-500 leading-relaxed italic">
                            Lot 123, Kawasan Perindustrian,<br>
                            08000 Sungai Petani, Kedah.<br>
                            Malaysia.<br>
                            <span class="text-indigo-600 block mt-2 not-italic tracking-wider">+60 12-345 6789</span>
                        </div>
                    </div>
                </div>
                <div>
                    <span class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] block mb-6 px-1">Kepada (Pelanggan)</span>
                    <div class="space-y-4">
                        <p class="text-2xl font-black text-slate-900 tracking-tight leading-none capitalize">{{ $invoice->order->customer_name }}</p>
                        <div class="text-sm font-bold text-slate-500 leading-relaxed italic">
                            {{ $invoice->order->customer_address }}<br>
                            <span class="text-indigo-600 block mt-2 not-italic tracking-wider">{{ $invoice->order->customer_phone }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="px-10 md:px-16 mb-12">
                <div class="overflow-x-auto rounded-3xl ring-1 ring-slate-100 shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th scope="col" class="py-5 pl-8 pr-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Keterangan Produk</th>
                                <th scope="col" class="px-3 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Unit</th>
                                <th scope="col" class="px-3 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Harga Seunit</th>
                                <th scope="col" class="py-5 pl-3 pr-8 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($invoice->order->items as $item)
                                <tr class="group hover:bg-slate-50/30 transition-all">
                                    <td class="py-6 pl-8 pr-3">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-900 tracking-tight uppercase">{{ $item->design->name }}</span>
                                            <span class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-wider italic">Material: {{ $invoice->order->material }} | Saiz: {{ $item->size->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-6 text-sm text-center">
                                        <span class="inline-flex items-center justify-center h-8 w-10 rounded-lg bg-slate-50 text-slate-900 font-black italic border border-slate-100 text-xs">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="px-3 py-6 text-sm text-slate-400 font-bold text-right italic tracking-wider">RM {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="py-6 pl-3 pr-8 text-right">
                                        <span class="text-base font-black text-slate-900 italic tracking-wider">RM {{ number_format($item->line_total, 2) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-900 text-white">
                                <td colspan="3" class="py-8 pl-8 text-right">
                                    <span class="text-xs font-black uppercase tracking-[0.3em] opacity-60">Jumlah Bersih (Total Nett)</span>
                                </td>
                                <td class="py-8 pr-8 text-right">
                                    <span class="text-4xl font-black italic tracking-tighter">RM {{ number_format($invoice->amount, 2) }}</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="p-10 md:p-16 bg-slate-50/50 border-t border-slate-100 flex flex-col md:flex-row justify-between gap-12">
                <div class="md:max-w-md">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3">Nota & Arahan</span>
                    <p class="text-xs font-bold text-slate-500 leading-relaxed italic">
                        {{ $invoice->notes ?: 'Terima kasih kerana berurusan dengan StickerTermurah. Kami amat menghargai sokongan anda!' }}
                    </p>
                </div>
                <div class="flex flex-col items-start md:items-end justify-center">
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.4em] mb-4">Pengurusan Digital</p>
                    <div class="h-1 w-24 bg-slate-900 rounded-full"></div>
                </div>
            </div>
        </div>

        <!-- Footer Info (External) -->
        <div class="mt-12 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] space-y-2 opacity-50">
            <p>Invois ini dijana secara automatik oleh sistem StickerTermurah Digital Portal.</p>
            <p>&copy; {{ date('Y') }} StickerTermurah Enterprise. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>

