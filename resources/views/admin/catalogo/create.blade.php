@extends('layouts.app')

@section('content')
<style>
  /* Panel flotante */
  #cartFloatWrap{
    position: fixed;
    top: 90px;
    right: 16px;
    width: 320px;
    z-index: 9999;
  }

  /* En PC: deja espacio para que el panel NO aplaste el contenido */
  @media (min-width: 1200px){
    .container-fluid{
      padding-right: 360px !important;
    }
  }

  /* En móvil/tablet: que el panel se vuelva normal (no flotante) */
  @media (max-width: 992px){
    #cartFloatWrap{
      position: static;
      width: 100%;
      max-width: 100%;
      margin-top: 12px;
    }

    /* En móviles no reservamos espacio */
    .container-fluid{
      padding-right: 12px !important;
    }
  }
</style>
<div class="container-fluid py-4">

   @if($errors->any())

     <div class="alert alert-danger">
       <ul class="mb-0">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
       </ul>
      </div>
 
      @endif

  @if(session('ok'))

    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif
  

<div class="d-flex flex-column flex-xl-row align-items-start justify-content-between gap-3">
  <div>
    <h3>Nuevo catálogo</h3>
    <p class="text-muted mb-2"></p>
  </div>

  <div class="mb-3">
    <label class="form-label fw-bold">Seleccionar catálogo para editar</label>

    <form method="GET" action="{{ route('admin.catalogs.create') }}">
      <div class="input-group">
        <select name="catalog" class="form-select" onchange="this.form.submit()">
          <option value="">-- Crear nuevo catálogo --</option>

          @foreach($catalogs as $c)
            <option value="{{ $c->id }}" {{ request('catalog') == $c->id ? 'selected' : '' }}>
              {{ $c->title }}
            </option>
          @endforeach
        </select>

        @if(request('catalog'))
          <a href="{{ route('admin.catalogs.create') }}" class="btn btn-secondary">Nuevo</a>
        @endif
      </div>
    </form>

    --
  {{-- FILTRO DE CATÁLOGO (MES + TIPO) --}}
<form method="GET" action="{{ route('admin.catalogs.create') }}" class="mb-3">
  <input type="hidden" name="catalog" value="{{ request('catalog') }}">

  <div class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="form-label">Mes del catálogo</label>
      <input type="text"
             name="mesyope"
             class="form-control"
             value="{{ request('mesyope', $mes ?? '03/2026') }}"
             placeholder="Ej: 03/2026">
    </div>

    <div class="col-md-3">
      <label class="form-label">Tipo del catálogo</label>
      <select name="tipocatalogo" class="form-select">
        <option value="N" {{ request('tipocatalogo', $tipo ?? 'N') == 'N' ? 'selected' : '' }}>
          N - Normal
        </option>
        <option value="E" {{ request('tipocatalogo', $tipo ?? '') == 'E' ? 'selected' : '' }}>
          E - Revista éxito
        </option>
        <option value="F" {{ request('tipocatalogo', $tipo ?? '') == 'F' ? 'selected' : '' }}>
          F - Promoción fuera de catálogo
        </option>
        <option value="C" {{ request('tipocatalogo', $tipo ?? '') == 'C' ? 'selected' : '' }}>
          C - Catálogo / Insumos
        </option>
      </select>
    </div>

    <div class="col-md-2 d-flex gap-2">
      <button type="submit" class="btn btn-primary w-100">
        🔄 Cargar productos
      </button>
    </div>
  </div>
</form>

@if($catalog)
  <div class="mb-3">
    <button type="button"
            class="btn btn-outline-dark"
            data-bs-toggle="modal"
            data-bs-target="#pagesModal">
      📄 Ver páginas
    </button>
  </div>
@endif
  </div>
</div>
  </div>
</div>
  {{--  AQUÍ CIERRAS EL FLEX --}}

  {{-- panel flotante arriba derecha --}}
<div id="cartFloatWrap" style="min-width: 320px; max-width: 420px;">
  <div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span class="fw-semibold">Productos en catálogo</span>
      <span class="badge bg-primary" id="cartCount">0</span>
    </div>

    <div class="card-body p-2" id="cartPanel" style="max-height: 260px; overflow:auto;">
      {{-- aquí JS va a pintar los productos --}}
    </div>
  </div>
</div>
                

       {{-- from crear catalogo (incluye type) --}}
       <form action="{{ route('admin.catalogs.store') }}" method="POST">
       @csrf
       <input type="hidden" name="mesyope" value="{{ request('mesyope', $mes ?? '03/2026') }}">
