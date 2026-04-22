<!doctype html>
<html lang="ms" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin | StickerTermurah</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-baru.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="relative flex min-h-full items-center justify-center overflow-hidden bg-slate-50 px-4 py-8 text-slate-900 antialiased">
    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-[8%] -top-[12%] h-[24rem] w-[24rem] rounded-full bg-brand-600/8 blur-3xl"></div>
        <div class="absolute -bottom-[10%] -right-[8%] h-[22rem] w-[22rem] rounded-full bg-sky-200/40 blur-3xl"></div>
    </div>

    <div class="w-full max-w-[480px]" x-data="{ showPassword: false }">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
            <div class="space-y-6 text-center">
                <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-xl bg-brand-600 text-white shadow-lg shadow-brand-600/20">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5h16.5M6.75 3.75v7.5m10.5-7.5v7.5M6 20.25h12A2.25 2.25 0 0 0 20.25 18V9.75H3.75V18A2.25 2.25 0 0 0 6 20.25Z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Precision Admin</h1>
                    <p class="mt-2 text-sm text-slate-500">Selamat kembali. Sila masukkan maklumat log masuk anda.</p>
                </div>
            </div>

            @if($errors->any())
                <div class="mt-8 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="post" action="{{ route('admin.login.attempt') }}" class="mt-8 space-y-6">
                @csrf

                <div class="space-y-2">
                    <label for="email" class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Email Address</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 7.5v9A2.25 2.25 0 0 1 19.5 18.75h-15A2.25 2.25 0 0 1 2.25 16.5v-9m19.5 0A2.25 2.25 0 0 0 19.5 5.25h-15A2.25 2.25 0 0 0 2.25 7.5m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 9.659A2.25 2.25 0 0 1 2.25 7.743V7.5" />
                            </svg>
                        </span>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="name@company.com"
                            class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="password" class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Password</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.875a4.5 4.5 0 1 0-9 0V10.5m-1.5 0h12a1.5 1.5 0 0 1 1.5 1.5v6.75a1.5 1.5 0 0 1-1.5 1.5H6A1.5 1.5 0 0 1 4.5 18.75V12A1.5 1.5 0 0 1 6 10.5Z" />
                            </svg>
                        </span>
                        <input
                            id="password"
                            name="password"
                            x-bind:type="showPassword ? 'text' : 'password'"
                            placeholder="••••••••"
                            class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-11 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100"
                            required
                        >
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-slate-700"
                            aria-label="Tunjuk atau sembunyi kata laluan"
                        >
                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.43 0 .644C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.58 10.58A3 3 0 0 0 13.42 13.42M9.88 5.09A9.77 9.77 0 0 1 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.43 0 .644a11.984 11.984 0 0 1-3.215 4.933M6.228 6.228A11.965 11.965 0 0 0 2.037 11.68a1.012 1.012 0 0 0 0 .644C3.423 16.493 7.36 19.5 12 19.5a9.76 9.76 0 0 0 5.272-1.523" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <label for="remember" class="flex cursor-pointer items-center text-sm text-slate-500">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
                        <span class="ml-2">Remember me</span>
                    </label>
                    <span class="text-sm font-semibold text-brand-600">Portal dalaman</span>
                </div>

                <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-brand-600 px-6 py-3.5 text-base font-semibold text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-brand-200 focus:ring-offset-2">
                    Sign In
                </button>
            </form>

            <div class="relative py-5">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-white px-4 text-xs font-medium uppercase tracking-[0.18em] text-slate-400">Or continue with</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <button type="button" class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#EA4335" d="M12 10.2v3.9h5.4c-.2 1.3-1.6 3.9-5.4 3.9-3.2 0-5.9-2.7-5.9-6s2.7-6 5.9-6c1.8 0 3.1.8 3.8 1.4l2.6-2.5C16.8 3.4 14.6 2.5 12 2.5 6.9 2.5 2.8 6.6 2.8 11.7S6.9 21 12 21c6.9 0 9.1-4.8 9.1-7.3 0-.5-.1-.9-.1-1.2H12Z"/>
                    </svg>
                    Google
                </button>
                <button type="button" class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5h10.5m-10.5 4.5h10.5m-10.5 4.5h6.75M4.5 5.25h15A2.25 2.25 0 0 1 21.75 7.5v9A2.25 2.25 0 0 1 19.5 18.75h-15A2.25 2.25 0 0 1 2.25 16.5v-9A2.25 2.25 0 0 1 4.5 5.25Z" />
                    </svg>
                    SSO
                </button>
            </div>

            <div class="pt-5 text-center">
                <p class="text-sm text-slate-500">
                    Hanya akaun admin dibenarkan masuk ke panel ini.
                </p>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-between px-2 text-xs text-slate-400">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                System Operational
            </div>
            <div class="flex items-center gap-4">
                <span>Privacy</span>
                <span>Terms</span>
            </div>
        </div>
    </div>
</body>
</html>
