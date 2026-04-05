@extends('layouts.admin')

@section('title', 'Contact')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Google Contact</h2>
                <p class="mt-2 text-xs font-medium text-slate-500">Sambungkan akaun Google untuk lihat semua senarai contact anda.</p>
            </div>

            <div class="flex items-center gap-2">
                @if(!$isConnected)
                    <a href="{{ route('admin.contacts.google.connect') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-5 py-2 text-[10px] font-black uppercase tracking-widest text-white hover:bg-brand-600 transition-all active:scale-95">
                        Sambung Google Contact
                    </a>
                @else
                    <form method="post" action="{{ route('admin.contacts.google.disconnect') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-xl bg-rose-600 px-5 py-2 text-[10px] font-black uppercase tracking-widest text-white hover:bg-rose-700 transition-all active:scale-95">
                            Putuskan Sambungan
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if($error)
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                {{ $error }}
            </div>
        @endif
    </div>

    @if($isConnected)
        <div class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Jumlah Contact: {{ count($contacts) }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-widest text-slate-500">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-widest text-slate-500">HP</th>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-widest text-slate-500">Email</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($contacts as $contact)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm font-semibold text-slate-800">{{ $contact['name'] }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-700">{{ $contact['phones'] }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-700">{{ $contact['emails'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-10 text-center text-sm font-semibold text-slate-500">
                                    Tiada contact dijumpai dalam akaun Google ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
