@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="row g-4">
    {{-- COLUMNA IZQUIERDA --}}
    <div class="col-lg-9">

      {{-- BLOQUE 1: CATÁLOGO --}}
     
            {{-- BLOQUE 1: CATÁLOGO --}}
<div class="section-card">
  <div class="card-header">
    <span class="step-badge">1</span> Catálogo
  </div>

  <div class="card-body">

    <form method="GET" action="{{ route('admin.catalogs.create') }}" id="catalogHeaderForm">
      <div class="row g-3 align-items-end">

        <div class="col-md-4">
  <label class="form-label fw-bold">Catálogo Existente</label>
  <select name="catalog" class="form-select">
    <option value="">Seleccionar Catàtalogo</option>
    @foreach($catalogs as $c)
      <option value="{{ $c->id }}" {{ request('catalog') == $c->id ? 'selected' : '' }}>
        {{ $c->title }}
      </option>
    @endforeach
  </select>
</div>

   <div class="col-md-2">
  <label class="form-label fw-bold">Mes</label>
  <input 
  type="text"
  name="mesyope"
  class="form-control"
  placeholder="EJEMPLO 05/2026"
  value="{{ request('mesyope') }}"
  maxlength="7"
>
</div>

        <div class="col-md-2">
          <label class="form-label fw-bold">Tipo</label>
          <select name="tipocatalogo" class="form-select">
  <option value="">Seleccionar</option>
  @foreach($tipos as $t)
    <option value="{{ $t }}" {{ request('tipocatalogo', $tipo) == $t ? 'selected' : '' }}>
      {{ $t }}
    </option>
  @endforeach
</select>
        </div>

        <div class="col-md-2 d-grid">
          <button type="submit" class="btn btn-primary">
            Cargar productos
          </button>
        </div>

        <div class="col-md-2 d-grid">
          @if(request('catalog'))
            <a href="{{ route('admin.catalogs.create') }}" class="btn btn-secondary">
              Nuevo
            </a>
          @endif
        </div>

      </div>
    </form>
    <hr>

    <form action="{{ route('admin.catalogs.store') }}" method="POST">
      @csrf
  
<input type="hidden" name="mesyope" value="{{ request('mesyope') ?: ($mes ?? '05/2026') }}">
<input type="hidden" name="tipocatalogo" value="{{ request('tipocatalogo') ?: ($tipo ?? 'N') }}">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label fw-bold">Título</label>
          <input name="title" class="form-control" required>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-bold">Descripción</label>
          <input name="description" class="form-control">
        </div>

        <div class="col-md-2">
          <label class="form-label fw-bold">Tipo</label>
          <select name="type" class="form-select">
            <option value="N">N</option>
            <option value="E">E</option>
            <option value="F">F</option>
            <option value="C">C</option>
          </select>
        </div>

        <div class="col-md-2 d-grid">
          <button class="btn btn-success">Crear</button>
        </div>
      </div>
</form>
<hr>

@if($catalog)

<form action="{{ route('admin.catalogos.tiendas.sync', $catalog->id) }}" method="POST">
  @csrf

  <div class="alert alert-light border mb-3">
  <strong>Tiendas asignadas:</strong>

  @if($catalog->tiendas->count())
    <div class="small text-success">
    {{ $catalog->tiendas->map(function($t) {
    return str_pad($t->bodega_codigo ?? $t->id, 3, '0', STR_PAD_LEFT) . ' - ' . $t->name;
})->join(', ') }}
    </div>
  @else
    <div class="small text-danger">
      ⚠ Este catálogo aún no tiene tiendas
    </div>
  @endif
</div>
  <label class="form-label fw-bold">Seleccionar tiendas para este catálogo</label>

  @foreach($tiendas as $tienda)
    <div class="form-check">
      <input 
        class="form-check-input"
        type="checkbox"
        name="tiendas[]"
        value="{{ $tienda->id }}"
        id="tienda{{ $tienda->id }}"
        {{ $catalog->tiendas->contains($tienda->id) ? 'checked' : '' }}
      >
      <label class="form-check-label" for="tienda{{ $tienda->id }}">
        {{ str_pad($tienda->bodega_codigo, 3, '0', STR_PAD_LEFT) }} - {{ $tienda->name }}
      </label>
    </div>
  @endforeach

  <button class="btn btn-primary mt-3">
    Guardar tiendas para este catálogo
  </button>
@if(!$catalog->tiendas->count())
  <div class="text-danger mt-2">
    ⚠ Debes asignar al menos una tienda antes de publicar
  </div>
@endif
</form>

@else

<div class="alert alert-info mb-0">
  Primero crea o selecciona un catálogo para asignar tiendas.
</div>


@endif

  </div>
