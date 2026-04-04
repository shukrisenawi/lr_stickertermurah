<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sticker Mirrorcote')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --brand: #e85d04;
            --brand-dark: #9d0208;
            --bg-soft: #fff7ed;
        }
        body { background: linear-gradient(180deg, #fff, var(--bg-soft)); }
        .hero { background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; border-radius: 1rem; }
        .card-img-top { height: 180px; object-fit: cover; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">StickerTermurah</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('orders.create') }}">Buat Order</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('orders.lookup-form') }}">Semak / Repeat Order</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.login') }}">Admin</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
