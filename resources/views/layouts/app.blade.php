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
    <link rel="stylesheet" href="{{ asset('css/panel_admin.css') }}?v=13">
    
    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    

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
    <script src="{{ asset('js/panel_admin.js') }}?v=13"></script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>