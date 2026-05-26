@extends('layouts.frontend')

@section('title', 'Login Ahli')

@section('content')
<div class="relative min-h-[calc(100vh-72px)] overflow-hidden bg-gradient-to-br from-pink-50 via-white to-blue-50">
    {{-- Decorative blobs --}}
    <div class="absolute -right-40 top-20 h-96 w-96 rounded-full bg-pink-200/30 blur-3xl"></div>
    <div class="absolute -left-40 bottom-20 h-96 w-96 rounded-full bg-blue-200/30 blur-3xl"></div>

    <div class="relative flex min-h-[calc(100vh-72px)] items-center justify-center px-4 py-12">
        <div class="w-full max-w-[420px]">
            {{-- Card --}}
            <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-xl shadow-slate-200/50" x-data="{ showPassword: false }">
                {{-- Header area with brand color --}}
                <div class="relative bg-gradient-to-r from-brand-600 to-fuchsia-700 px-8 pb-10 pt-10 text-center">
                    <div class="absolute -bottom-6 left-1/2 -translate-x-1/2">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-lg shadow-brand-600/20 ring-4 ring-white">
                            <img src="{{ asset('images/logo-baru.png') }}" alt="StickerTermurah" class="h-10 w-10 object-contain">
                        </div>
                    </div>
                    <h1 class="text-2xl font-extrabold text-white">Login Ahli</h1>
                    <p class="mt-1.5 text-sm text-white/80">Akses rekod order & invoice dengan pantas</p>
                </div>

                {{-- Form area --}}
                <div class="px-8 pb-8 pt-10">
                    {{-- Google Login --}}
                    <a href="{{ route('member.google.redirect') }}" class="inline-flex w-full items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:shadow-md">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.75c1.89 0 3.58.65 4.92 1.92l3.67-3.67C18.36.94 15.44 0 12 0 7.31 0 3.27 2.69 1.28 6.61l4.26 3.31C6.53 6.99 9.02 4.75 12 4.75zm11.64 7.48c0-.9-.08-1.77-.24-2.61H12v4.94h6.46c-.28 1.5-1.13 2.77-2.4 3.62l3.77 2.92c2.2-2.03 3.47-5.03 3.47-8.87zM5.54 14.08a7.27 7.27 0 010-4.16L1.28 6.61a12 12 0 000 10.78l4.26-3.31zM12 24c3.24 0 5.96-1.07 7.95-2.9l-3.77-2.92c-1.05.7-2.39 1.12-4.18 1.12-2.98 0-5.47-2.24-6.46-5.17l-4.26 3.31C3.27 21.31 7.31 24 12 24z"/></svg>
                        Login dengan Google
                    </a>

                    {{-- Divider --}}
                    <div class="relative py-6">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-100"></div></div>
                        <div class="relative flex justify-center"><span class="bg-white px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">atau</span></div>
                    </div>

                    {{-- Error message --}}
                    @if($errors->any())
                        <div class="mb-5 flex items-center gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    {{-- Login Form --}}
                    <form method="post" action="{{ route('member.login.attempt') }}" class="space-y-5">
                        @csrf

                        {{-- Email --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Email</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                </div>
                                <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-2 focus:ring-brand-100">
                            </div>
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Kata Laluan</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                </div>
                                <input x-ref="passwordInput" type="password" name="password" required placeholder="Masukkan kata laluan" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-11 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-2 focus:ring-brand-100">
                                <button type="button" @click="showPassword = !showPassword; $refs.passwordInput.type = showPassword ? 'text' : 'password'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-brand-600">
                                    <svg x-show="!showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.43 0 .644C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z"/></svg>
                                    <svg x-show="showPassword" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Remember me + Submit --}}
                        <div class="flex items-center justify-between">
                            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600">
                                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
                                Kekalkan log masuk
                            </label>
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-brand-600/20 transition hover:bg-brand-700">
                            Login Akaun Ahli
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                        </button>
                    </form>

                    {{-- Register link --}}
                    <div class="mt-6 text-center">
                        <p class="text-sm text-slate-500">Belum ada akaun? <a href="{{ route('member.register') }}" class="font-bold text-brand-600 hover:text-brand-700 transition">Daftar sekarang</a></p>
                    </div>
                </div>
            </div>

            {{-- Back to home --}}
            <div class="mt-6 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-brand-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75L3 12m0 0l3.75-3.75M3 12h18"/></svg>
                    Kembali ke laman utama
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
