@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1 class="h5">Kategori</h1>
    <a class="btn btn-primary btn-sm" href="{{ route('admin.categories.create') }}">Tambah Kategori</a>
</div>
<div class="card"><div class="table-responsive"><table class="table table-sm mb-0">
    <thead><tr><th>Nama</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($categories as $category)
        <tr>
            <td>{{ $category->name }}</td>
            <td>{!! $category->is_active ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Off</span>' !!}</td>
            <td class="text-end">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                <form method="post" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline">
                    @csrf @method('delete')
                    <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Padam kategori?')">Padam</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="3" class="text-muted">Tiada kategori</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-2">{{ $categories->links() }}</div>
@endsection