</div>
      {{-- BLOQUE 2: PÁGINAS --}}
      <div class="section-card {{ !$catalog ? 'disabled-block' : '' }}">
        <div class="card-header">
          <span class="step-badge">2</span> Páginas del catálogo
        </div>

        <div class="card-body">
        @if($catalog)
  <form action="{{ route('admin.catalogs.pages.store', $catalog) }}"
        method="POST"
        enctype="multipart/form-data">
    @csrf

    <div class="row g-3 align-items-end">
      <div class="col-md-8">
        <label class="form-label fw-bold">Subir páginas</label>
        <input type="file" name="pages[]" class="form-control" multiple>
        <small class="text-muted">Sube las páginas en orden (page-001, page-002...).</small>
      </div>

      <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>

      <div class="col-md-2 d-grid">
        <button type="button"
                class="btn btn-outline-dark"
                data-bs-toggle="modal"
                data-bs-target="#pagesModal">
          Ver páginas
        </button>
      </div>
    </div>
  </form>
@else
  <div class="alert alert-info mb-0">
    Primero crea un catálogo y luego podrás subir sus páginas aquí.
  </div>
@endif  
        </div>
      </div>

      @if($catalog)
  <div class="mb-3 text-end">
    <a href="{{ route('admin.catalogos.combos.create', $catalog->id) }}" 
       class="btn btn-warning">
      ➕ Crear Combo
    </a>
  </div>
@endif

      {{-- BLOQUE 3: PRODUCTOS --}}
      <div class="section-card {{ !$catalog ? 'disabled-block' : '' }}">
        <div class="card-header">
          <span class="step-badge">3</span> Buscar y agregar productos
        </div>

        <div class="card-body">
          @if(!$catalog)
            <div class="alert alert-info mb-0">
              Primero crea o selecciona un catálogo para agregar productos.
            </div>
          @else

            {{-- FILTRO GENERAL --}}
<form method="GET"
      action="{{ route('admin.catalogs.products.search') }}"
      class="mb-4"
      id="productsFilterForm">
  <input type="hidden" name="catalog" value="{{ request('catalog') }}">
  <input type="hidden" name="mesyope" value="{{ request('mesyope', $mes ?? '04/2026') }}">
<input type="hidden" name="tipocatalogo" value="{{ request('tipocatalogo', $tipo ?? 'N') }}">

  <div class="row g-3 align-items-end">
   
    <div class="col-md-2">
      <label class="form-label fw-bold">Filtrar por página</label>
      <input
        type="number"
        name="filter_page"
        class="form-control"
        min="1"
        value="{{ request('filter_page', $pageFilter ?? '') }}"
        placeholder="27"
      >
    </div>

    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-primary">Buscar productos</button>
    </div>

    <div class="col-md-2 d-grid">
     <button type="button" class="btn btn-outline-secondary" id="clearProductsFilter">
  Limpiar filtro
</button>
    </div>
  </div>
</form>

            {{-- BULK ADD --}}
            <div id="bulkAddForm">
              <div class="card mb-4 shadow-sm">
                <div class="card-body">
                  <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                      <label class="form-label fw-bold">Página destino</label>
                      <input type="number" id="bulkPageNumber" class="form-control" min="1" value="1" required>
                    </div>

                    <div class="col-md-3">
                      <label class="form-label fw-bold">Cantidad</label>
                      <input type="number" id="bulkQuantity" class="form-control" min="1" value="1" required>
                    </div>

                    <div class="col-md-3">
                      <label class="form-label fw-bold">Seleccionados</label>
                      <input type="text" id="selectedCount" class="form-control" value="0 productos" readonly>
                    </div>

                    <div class="col-md-3 d-grid">
                      <button type="button" class="btn btn-success" id="bulkAddBtn">
                        Agregar seleccionados
                      </button>
                    </div>
                  </div>

                  <div class="mt-3 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllBtn">
                      Seleccionar todos
                    </button>

                    <button type="button" class="btn btn-outline-secondary btn-sm" id="unselectAllBtn">
                      Quitar selección
                    </button>
                  </div>
                </div>
              </div>
            </div>

            {{-- LISTADO PRODUCTOS --}}
           <div id="productsSection">
  @include('admin.catalogo.parcial.products_list', [
      'products' => $products,
      'catalog' => $catalog,
      'mes' => $mes,
      'tipo' => $tipo,
      'pageFilter' => $pageFilter,
  ])
