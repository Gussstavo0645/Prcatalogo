<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Catálogos'))</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background: #f4f6fb;
        }

        .admin-navbar {
            background: #006b68;
            border-bottom: 1px solid rgba(255,255,255,.15);
            padding: 18px 0;
        }

        .admin-navbar .brand {
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-navbar .nav-link-custom {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            margin-left: 22px;
        }

        .admin-navbar .nav-link-custom:hover {
            color: #d1fae5;
        }

        .admin-content {
            padding-top: 24px;
        }

        .section-card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 4px 14px rgba(0,0,0,.04);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .section-card .card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 700;
            font-size: 18px;
            padding: 14px 18px;
        }

        .section-card .card-body {
            padding: 18px;
        }

        .step-badge {
            display: inline-block;
            min-width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #0d6efd;
            color: #fff;
            text-align: center;
            line-height: 28px;
            font-size: 14px;
            margin-right: 8px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            min-height: 42px;
        }

        .btn {
            border-radius: 8px;
            min-height: 42px;
        }

        .summary-box {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .summary-title {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: 700;
        }
    </style>

    @yield('styles')
    @stack('styles')
</head>

<body>

    <nav class="admin-navbar">
        <div class="container d-flex justify-content-between align-items-center flex-wrap">
           <a href="{{ route('admin.dashboard') }}" class="brand">
    <span>📊</span>
    <span>ADMIN</span>
</a>

          <div class="d-flex align-items-center flex-wrap">

  

    <a href="{{ route('admin.catalogs.index') }}" class="nav-link-custom">
        Catálogos
    </a>

    <a href="{{ route('admin.pedidos.index') }}" class="nav-link-custom">
        Pedidos
    </a>

    @auth
        @if(auth()->user()->role === 'admin_general')

            <a href="{{ route('admin.catalogs.create') }}" class="nav-link-custom">
                Crear Catálogo
            </a>

              <a href="{{ route('admin.dashboard') }}" class="nav-link-custom">
        Dashboard
    </a>

         {{--   <a href="{{ route('admin.products.index') }}" class="nav-link-custom">
                Crear Productos
            </a>--}}

            @if(Route::has('admin.stores.index'))
                <a href="{{ route('admin.stores.index') }}" class="nav-link-custom">
                    Tiendas
                </a>
            @endif

        @endif

        <form method="POST" action="{{ route('logout') }}" class="d-inline ms-3">
            @csrf
            <button type="submit" class="btn btn-sm btn-light fw-bold">
                Cerrar sesión
            </button>
        </form>
    @endauth

</div>
        </div>
    </nav>

    <main class="admin-content">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>