<!doctype html>
<html lang="ms" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Pengurusan Sticker | Login Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-baru.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col items-center justify-center p-6 antialiased">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-10">
            <img src="{{ asset('images/logo-baru.png') }}" alt="StickerTermurah" class="mx-auto h-28 w-auto drop-shadow-2xl">
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Portal Admin</h1>
            <p class="mt-2 text-sm font-medium text-slate-500 uppercase tracking-widest">StickerTermurah Management</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 ring-1 ring-slate-200 overflow-hidden border-b-8 border-brand-50">
            <div class="p-8 sm:p-10">
                @if($errors->any())
                    <div class="mb-8 p-4 rounded-2xl bg-rose-50 border border-rose-100 flex items-center gap-3 animate-shake">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-rose-500 text-white flex items-center justify-center text-lg font-black">!</div>
                        <p class="text-sm font-bold text-rose-800 tracking-tight">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form method="post" action="{{ route('admin.login.attempt') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 pl-1">ID Pengguna (Email)</label>
                        <div class="relative">
                            <input type="email" name="email" value="{{ old('email') }}" 
                                class="block w-full rounded-2xl border-0 py-4 px-5 text-slate-900 font-bold bg-slate-50 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-brand-600 tracking-wide transition-all" 
                                placeholder="nama@email.com" required autofocus>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between pl-1 mb-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kata Laluan</label>
                            <a href="#" class="text-[10px] font-black text-brand-600 uppercase tracking-[0.1em] hover:text-brand-500 transition-colors">Lupa Kata Laluan?</a>
                        </div>
                        <input type="password" name="password" 
                            class="block w-full rounded-2xl border-0 py-4 px-5 text-slate-900 font-bold bg-slate-50 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-brand-600 tracking-widest transition-all" 
                            placeholder="••••••••" required>
                    </div>

                    <div class="flex items-center gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <div class="flex h-5 items-center">
                            <input id="remember" name="remember" type="checkbox" 
                                class="h-5 w-5 rounded-lg border-slate-300 text-brand-600 focus:ring-brand-600 cursor-pointer">
                        </div>
                        <label for="remember" class="text-xs font-bold text-slate-600 cursor-pointer select-none">Kekal log masuk untuk sesi ini</label>
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                            class="w-full flex items-center justify-center gap-3 rounded-2xl bg-brand-600 px-8 py-5 text-sm font-black text-white shadow-2xl shadow-brand-100 hover:bg-brand-700 transition-all border-b-4 border-brand-800 active:border-b-0 active:translate-y-1">
                            Akses Sistem Sekarang
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer Info -->
        <p class="mt-10 text-center text-xs font-bold text-slate-400 uppercase tracking-widest">
            &copy; {{ date('Y') }} StickerTermurah Redesign. All rights reserved.
        </p>
    </div>
</body>
</html>

