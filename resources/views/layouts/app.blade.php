<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Catálogo Digital')</title>

  {{-- Bootstrap 5 --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  {{-- Iconos FontAwesome --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  {{-- StPageFlip CSS (debe ir en <head>) --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/css/page-flip.min.css">

  {{-- Estilos globales --}}
  <style>
    body { background-color: #f8f9fa; font-family: "Poppins", sans-serif; }
    header.navbar { background-color: #006666; }
    header .navbar-brand, header .nav-link { color: white !important; font-weight: 500; }
    footer { background-color: #006666; color: #fff; text-align: center; padding: 12px 0; margin-top: 40px; }
  </style>

  @yield('head')
</head>
<body>

  {{-- NAVBAR --}}
  <header class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">
      <a href="{{ url('/') }}" class="navbar-brand fw-bold">
        <i class="fa-solid fa-book-open me-2"></i> CATALOGOS
        
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarMenu">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('catalogs.*') ? 'active' : '' }}"
               href="{{ route('catalogs.index') }}">Catálogos</a>
          </li>
          <a class="nav-link {{ request()->routeIs('catalogs.*') ? 'active' : '' }}"
               href="{{ route('admin.catalogs.create') }}">Crear Catalogo</a>
          </li>
         
        </ul>
      </div>
    </div>
  </header>

  {{-- CONTENIDO --}}
  <main>
    @yield('content')
  </main>

  {{-- PIE DE PÁGINA --}}
  <footer>
    <small>&copy; {{ date('Y') }} Catálogo Digital · Desarrollado en Laravel</small>
  </footer>

  {{-- Scripts globales --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  {{-- StPageFlip JS (va antes de </body>) --}}
  <script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.min.js"></script>

  @yield('scripts')
</body>
</html>
