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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.6/dist/driver.css">
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

    <!-- BOTONES FLOTANTES -->
<div class="floating-buttons">

    <!-- WhatsApp -->
    <a id="tour-whatsapp" href="https://wa.me/50254392024" target="_blank" class="float-btn whatsapp">
        <i class="bi bi-whatsapp"></i>
        <span class="tooltip">WhatsApp</span>
    </a>

    <!-- Teléfono -->
    <a id="tour-telefono" href="tel:+50254392024" class="float-btn phone">
        <i class="bi bi-telephone-fill"></i>
        <span class="tooltip">Llamar</span>
    </a>

    <!-- Chat -->
    {{--   
    <a href="#" class="float-btn chat">
        <i class="bi bi-chat-dots-fill"></i>
    </a>
    --}}

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/page-flip/dist/js/page-flip.browser.min.js"></script>
     <script> window.STORE_INFO = {
    name: @json($catalog->store_name ?? $catalog->title ?? 'Tienda'),
    address: @json($catalog->store_address ?? ''),
    hours: @json($catalog->store_hours ?? ''),
    manager: @json($catalog->store_manager ?? ''),
    whatsapp: @json($catalog->whatsapp_number ?? '50237553802')
  }; </script>
    <script src="{{ asset('js/catalogo_publico.js') }}?v=14"></script>

    <!-- ONBOARDING / TOUR GUIADO -->
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.6/dist/driver.js.iife.js"></script>

    @yield('scripts')
</body>
</html>