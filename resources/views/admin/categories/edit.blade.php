@extends('layouts.admin')

@section('content')
<h1 class="h5">Edit Kategori</h1>
<form method="post" action="{{ route('admin.categories.update', $category) }}" class="card card-body">
    @csrf @method('put')
    <div class="mb-2">
        <label class="form-label">Nama Kategori</label>
        <input class="form-control" name="name" value="{{ old('name', $category->name) }}" required>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" name="is_active" value="1" id="active" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
        <label for="active" class="form-check-label">Aktif</label>
    </div>
    <button class="btn btn-primary">Kemaskini</button>
</form>
@endsection
