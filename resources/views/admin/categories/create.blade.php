@extends('layouts.admin')

@section('content')
<h1 class="h5">Tambah Kategori</h1>
<form method="post" action="{{ route('admin.categories.store') }}" class="card card-body">
    @csrf
    <div class="mb-2">
        <label class="form-label">Nama Kategori</label>
        <input class="form-control" name="name" value="{{ old('name') }}" required>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" name="is_active" value="1" checked id="active">
        <label for="active" class="form-check-label">Aktif</label>
    </div>
    <button class="btn btn-primary">Simpan</button>
</form>
@endsection
