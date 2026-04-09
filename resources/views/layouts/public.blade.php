<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Catálogo')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/catalogo_publico.css') }}?v=13">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/page-flip/dist/css/page-flip.css">
</head>

<body class="catalog-body">
<div class="bg-logos">
  <span class="bg-logo l1"></span>
  <span class="bg-logo l2"></span>
  <span class="bg-logo l3"></span>
  <span class="bg-logo l4"></span>
</div>
    <div class="catalog-page">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/page-flip/dist/js/page-flip.browser.min.js"></script>
    
    <script src="{{ asset('js/catalogo_publico.js') }}?v=13"></script>

    @yield('scripts')
</body>
</html>