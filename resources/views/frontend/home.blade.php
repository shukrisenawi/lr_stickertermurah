@extends('layouts.frontend')

@section('title', 'Sticker Mirrorcote')

@section('content')
<div class="hero p-4 p-md-5 mb-4">
    <h1 class="h3 mb-2">Sticker Mirrorcote Custom</h1>
    <p class="mb-3">Guest boleh pilih design, buat request custom, upload resit, dan repeat order bila-bila.</p>
    <a href="{{ route('orders.create') }}" class="btn btn-light">Mula Order</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5">Harga Sticker (Material: Mirrorcote)</h2>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr><th>Saiz</th><th>Dimensi (cm)</th><th>Harga / unit (RM)</th><th>Default</th></tr></thead>
                <tbody>
                @forelse($sizes as $size)
                    <tr>
                        <td>{{ $size->name }}</td>
                        <td>{{ $size->width_cm && $size->height_cm ? $size->width_cm.' x '.$size->height_cm : '-' }}</td>
                        <td>{{ number_format($size->price, 2) }}</td>
                        <td>{!! $size->is_default ? '<span class="badge text-bg-success">Ya</span>' : '-' !!}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">Harga belum ditetapkan oleh admin.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($categories as $category)
    <div class="mb-4">
        <h3 class="h5">{{ $category->name }}</h3>
        <div class="row g-3">
            @forelse($category->designs as $design)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100">
                        @if($design->image_path)
                            <img src="{{ asset('storage/'.$design->image_path) }}" class="card-img-top" alt="{{ $design->name }}">
                        @else
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-secondary-subtle">Tiada gambar</div>
                        @endif
                        <div class="card-body">
                            <h4 class="h6">{{ $design->name }}</h4>
                            <p class="small text-muted">{{ $design->description }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><p class="text-muted">Belum ada design untuk kategori ini.</p></div>
            @endforelse
        </div>
    </div>
@endforeach
@endsection