<input type="hidden" name="tipocatalogo" value="{{ request('tipocatalogo', $tipo ?? 'N') }}">

        <div class="row g-2 align-items-center">
       <div class="col-md-4">
        <input name="title" class="form-control" placeholder="Título" required value="{{ old('title') }}">
       </div>

         <div class="col-md-5">
         <input name="description" class="form-control" placeholder="Descripción" value="{{ old('description') }}">
         </div>

         <div class="col-md-2">
         <select name="type" class="form-select" required>
          <option value="N" {{ old('type','N')=='N'?'selected':'' }}>N - Normal</option>
          <option value="E" {{ old('type')=='E'?'selected':'' }}>E - Revista éxito</option>
          <option value="F" {{ old('type')=='F'?'selected':'' }}>F - Promoción fuera de catálogo</option>
          <option value="C" {{ old('type')=='C'?'selected':'' }}>C - Catálogo / Insumos</option>
         </select>
         </div>

      <div class="col-md-1 form-check">
        <input type="checkbox"
               class="form-check-input"
               name="is_public"
               id="is_public"
               {{ old('is_public', true) ? 'checked' : '' }}>
        <label for="is_public" class="form-check-label">Público</label>
      </div>
    </div>

    <button class="btn btn-primary mt-3">Crear</button>
  </form>

  {{-- SUBIR PAGINAS --}}
  {{-- SUBIR PAGINAS --}}
<h4>Subir páginas</h4>

@if($catalog)
  <form action="{{ route('admin.catalogs.pages.store', $catalog) }}"
        method="POST"
        enctype="multipart/form-data">
    @csrf

    <div class="mb-2">
      <input type="file" name="pages[]" class="form-control" multiple>
      <small class="text-muted">Sube las páginas en orden (page-001, page-002...).</small>
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
  </form>
@else
  <div class="alert alert-info">
    Primero crea un catálogo (arriba) y luego podrás subir sus páginas aquí.
  </div>
@endif
 <h4 class="mb-3">Catálogo armado</h4>

@if(!$catalog)
  <div class="alert alert-info">
    Crea un catálogo arriba para empezar a armarlo con productos.
  </div>
@endif

  <hr>

  {{-- PRODUCTOS DISPONIBLES --}}
  <h4 class="mb-3">Productos</h4>

  @if($products->isEmpty())
    <div class="alert alert-info">Aún no tienes productos registrados.</div>
  @else
    <div class="row g-3" id="availableProducts">

  @foreach($products as $p)
  @php
    $prod = $p instanceof \Illuminate\Support\Collection ? $p->first() : $p;
  @endphp
  @continue(!$prod)

  <div class="col-6 col-md-3" id="product-card-{{$prod->code}}-{{$prod->color}}">
    <div class="card h-100">

      @php
        $imgUrl = !empty($prod->color)
            ? route('catalog.product.image', ['code' => $prod->code, 'color' => $prod->color])
            : route('catalog.product.image', ['code' => $prod->code]);
      @endphp

      <img src="{{ $imgUrl }}"
           class="card-img-top bg-white"
           style="height:220px; object-fit:contain; width:100%;"
           alt="{{ $prod->name }}"
           onerror="this.onerror=null;this.src='https://via.placeholder.com/220x220?text=Sin+imagen';">

      <div class="card-body d-flex flex-column">
        <div class="fw-semibold p-name">{{ $prod->name }}</div>
        <div class="text-muted small p-price">Q {{ number_format($prod->price,2) }}</div>

          <div class="p-qty mt-auto">
            <span class="badge bg-primary w-100 text-center">1 u</span>
          </div>

          <div class="d-flex gap-2 mt-2">

             {{-- CANTIDAD --}}
        <input type="number" min="1" value="1"
               class="form-control form-control-sm"
               style="max-width:90px"
               id="qty-{{ $prod->code }}-{{ $prod->color }}">

        {{-- PÁGINA AUTOMÁTICA DESDE INVENTARIO --}}
        <input type="number" min="1"
               value="{{ $prod->source_page ?? 1 }}"
               class="form-control form-control-sm"
               style="max-width:90px"
               id="page-{{ $prod->code }}-{{ $prod->color }}">

              <button type="button"
        class="btn btn-primary btn-sm ms-auto"
        data-code="{{ $prod->code }}"
        data-color="{{ $prod->color }}"
        onclick="addToCatalog(this, {{ $catalog?->id ?? 'null' }})"
        {{ $catalog ? '' : 'disabled' }}>
  Agregar
