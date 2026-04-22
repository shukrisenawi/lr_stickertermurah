@extends('layouts.frontend')

@section('title', 'Login Ahli')

@section('content')
<div class="mx-auto max-w-[480px]">
    <div class="frontend-flat-card p-8 sm:p-10" x-data="{ showPassword: false }">
        <div class="space-y-6 text-center">
            <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-xl bg-brand-600 text-white shadow-lg shadow-brand-600/20">
                <img src="{{ asset('images/logo-baru.png') }}" alt="StickerTermurah" class="h-8 w-8 object-contain">
            </div>
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Login Ahli</h1>
                <p class="mt-2 text-sm text-slate-500">Akses rekod order, invoice, dan repeat order dengan lebih cepat.</p>
            </div>
        </div>

        <a href="{{ route('member.google.redirect') }}" class="mt-8 inline-flex w-full items-center justify-center gap-3 rounded-xl border border-slate-200 px-5 py-3.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.75c1.89 0 3.58.65 4.92 1.92l3.67-3.67C18.36.94 15.44 0 12 0 7.31 0 3.27 2.69 1.28 6.61l4.26 3.31C6.53 6.99 9.02 4.75 12 4.75zm11.64 7.48c0-.9-.08-1.77-.24-2.61H12v4.94h6.46c-.28 1.5-1.13 2.77-2.4 3.62l3.77 2.92c2.2-2.03 3.47-5.03 3.47-8.87zM5.54 14.08a7.27 7.27 0 010-4.16L1.28 6.61a12 12 0 000 10.78l4.26-3.31zM12 24c3.24 0 5.96-1.07 7.95-2.9l-3.77-2.92c-1.05.7-2.39 1.12-4.18 1.12-2.98 0-5.47-2.24-6.46-5.17l-4.26 3.31C3.27 21.31 7.31 24 12 24z"/></svg>
            Login dengan Google
        </a>

        <div class="relative py-5">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
            <div class="relative flex justify-center"><span class="bg-white px-4 text-xs font-medium uppercase tracking-[0.18em] text-slate-400">atau</span></div>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="{{ route('member.login.attempt') }}" class="space-y-5">
            @csrf
            <div>
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com">
            </div>
            <div>
                <label>Kata Laluan</label>
                <div class="relative">
                    <input x-bind:type="showPassword ? 'text' : 'password'" type="password" name="password" required placeholder="Masukkan kata laluan" class="pr-11">
                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-700">
                        <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.43 0 .644C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.58 10.58A3 3 0 0 0 13.42 13.42M9.88 5.09A9.77 9.77 0 0 1 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.43 0 .644a11.984 11.984 0 0 1-3.215 4.933M6.228 6.228A11.965 11.965 0 0 0 2.037 11.68a1.012 1.012 0 0 0 0 .644C3.423 16.493 7.36 19.5 12 19.5a9.76 9.76 0 0 0 5.272-1.523"/></svg>
                    </button>
                </div>
            </div>
            <label class="flex items-center gap-3 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200"> Kekalkan log masuk
            </label>
            <button type="submit" class="frontend-btn-primary w-full">Login Akaun Ahli</button>
        </form>

        <p class="mt-6 text-sm text-slate-500">Belum ada akaun? <a href="{{ route('member.register') }}" class="font-semibold text-brand-600">Daftar sekarang</a>.</p>
    </div>
</div>
@endsection
