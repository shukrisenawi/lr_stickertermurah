@extends('layouts.admin')

@section('title', 'Profil Admin')

@section('content')
    <section class="space-y-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="admin-title-block">
                    <span class="admin-title-accent"></span>
                    <h1 class="admin-section-title">Profil Admin</h1>
                </div>
                <p class="admin-section-copy mt-2">Kemas kini maklumat akaun yang dipaparkan pada navbar dan sistem admin.</p>
            </div>

            <a href="{{ route('admin.password.edit') }}" class="admin-btn-secondary">
                Tukar Kata Laluan
            </a>
        </div>

        <div class="admin-flat-card max-w-3xl p-6 lg:p-8">
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid gap-6 lg:grid-cols-[220px_minmax(0,1fr)] lg:items-start">
                    <div class="space-y-3">
                        <label>Avatar Admin</label>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-center">
                            @if(auth()->user()?->avatar_path)
                                <img
                                    src="{{ asset('storage/' . auth()->user()->avatar_path) }}"
                                    alt="{{ auth()->user()->name }}"
                                    class="mx-auto h-28 w-28 rounded-full border-4 border-white object-cover shadow-sm"
                                >
                            @else
                                <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-slate-900 text-3xl font-bold text-white shadow-sm">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                                </div>
                            @endif

                            <p class="mt-4 text-sm font-semibold text-slate-900">Gambar Profil</p>
                            <p class="mt-1 text-xs text-slate-500">PNG, JPG atau WEBP sehingga 4MB.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8">
                            <div class="text-center">
                                <svg class="mx-auto h-10 w-10 text-slate-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                </svg>
                                <div class="mt-4 flex justify-center text-sm text-slate-600">
                                    <label for="avatar" class="cursor-pointer font-semibold text-brand-600 transition hover:text-brand-500">
                                        <span>Pilih avatar</span>
                                        <input id="avatar" name="avatar" type="file" class="sr-only" accept="image/png,image/jpeg,image/webp">
                                    </label>
                                    <p class="pl-1">untuk dipaparkan dalam panel admin</p>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">Imej akan menggantikan bulatan huruf pada navbar dan sidebar.</p>
                            </div>
                        </div>

                        @error('avatar')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="name">Nama Penuh</label>
                        <input id="name" name="name" type="text" value="{{ old('name', auth()->user()->name) }}" placeholder="Nama admin">
                        @error('name')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="email">Emel</label>
                        <input id="email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}" placeholder="nama@syarikat.com">
                        @error('email')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Peranan</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">System Admin</p>
                    <p class="mt-1 text-sm text-slate-500">Akses penuh untuk pengurusan dashboard, tempahan, pelanggan dan tetapan sistem.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.dashboard') }}" class="admin-btn-secondary">Kembali Dashboard</a>
                    <button type="submit" class="admin-btn-primary">Simpan Profil</button>
                </div>
            </form>
        </div>
    </section>
@endsection