</button>


    <button type="button"
    class="btn btn-danger btn-sm"
    onclick="deleteProduct('{{$prod->code}}','{{$prod->color}}')">
    Eliminar
    </button>

          </div>
             @if(!$catalog)
             <div class="text-muted small mt-2">
                 Primero crea un catálogo para poder agregar productos.
                 </div>
              @endif
                     </div>
                                     </div>
                            </div>
                                                  @endforeach
</div>


    <div class="mt-3">
      {{ $products->appends(request()->query())->links() }}

    </div>
  @endif

  <hr class="my-4">

  {{-- Catalogo armado --}}

  @if(!$catalog)
  
  @else
  
<hr>
@endif
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
@endsection

@section('scripts')
@php
  $cartItems = [];

  if(isset($catalogProducts) && $catalogProducts->count()){
    foreach($catalogProducts as $cp){
      $page = (int)($cp->page_number ?? 1);

      $cartItems[] = [
        'product' => [
          'id' => $cp->id ?? 0,
          'name' => $cp->name ?? 'Producto no encontrado',
          'price' => (float)($cp->price ?? 0),
          'code' => trim($cp->code ?? ''),
          'color' => trim($cp->color ?? ''),
        ],
        'quantity' => (int)($cp->quantity ?? 1),
        'page_number' => $page,
      ];
    }
  }
@endphp

<script>
  window.__CART_ITEMS__ = @json($cartItems);
  window.__CATALOG_ID__ = @json($catalog?->id);
 window.__PRODUCT_IMG_BASE__ = "{{ url('/catalogo/producto-imagen') }}";
