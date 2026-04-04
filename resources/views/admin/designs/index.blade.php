@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1 class="h5">Design Sticker</h1>
    <a class="btn btn-primary btn-sm" href="{{ route('admin.designs.create') }}">Tambah Design</a>
</div>
<div class="row g-3">
@forelse($designs as $design)
    <div class="col-6 col-md-4 col-lg-3">
        <div class="card h-100">
            @if($design->image_path)
                <img src="{{ asset('storage/'.$design->image_path) }}" class="card-img-top" style="height:160px;object-fit:cover" alt="{{ $design->name }}">
            @else
                <div class="card-img-top bg-secondary-subtle d-flex align-items-center justify-content-center" style="height:160px">Tiada gambar</div>
            @endif
            <div class="card-body">
                <div class="small text-muted">{{ $design->category?->name }}</div>
                <h2 class="h6">{{ $design->name }}</h2>
                <div class="d-flex gap-1">
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.designs.edit', $design) }}">Edit</a>
                    <form method="post" action="{{ route('admin.designs.destroy', $design) }}">
                        @csrf @method('delete')
                        <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Padam design?')">Padam</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12 text-muted">Tiada design</div>
@endforelse
</div>
<div class="mt-3">{{ $designs->links() }}</div>
@endsection
