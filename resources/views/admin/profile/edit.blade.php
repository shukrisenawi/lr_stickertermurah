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
            <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

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