</script>
<script>
function formatPrice(q){
  return Number(q).toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function updateCartCount(){
  const panel = document.getElementById('cartPanel');
  const count = panel ? panel.querySelectorAll('[data-cart-item="1"]').length : 0;
  const badge = document.getElementById('cartCount');
  if(badge) badge.textContent = String(count);
}

function renderCartRow(product, qty, catalogId, pageNumber){
  const code = (product.code || '').trim();
  const color = (product.color || '').trim();
  const key = `${code}-${color}-${pageNumber}`;

  let imgUrl = 'https://via.placeholder.com/44x44?text=Sin+foto';

  if(code){
    imgUrl = color
      ? `${window.__PRODUCT_IMG_BASE__}/${encodeURIComponent(code)}/${encodeURIComponent(color)}`
      : `${window.__PRODUCT_IMG_BASE__}/${encodeURIComponent(code)}`;
  }

  return `
    <div class="d-flex align-items-center gap-2 border rounded p-2 mb-2"
         data-cart-item="1"
         id="cart-item-${key}">
      <img src="${imgUrl}"
           style="width:44px;height:44px;object-fit:contain;border-radius:8px;background:#fff;"
           alt=""
           onerror="this.onerror=null;this.src='https://via.placeholder.com/44x44?text=Sin+foto'">

      <div class="flex-grow-1">
        <div class="fw-semibold small">${product.name ?? 'Producto no encontrado'}</div>
        <div class="text-muted small">Q ${formatPrice(product.price ?? 0)}</div>
        <div class="text-muted small">Pág: ${pageNumber}</div>
      </div>

      <span class="badge bg-secondary" id="cart-qty-${key}">${qty} u</span>

      <button type="button"
        class="btn btn-outline-danger btn-sm"
        onclick="removeFromCatalog('${code}', '${color}', ${catalogId}, ${pageNumber})">✕</button>
    </div>
  `;
}

function renderGridCard(product, qty, catalogId, pageNumber){
  const code = (product.code || '').trim();
  const color = (product.color || '').trim();
  const key = `${code}-${color}-${pageNumber}`;

  let imgUrl = 'https://via.placeholder.com/220x220?text=Sin+imagen';

  if(code){
    imgUrl = color
      ? `${window.__PRODUCT_IMG_BASE__}/${encodeURIComponent(code)}/${encodeURIComponent(color)}`
      : `${window.__PRODUCT_IMG_BASE__}/${encodeURIComponent(code)}`;
  }

  return `
    <div class="col-6 col-md-3" id="cat-item-${key}">
      <div class="card h-100">
        <img src="${imgUrl}"
             class="card-img-top bg-white"
             style="height:220px;object-fit:contain;width:100%;"
             alt=""
             onerror="this.onerror=null;this.src='https://via.placeholder.com/220x220?text=Sin+imagen';">

        <div class="card-body">
          <div class="fw-semibold">${product.name}</div>
          <div class="text-muted small">Q ${formatPrice(product.price)}</div>
          <div class="text-muted small">Pág: ${pageNumber}</div>

          <div class="d-flex justify-content-between align-items-center mt-2">
            <span class="badge bg-secondary" id="grid-qty-${key}">${qty} u</span>

            <button type="button"
              class="btn btn-outline-danger btn-sm"
              onclick="removeFromCatalog('${code}', '${color}', ${catalogId}, ${pageNumber})">
              Quitar
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
}

async function addToCatalog(btn, catalogId){
  const oldText = btn?.textContent || 'Agregar';

  if(btn){
    btn.disabled = true;
    btn.textContent = '...';
  }

  try {
    if(!catalogId){
      alert('Primero crea un catálogo.');
      return;
    }

    const code = (btn?.dataset.code || '').trim();
    const color = (btn?.dataset.color || '').trim();
    const keyBase = (btn?.dataset.key || `${code}-${color}`).trim();

    if(!code){
      alert('El producto no tiene código.');
      return;
    }

    const qty = Number(document.getElementById(`qty-${keyBase}`)?.value || 1);
    const pageNumber = Number(document.getElementById(`page-${keyBase}`)?.value || 1);

    const res = await fetch(`/admin/catalogos/${catalogId}/products`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        code: code,
        color: color,
        quantity: qty,
        page_number: pageNumber
      })
    });

    let data = null;
    const ct = res.headers.get('content-type') || '';

    if (ct.includes('application/json')) {
      data = await res.json();
    } else {
      const text = await res.text();
      console.log('RESPUESTA NO JSON:', text);
      alert('El servidor no devolvió JSON. Revisa consola (F12).');
      return;
    }

    if(!res.ok || !data || !data.ok){
      console.log('DATA ERROR:', data);
      alert(data?.message || 'No se pudo agregar.');
      return;
    }

    if(!data.product){
      console.log('DATA SIN PRODUCT:', data);
      alert('El servidor no devolvió product. Revisa el controller addProduct().');
      return;
    }

    const key = `${data.product.code}-${data.product.color}-${data.page_number}`;

    // PANEL DERECHO
    const panel = document.getElementById('cartPanel');
    if(panel){
      document.getElementById('cartEmpty')?.remove();

      const existingCart = document.getElementById(`cart-item-${key}`);
      if(existingCart){
        const b = document.getElementById(`cart-qty-${key}`);
        if(b) b.textContent = `${data.quantity} u`;
      } else {
        panel.insertAdjacentHTML(
          'afterbegin',
          renderCartRow(data.product, data.quantity, catalogId, data.page_number)
        );
      }
    }

    updateCartCount();

   

  } catch (e) {
    console.error(e);
    alert('Ocurrió un error al agregar el producto.');
  } finally {
    if(btn){
      btn.disabled = false;
      btn.textContent = oldText;
    }
  }
}

async function removeFromCatalog(code, color, catalogId, pageNumber = 1){
  if(!confirm('¿Quitar este producto del catálogo?')){
    return;
  }

  try {
    const res = await fetch(`/admin/catalogos/${catalogId}/products/remove-by-code`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        code: code,
        color: color,
        page_number: pageNumber
      })
    });

    const data = await res.json();

    if(!res.ok || !data.ok){
      alert(data?.message || 'Error al quitar');
      return;
    }

    const key = `${code}-${color}-${pageNumber}`;


    const panel = document.getElementById('cartPanel');
    if(panel && !panel.querySelector('[data-cart-item="1"]')){
      panel.innerHTML = `
        <div class="text-muted small px-2 py-2" id="cartEmpty">
          Aún no has agregado productos.
        </div>
      `;
    }

  } catch (e) {
    console.error(e);
    alert('Ocurrió un error al quitar el producto.');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const panel = document.getElementById('cartPanel');
  const catalogId = window.__CATALOG_ID__;
  const items = window.__CART_ITEMS__ || [];

  if(!panel) return;

  panel.innerHTML = '';

  if(!items.length){
    panel.insertAdjacentHTML('beforeend', `
      <div class="text-muted small px-2 py-2" id="cartEmpty">
        Aún no has agregado productos.
      </div>
    `);
  } else {
    items.forEach(it => {
      panel.insertAdjacentHTML('beforeend',
        renderCartRow(it.product, it.quantity, catalogId, it.page_number)
      );
    });
  }

  updateCartCount();
});
</script>
@endsection