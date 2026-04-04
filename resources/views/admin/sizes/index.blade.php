@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1 class="h5">Saiz & Harga Sticker</h1>
    <a class="btn btn-primary btn-sm" href="{{ route('admin.sizes.create') }}">Tambah Saiz</a>
</div>
<div class="card"><div class="table-responsive"><table class="table table-sm mb-0">
    <thead><tr><th>Nama</th><th>Saiz (cm)</th><th>Harga</th><th>Default</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($sizes as $size)
        <tr>
            <td>{{ $size->name }}</td>
            <td>{{ $size->width_cm && $size->height_cm ? $size->width_cm.' x '.$size->height_cm : '-' }}</td>
            <td>RM {{ number_format($size->price, 2) }}</td>
            <td>{!! $size->is_default ? '<span class="badge text-bg-success">Ya</span>' : '-' !!}</td>
            <td>{!! $size->is_active ? '<span class="badge text-bg-primary">Aktif</span>' : '<span class="badge text-bg-secondary">Off</span>' !!}</td>
            <td class="text-end">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.sizes.edit', $size) }}">Edit</a>
                <form method="post" action="{{ route('admin.sizes.destroy', $size) }}" class="d-inline">
                    @csrf @method('delete')
                    <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Padam saiz?')">Padam</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-muted">Tiada data saiz/harga</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-2">{{ $sizes->links() }}</div>
@endsection
