@extends('layouts.admin')

@section('content')
<h1 class="h5">Tambah Saiz & Harga</h1>
<form method="post" action="{{ route('admin.sizes.store') }}" class="card card-body">
    @csrf
    <div class="row g-2">
        <div class="col-md-4"><label class="form-label">Nama</label><input class="form-control" name="name" required></div>
        <div class="col-md-2"><label class="form-label">Lebar cm</label><input type="number" step="0.01" class="form-control" name="width_cm"></div>
        <div class="col-md-2"><label class="form-label">Tinggi cm</label><input type="number" step="0.01" class="form-control" name="height_cm"></div>
        <div class="col-md-4"><label class="form-label">Harga (RM)</label><input type="number" step="0.01" class="form-control" name="price" required></div>
    </div>
    <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_default" value="1" id="defaultSize"><label class="form-check-label" for="defaultSize">Set sebagai harga default</label></div>
    <div class="form-check mt-1 mb-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="activeSize"><label class="form-check-label" for="activeSize">Aktif</label></div>
    <button class="btn btn-primary">Simpan</button>
</form>
@endsection