</div>
          @endif
        </div>
      </div>

      {{-- BLOQUE 4: LINKS --}}
      @if($catalog)
        <div class="section-card">
          <div class="card-header">
            Enlaces del catálogo
          </div>
          <div class="card-body">
            <div class="alert alert-info mb-0">
              <div>
                <strong>🔵 Vista interna:</strong><br>
                <a href="{{ url('/catalogos/'.$catalog->slug) }}" target="_blank">
                  {{ url('/catalogos/'.$catalog->slug) }}
                </a>
              </div>

              <hr>

              <div>
                <strong>🟢 Vista pública:</strong><br>
                <a href="{{ url('/c/'.$catalog->slug) }}" target="_blank">
                  {{ url('/c/'.$catalog->slug) }}
                </a>
              </div>
            </div>
          </div>
        </div>
      @endif

    </div>

    {{-- COLUMNA DERECHA --}}
    <div class="col-lg-3">
      <div id="cartFloatWrap">
        <div class="section-card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span><span class="step-badge">4</span> Resumen</span>
            <span class="badge bg-primary" id="cartCount">0</span>
          </div>

          <div class="card-body">
            <div class="summary-box">
              <div class="summary-title">Catálogo actual</div>
              <div class="summary-value" style="font-size:16px;">
                {{ $catalog->title ?? 'Sin seleccionar' }}
              </div>
            </div>

            <div class="summary-box">
              <div class="summary-title">Productos agregados</div>
              <div class="summary-value">
                {{ (isset($catalogProducts) ? $catalogProducts->count() : 0) + (isset($catalogCombos) ? $catalogCombos->count() : 0) }}
              </div>
            </div>
@php
  $mesMostrar = request('mesyope') ?? $mes;
  if ($mesMostrar === '99/9999') {
      $mesMostrar = null;
  }
@endphp

<div class="summary-value" style="font-size:16px;">
  {{ $mesMostrar ?: 'No definido' }}
</div>
            <div class="summary-box">
              <div class="summary-title">Tipo</div>
              <div class="summary-value" style="font-size:16px;">
                {{ request('tipocatalogo', $tipo ?? 'N') }}
              </div>
            </div>

            <hr>

            <div id="cartPanel" style="max-height: 360px; overflow:auto;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- MODAL PÁGINAS --}}
  @if($catalog)
    <div class="modal fade" id="pagesModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Páginas del catálogo: {{ $catalog->title }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>

          <div class="modal-body">
            @if($catalog->paginas->count())
              <div class="row g-3">
                @foreach($catalog->paginas->sortBy('page_number') as $pagina)
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm">
                      <img src="{{ route('catalog_pages.image', $pagina->id) }}"
                           class="card-img-top bg-white"
                           style="height:260px; object-fit:contain;"
                           alt="Página {{ $pagina->page_number }}">

                      <div class="card-body p-2">
                        <div class="fw-semibold text-center mb-2">
                          Página {{ $pagina->page_number }}
                        </div>

                        <form action="{{ route('admin.catalogs.paginas.destroy', [$catalog->id, $pagina->id]) }}"
                              method="POST"
                              onsubmit="return confirm('¿Eliminar esta página? También se eliminarán los productos asignados a esta página.');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-danger btn-sm w-100">
                            🗑 Eliminar página
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="alert alert-info mb-0">
                Este catálogo aún no tiene páginas.
              </div>
            @endif
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
  @endif

</div>
@endsection

@section('scripts')

@php
  $cartItems = [];

  if(isset($catalogProducts) && $catalogProducts->count()){
    foreach($catalogProducts as $cp){
      $cartItems[] = [
        'product' => [
          'id' => $cp->id ?? 0,
          'name' => $cp->name ?? 'Producto no encontrado',
          'price' => (float)($cp->price ?? 0),
          'code' => trim((string)($cp->code ?? '')),
          'color' => trim((string)($cp->color ?? '')),
          'is_combo' => false,
          'image_path' => null,
        ],
        'quantity' => (int)($cp->quantity ?? 1),
        'page_number' => (int)($cp->page_number ?? 1),
        'position' => (int)($cp->position ?? 999),
      ];
    }
  }

  if(isset($catalogCombos) && $catalogCombos->count()){
    foreach($catalogCombos as $cb){
      $cartItems[] = [
        'product' => [
          'id' => $cb->id ?? 0,
          'name' => $cb->name ?? 'Combo sin descripción',
          'price' => (float)($cb->price ?? 0),
          'code' => trim((string)($cb->code ?? '')),
          'color' => trim((string)($cb->color ?? '')),
          'is_combo' => true,
          'image_path' => $cb->image_path ?? null,
        ],
        'quantity' => (int)($cb->quantity ?? 1),
        'page_number' => (int)($cb->page_number ?? 1),
        'position' => (int)($cb->position ?? 0),
      ];
    }
  }

  $cartItems = collect($cartItems)
    ->sortBy([
      ['page_number', 'asc'],
      ['position', 'asc'],
    ])
    ->values()
    ->all();
@endphp

<script>
  window.__CART_ITEMS__ = @json($cartItems);
  window.__CATALOG_ID__ = @json($catalog?->id);
  window.__PRODUCT_IMG_BASE__ = "{{ url('/catalogo/producto-thumb') }}";
</script>
