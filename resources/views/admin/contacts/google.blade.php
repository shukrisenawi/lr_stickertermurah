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
                @if(!$isConnected && ($isConfigured ?? true))
                    <a href="{{ route('admin.contacts.google.connect') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-5 py-2 text-[10px] font-black uppercase tracking-widest text-white hover:bg-brand-600 transition-all active:scale-95">
                        Sambung Google Contact
                    </a>
                @elseif(!$isConnected)
                    <span class="inline-flex items-center rounded-xl bg-slate-200 px-5 py-2 text-[10px] font-black uppercase tracking-widest text-slate-500 cursor-not-allowed">
                        Google OAuth Belum Siap
                    </span>
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
        <div class="admin-table-card">
            <div class="admin-card-header">
                <div>
                    <h3 class="admin-section-title">Senarai Contact</h3>
                    <p class="admin-section-copy">Jumlah contact semasa: {{ count($contacts) }}</p>
                </div>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>HP</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contacts as $contact)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $contact['name'] }}</td>
                                <td>{{ $contact['phones'] }}</td>
                                <td>{{ $contact['emails'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6">
                                    <div class="admin-table-empty">
                                        <p class="admin-table-empty-title">Tiada contact dijumpai.</p>
                                        <p class="admin-table-empty-copy">Akaun Google ini belum ada contact yang boleh dipaparkan.</p>
                                    </div>
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
