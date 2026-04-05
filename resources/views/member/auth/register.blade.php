@extends('layouts.frontend')

@section('title', 'Daftar Ahli')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 md:p-10">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Daftar Ahli</h1>
        <p class="mt-2 text-sm text-slate-500 font-medium">Daftar ringkas dengan email & kata laluan untuk ulang order, lihat invoice dan sejarah tempahan.</p>

        <a href="{{ route('member.google.redirect') }}" class="mt-6 inline-flex w-full items-center justify-center gap-3 rounded-2xl border border-slate-200 px-5 py-3.5 text-sm font-black text-slate-700 hover:bg-slate-50 transition-colors">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.75c1.89 0 3.58.65 4.92 1.92l3.67-3.67C18.36.94 15.44 0 12 0 7.31 0 3.27 2.69 1.28 6.61l4.26 3.31C6.53 6.99 9.02 4.75 12 4.75zm11.64 7.48c0-.9-.08-1.77-.24-2.61H12v4.94h6.46c-.28 1.5-1.13 2.77-2.4 3.62l3.77 2.92c2.2-2.03 3.47-5.03 3.47-8.87zM5.54 14.08a7.27 7.27 0 010-4.16L1.28 6.61a12 12 0 000 10.78l4.26-3.31zM12 24c3.24 0 5.96-1.07 7.95-2.9l-3.77-2.92c-1.05.7-2.39 1.12-4.18 1.12-2.98 0-5.47-2.24-6.46-5.17l-4.26 3.31C3.27 21.31 7.31 24 12 24z"/></svg>
            Daftar / Login dengan Google
        </a>

        <div class="my-6 flex items-center gap-4">
            <div class="h-px flex-1 bg-slate-200"></div>
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">atau</span>
            <div class="h-px flex-1 bg-slate-200"></div>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-100 p-4 text-sm text-rose-700 font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="post" action="{{ route('member.register.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-900 focus:border-brand-600 focus:ring-brand-600" placeholder="nama@email.com">
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Kata Laluan</label>
                <input type="password" name="password" required class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-900 focus:border-brand-600 focus:ring-brand-600" placeholder="Minimum 8 aksara">
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Sahkan Kata Laluan</label>
                <input type="password" name="password_confirmation" required class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-900 focus:border-brand-600 focus:ring-brand-600" placeholder="Ulang kata laluan">
            </div>
            <button type="submit" class="w-full rounded-2xl bg-brand-600 px-6 py-4 text-sm font-black uppercase tracking-widest text-white hover:bg-brand-700 transition-colors">Daftar Akaun Ahli</button>
        </form>

        <p class="mt-6 text-sm text-slate-500 font-medium">Sudah ada akaun? <a href="{{ route('member.login') }}" class="font-black text-brand-600">Login di sini</a>.</p>
    </div>
</div>
@endsection
