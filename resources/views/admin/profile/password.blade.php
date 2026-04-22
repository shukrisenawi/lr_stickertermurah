@extends('layouts.admin')

@section('title', 'Tukar Kata Laluan')

@section('content')
    <section class="space-y-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="admin-title-block">
                    <span class="admin-title-accent"></span>
                    <h1 class="admin-section-title">Tukar Kata Laluan</h1>
                </div>
                <p class="admin-section-copy mt-2">Tetapkan kata laluan baharu untuk akaun admin dengan pengesahan kata laluan semasa.</p>
            </div>

            <a href="{{ route('admin.profile.edit') }}" class="admin-btn-secondary">
                Lihat Profil
            </a>
        </div>

        <div class="admin-flat-card max-w-3xl p-6 lg:p-8">
            <form action="{{ route('admin.password.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="space-y-2">
                    <label for="current_password">Kata Laluan Semasa</label>
                    <input id="current_password" name="current_password" type="password" placeholder="Masukkan kata laluan semasa">
                    @error('current_password')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="password">Kata Laluan Baharu</label>
                        <input id="password" name="password" type="password" placeholder="Minimum 8 aksara">
                        @error('password')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="password_confirmation">Sahkan Kata Laluan Baharu</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulang kata laluan baharu">
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700">Keselamatan Akaun</p>
                    <p class="mt-2 text-sm text-amber-900">Gunakan kombinasi huruf besar, huruf kecil, nombor dan simbol untuk kata laluan yang lebih kuat.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.dashboard') }}" class="admin-btn-secondary">Kembali Dashboard</a>
                    <button type="submit" class="admin-btn-primary">Simpan Kata Laluan</button>
                </div>
            </form>
        </div>
    </section>
@endsection
