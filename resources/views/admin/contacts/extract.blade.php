@extends('layouts.admin')

@section('title', 'Extract Contact')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm p-6">
        <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Extract Contact</h2>
        <p class="mt-2 text-xs font-medium text-slate-500">Tampal data mentah format `NAMA | NO HP | ALAMAT`. Sistem akan extract ke huruf besar, kesan poskod, dan cadangkan pengguna hampir sama.</p>

        <form method="post" action="{{ route('admin.contacts.extract.run') }}" class="mt-5 space-y-3">
            @csrf
            <textarea
                name="raw_text"
                rows="10"
                class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold text-slate-800 focus:border-brand-500 focus:ring-brand-500"
                placeholder="Contoh: NAMA | 60123456789 | ALAMAT PENUH"
            >{{ old('raw_text', $rawText) }}</textarea>
            @error('raw_text')
                <p class="text-xs font-bold text-rose-600">{{ $message }}</p>
            @enderror
            <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-5 py-2 text-[10px] font-black uppercase tracking-widest text-white hover:bg-brand-600 transition-all active:scale-95">
                Extract Sekarang
            </button>
        </form>
    </div>

    @if(!empty($contacts))
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Hasil Extraction: {{ count($contacts) }} kontak</p>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($contacts as $index => $contact)
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
                            <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-100">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Nama</p>
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $contact['name'] }}"
                                    class="js-copy-field mt-1 w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-black text-slate-900 cursor-pointer focus:ring-2 focus:ring-brand-500"
                                    title="Klik untuk copy"
                                >
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-100">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">No HP</p>
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $contact['phone'] }}"
                                    class="js-copy-field mt-1 w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-black text-slate-900 cursor-pointer focus:ring-2 focus:ring-brand-500"
                                    title="Klik untuk copy"
                                >
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-100 lg:col-span-2">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Alamat & Poskod</p>
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $contact['address'] }}"
                                    class="js-copy-field mt-1 w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-black text-slate-900 cursor-pointer focus:ring-2 focus:ring-brand-500"
                                    title="Klik untuk copy"
                                >
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="text-[10px] font-black text-brand-600">POSKOD</span>
                                    <input
                                        type="text"
                                        readonly
                                        value="{{ $contact['postcode'] }}"
                                        class="js-copy-field w-28 rounded-lg border border-slate-200 bg-white px-2 py-1 text-[10px] font-black text-brand-700 cursor-pointer focus:ring-2 focus:ring-brand-500"
                                        title="Klik untuk copy"
                                    >
                                </div>
                            </div>
                        </div>

                        @if(!empty($contact['suggestions']))
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4">
                                <p class="text-[10px] font-black uppercase tracking-widest text-emerald-700">Padanan Nama Sama / Hampir Sama</p>
                                <div class="mt-3 space-y-2">
                                    @foreach($contact['suggestions'] as $suggestion)
                                        <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_1.4fr_auto] gap-3 rounded-xl bg-white ring-1 ring-emerald-100 p-3 items-start lg:items-center">
                                            <div>
                                                <p class="text-xs font-black text-slate-900">{{ $suggestion['name'] }}</p>
                                                <p class="text-[10px] font-bold text-slate-500">{{ $suggestion['email'] }} | Skor: {{ $suggestion['score'] }}</p>
                                            </div>

                                            <div class="rounded-lg bg-slate-50 ring-1 ring-slate-100 p-2">
                                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Alamat Terbaru User</p>
                                                <p class="mt-1 text-[10px] font-black text-slate-700 leading-relaxed">{{ $suggestion['latest_address'] ?? '-' }}</p>
                                            </div>

                                            <form method="post" action="{{ route('admin.contacts.extract.add-address') }}">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $suggestion['id'] }}">
                                                <input type="hidden" name="name" value="{{ $contact['name'] }}">
                                                <input type="hidden" name="phone" value="{{ $contact['phone'] }}">
                                                <input type="hidden" name="address" value="{{ $contact['address'] }}">
                                                <input type="hidden" name="postcode" value="{{ $contact['postcode'] }}">
                                                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white hover:bg-emerald-700 transition-all">
                                                    Tambah Address
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <form method="post" action="{{ route('admin.contacts.extract.add-user') }}" class="inline-block">
                            @csrf
                            <input type="hidden" name="name" value="{{ $contact['name'] }}">
                            <input type="hidden" name="phone" value="{{ $contact['phone'] }}">
                            <input type="hidden" name="address" value="{{ $contact['address'] }}">
                            <input type="hidden" name="postcode" value="{{ $contact['postcode'] }}">
                            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white hover:bg-brand-600 transition-all">
                                Tambah User Baru
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function showCopiedAlert() {
        let toast = document.getElementById('copy-toast-alert');

        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'copy-toast-alert';
            toast.className = 'fixed bottom-6 right-6 z-[9999] rounded-lg bg-emerald-600 px-4 py-2 text-[11px] font-black uppercase tracking-widest text-white shadow-xl ring-1 ring-emerald-500/40 opacity-0 transition-opacity duration-200';
            toast.textContent = 'Dah Copy';
            document.body.appendChild(toast);
        }

        toast.classList.remove('opacity-0');
        toast.classList.add('opacity-100');

        clearTimeout(window.__copyToastTimer);
        window.__copyToastTimer = setTimeout(() => {
            toast.classList.remove('opacity-100');
            toast.classList.add('opacity-0');
        }, 900);
    }

    document.addEventListener('click', async function (event) {
        const target = event.target;
        if (!(target instanceof HTMLInputElement) || !target.classList.contains('js-copy-field')) {
            return;
        }

        const value = target.value ?? '';
        target.select();
        target.setSelectionRange(0, target.value.length);

        try {
            await navigator.clipboard.writeText(value);
        } catch (e) {
            document.execCommand('copy');
        }

        target.classList.add('ring-2', 'ring-emerald-500');
        setTimeout(() => {
            target.classList.remove('ring-2', 'ring-emerald-500');
        }, 350);

        showCopiedAlert();
    });
</script>
@endpush





