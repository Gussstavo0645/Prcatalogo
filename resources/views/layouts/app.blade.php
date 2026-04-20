<!DOCTYPE html>
<html lang="es">
<head>

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Catálogo Digital')</title>

  {{-- Bootstrap 5 --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  {{-- Iconos FontAwesome --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="{{asset('css/catalogo_interno.css')}}">
  {{-- StPageFlip CSS (debe ir en <head>) --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/css/page-flip.min.css">

  {{-- Estilos globales --}}
  <style>
    body { background-color: #f8f9fa; font-family: "Poppins", sans-serif; }
    header.navbar { background-color: #006666; }
    header .navbar-brand, header .nav-link { color: white !important; font-weight: 500; }
    footer { background-color: #006666; color: #fff; texts-align: center; padding: 12px 0; margin-top: 40px; }
  </style>

<style>
@media print {
  .catalog-page { page-break-after: always; }
}
</style>
  @yield('head')
</head>
<body>

@if(empty($publicView))
    
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
               href="{{ route('admin.catalogs.index') }}">Catálogos</a>
          </li>
          <a class="nav-link {{ request()->routeIs('catalogs.*') ? 'active' : '' }}"
               href="{{ route('admin.pedidos.index') }}">Pedidos</a>
          </li>
          <a class="nav-link {{ request()->routeIs('catalogs.*') ? 'active' : '' }}"
               href="{{ route('admin.catalogs.create') }}">Crear Catalogo</a>
          </li>
           
           <li class="nav-item">
    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalProducto">
      <i {{--class="fa-solid fa-box me-1"--}}></i> Crear Productos
    </a>
</li>
</ul>
        </ul>
      </div>
    </div>
  </header>
  @endif

  {{-- CONTENIDO --}}
  <main>
    @yield('content')
  </main>

  {{-- PIE DE PÁGINA --}}
  <footer>
    <small>&copy; {{ date('Y') }} Catálogo Digital</small>
  </footer>

  {{-- Scripts globales --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  {{-- StPageFlip JS (va antes de </body>) --}}
  <script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.min.js"></script>
  <script src="{{asset('js/catalogo_interno.js')}}"> </script>
<!--  MODAL CREAR PRODUCTO -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

         <input type="hidden" name="catalog_id" value="{{ $catalog->id ?? '' }}">

        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Nuevo Producto</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
<div class="col-12 d-none" id="catBlock">
  <label class="form-label">Categorías</label>
  <select name="categories[]" id="categories" class="form-select" multiple>
    @foreach($categories as $cat)
      <option value="{{ $cat->id }}">{{ $cat->name }}</option>
    @endforeach
  </select>
  <small class="text-muted">Ctrl + click para seleccionar varias</small>
</div>

<div class="col-12 d-none" id="barcodeBlock">
  <label class="form-label">Código de identificación</label>

  <div class="row g-2">
    <div class="col-md-6">
      <label class="form-label small text-muted">Tipo de código de barras</label>
      <select name="barcode_type" id="barcode_type" class="form-select">
        <option value="">-- Seleccionar --</option>
        <option value="EAN13">EAN-13</option>
        <option value="EAN8">EAN-8</option>
        <option value="UPC">UPC</option>
        <option value="CODE128">CODE-128</option>
        <option value="QR">QR</option>
      </select>
    </div>

    <div class="col-md-6">
      <label class="form-label small text-muted">Código de barras</label>
      <input type="text" name="barcode_value" id="barcode_value" class="form-control" placeholder="Ej: 1234567890123">
    </div>
  </div>
</div>


            <div class="col-md-6">
              <label class="form-label">Título del producto</label>
              <input name="name" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Código de identificación</label>
              <input name="code" class="form-control">

            </div>

            <div class="col-12">
              <label class="form-label">Breve descripción</label>
              <textarea name="description" class="form-control" rows="2"></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label">Precio</label>
              <input type="number" step="0.01" name="price" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Foto</label>
              <input type="file" name="image" accept="image/*" class="form-control" required>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-success">Guardar</button>
        </div>

      </form>

    </div>
  </div>
</div>

  @yield('scripts')

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session()->has('swal_product_created'))
<script>
  const data = {
  title: {!! json_encode(session('swal_product_created.title'), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) !!},
  text:  {!! json_encode(session('swal_product_created.text'),  JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) !!},
  view:  {!! json_encode(session('swal_product_created.view'),  JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) !!},
  product: {!! json_encode(session('swal_product_created.product'), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) !!},
  update_url: {!! json_encode(session('swal_product_created.update_url'), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) !!},
};


  Swal.fire({
    icon: 'success',
    title: data.title || 'Listo',
    text: data.text || '',
    showDenyButton: true,
    showCancelButton: true,
    confirmButtonText: '1) Seguir editando',
    denyButtonText: '2) Registrar nuevo producto',
    cancelButtonText: '3) Ver el producto en el catálogo',
 }).then((result) => {

  // 1) Seguir editando
  if (result.isConfirmed) {
    const modalEl = document.getElementById('modalProducto');
    const form = modalEl?.querySelector('form');
    if (!modalEl || !form) return;

    // Mostrar categorías SOLO aquí
    const catBlock = document.getElementById('catBlock');
    if (catBlock) catBlock.classList.remove('d-none');

    // Cambiar a UPDATE
    if (data.update_url) form.action = data.update_url;

    // Spoof PATCH
    let methodInput = form.querySelector('input[name="_method"]');
    if (!methodInput) {
      methodInput = document.createElement('input');
      methodInput.type = 'hidden';
      methodInput.name = '_method';
      form.appendChild(methodInput);
    }
    methodInput.value = 'PATCH';

    // Llenar campos
    const p = data.product || {};
    form.querySelector('[name="name"]').value = p.name ?? '';
    form.querySelector('[name="code"]').value = p.code ?? '';
    form.querySelector('[name="description"]').value = p.description ?? '';
    form.querySelector('[name="price"]').value = p.price ?? '';

    // Marcar categorías
    const select = document.getElementById('categories');
    const selected = p.categories_ids || [];
    if (select) {
      [...select.options].forEach(opt => {
        opt.selected = selected.includes(parseInt(opt.value));
      });
    }

    // Mostrar bloque de barcode
const barcodeBlock = document.getElementById('barcodeBlock');
if (barcodeBlock) barcodeBlock.classList.remove('d-none');

// llenar barcode
form.querySelector('[name="barcode_type"]').value  = p.barcode_type ?? '';
form.querySelector('[name="barcode_value"]').value = p.barcode_value ?? '';


    // Botón a "Guardar cambios"
    const btn = form.querySelector('.btn.btn-success');
    if (btn) btn.textContent = 'Guardar cambios';

    new bootstrap.Modal(modalEl).show();
    return;
  }

  // 2) Registrar nuevo producto
  if (result.isDenied) {
    const modalEl = document.getElementById('modalProducto');
    const form = modalEl?.querySelector('form');
    if (!modalEl || !form) return;

    // Reset form
    form.reset();

    // Ocultar categorías otra vez
    const catBlock = document.getElementById('catBlock');
    if (catBlock) catBlock.classList.add('d-none');

    // limpiar selección
    const select = document.getElementById('categories');
    if (select) [...select.options].forEach(o => o.selected = false);

    // quitar PATCH si existe
    const m = form.querySelector('input[name="_method"]');
    if (m) m.remove();

    // volver a STORE
    form.action = "{{ route('admin.products.store') }}";

    // botón a Guardar
    const btn = form.querySelector('.btn.btn-success');
    if (btn) btn.textContent = 'Guardar';

    new bootstrap.Modal(modalEl).show();
    return;
  }

  // 3) Ver en catálogo
  if (result.dismiss === Swal.DismissReason.cancel) {
    if (data.view) window.location.href = data.view;
  }
});

</script>
@endif
</body>
</html>
