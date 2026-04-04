@extends('layouts.admin')

@section('content')
<h1 class="h5">Edit Design</h1>
<form method="post" action="{{ route('admin.designs.update', $design) }}" enctype="multipart/form-data" class="card card-body">
    @csrf @method('put')
    <div class="mb-2">
        <label class="form-label">Kategori</label>
        <select class="form-select" name="category_id" required>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id', $design->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-2">
        <label class="form-label">Nama Design</label>
        <input class="form-control" name="name" value="{{ old('name', $design->name) }}" required>
    </div>
    <div class="mb-2">
        <label class="form-label">Deskripsi</label>
        <textarea class="form-control" name="description" rows="2">{{ old('description', $design->description) }}</textarea>
    </div>
    @if($design->image_path)
        <img src="{{ asset('storage/'.$design->image_path) }}" alt="{{ $design->name }}" style="max-width:220px" class="mb-2 rounded border">
    @endif
    <div class="mb-2">
        <label class="form-label">Tukar Gambar (optional)</label>
        <input type="file" class="form-control" name="image" accept="image/*">
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" name="is_active" value="1" id="activeDesign" {{ old('is_active', $design->is_active) ? 'checked' : '' }}>
        <label class="form-check-label" for="activeDesign">Aktif</label>
    </div>
    <button class="btn btn-primary">Kemaskini</button>
</form>
@endsection
