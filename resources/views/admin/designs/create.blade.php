@extends('layouts.admin')

@section('content')
<h1 class="h5">Tambah Design</h1>
<form method="post" action="{{ route('admin.designs.store') }}" enctype="multipart/form-data" class="card card-body">
    @csrf
    <div class="mb-2">
        <label class="form-label">Kategori</label>
        <select class="form-select" name="category_id" required>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-2">
        <label class="form-label">Nama Design</label>
        <input class="form-control" name="name" value="{{ old('name') }}" required>
    </div>
    <div class="mb-2">
        <label class="form-label">Deskripsi</label>
        <textarea class="form-control" name="description" rows="2">{{ old('description') }}</textarea>
    </div>
    <div class="mb-2">
        <label class="form-label">Gambar Design</label>
        <input type="file" class="form-control" name="image" accept="image/*">
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" name="is_active" value="1" checked id="activeDesign">
        <label class="form-check-label" for="activeDesign">Aktif</label>
    </div>
    <button class="btn btn-primary">Simpan</button>
</form>
@endsection
