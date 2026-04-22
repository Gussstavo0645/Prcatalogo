@extends('layouts.app')

@section('content')
<style>
  .section-card{
    border:1px solid #e5e7eb;
    border-radius:14px;
    background:#fff;
    box-shadow:0 4px 14px rgba(0,0,0,.04);
    margin-bottom:20px;
  }

  .section-card .card-header{
    background:#f8fafc;
    border-bottom:1px solid #e5e7eb;
    font-weight:700;
    font-size:18px;
    padding:14px 18px;
    border-radius:14px 14px 0 0;
  }

  .section-card .card-body{
    padding:18px;
  }

  .step-badge{
    display:inline-block;
    min-width:28px;
    height:28px;
    border-radius:50%;
    background:#0d6efd;
    color:#fff;
    text-align:center;
    line-height:28px;
    font-size:14px;
    margin-right:8px;
  }

  .disabled-block{
    opacity:.65;
  }

  #cartFloatWrap{
    position:sticky;
    top:90px;
  }

  .summary-box{
    background:#f8fafc;
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:12px;
    margin-bottom:10px;
  }

  .summary-title{
    font-size:13px;
    color:#6b7280;
    margin-bottom:4px;
  }

  .summary-value{
    font-size:18px;
    font-weight:700;
  }

  .product-card img{
    height:220px;
    object-fit:contain;
    width:100%;
    background:#fff;
  }

  @media (max-width: 991.98px){
    #cartFloatWrap{
      position:static;
      top:auto;
    }
  }
</style>

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
  <label class="form-label fw-bold">Seleccionar catálogo</label>
  <select name="catalog" class="form-select">
    <option value="">-- Crear nuevo catálogo --</option>
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

const key = product.is_combo
  ? `combo-${product.id}-${pageNumber}`
  : `${code}-${color}-${pageNumber}`;

  let imgUrl = 'https://via.placeholder.com/44x44?text=Sin+foto';

  if (product.is_combo && product.image_path) {
    imgUrl = `/storage/${product.image_path}`;
  } else if (code) {
    imgUrl = color
      ? `${window.__PRODUCT_IMG_BASE__}/${encodeURIComponent(code)}/${encodeURIComponent(color)}?v=${code}${color}`
      : `${window.__PRODUCT_IMG_BASE__}/${encodeURIComponent(code)}?v=${code}`;
  }

  return `
    <div class="d-flex align-items-center gap-2 border rounded p-2 mb-2"
         data-cart-item="1"
         id="cart-item-${key}">
      <img data-src="${imgUrl}"
           class="hover-img"
           style="width:44px;height:44px;object-fit:contain;border-radius:8px;background:#fff;cursor:pointer;"
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
  onclick="${
    product.is_combo
      ? `removeCombo(${product.id})`
      : `removeFromCatalog('${code}', '${color}', ${catalogId}, ${pageNumber})`
  }">✕</button>
    </div>
  `;
}

async function removeCombo(comboId){
  if(!confirm('¿Quitar este combo del catálogo?')){
    return;
  }

  try {
    const res = await fetch(`/admin/catalogos/combos/${comboId}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });

    const data = await res.json();

    if(!res.ok || !data.ok){
      alert(data?.message || 'Error al eliminar combo');
      return;
    }

    document.querySelector(`[id^="cart-item-combo-${comboId}-"]`)?.remove();
    updateCartCount();

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
    alert('Ocurrió un error al eliminar el combo.');
  }
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

    const qtyInput = document.getElementById(`qty-${keyBase}`);
    const pageInput = document.getElementById(`page-${keyBase}`);

    const qty = qtyInput ? Number(qtyInput.value || 1) : 1;
    const pageNumber = pageInput ? Number(pageInput.value || 1) : 1;

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
    document.getElementById(`cart-item-${key}`)?.remove();

    const panel = document.getElementById('cartPanel');
    if(panel && !panel.querySelector('[data-cart-item="1"]')){
      panel.innerHTML = `
        <div class="text-muted small px-2 py-2" id="cartEmpty">
          Aún no has agregado productos.
        </div>
      `;
    }

    updateCartCount();

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

<script>
function initBulkActions() {
  const checks = document.querySelectorAll('.product-check');
  const selectedCount = document.getElementById('selectedCount');
  const selectAllBtn = document.getElementById('selectAllBtn');
  const unselectAllBtn = document.getElementById('unselectAllBtn');
  const bulkAddBtn = document.getElementById('bulkAddBtn');
  const catalogId = window.__CATALOG_ID__;

  function updateSelectedCount() {
    if (!selectedCount) return;
    const total = document.querySelectorAll('.product-check:checked').length;
    selectedCount.value = total + ' productos';
  }

  checks.forEach(chk => {
    chk.addEventListener('change', updateSelectedCount);
  });

  if (selectAllBtn) {
    selectAllBtn.onclick = function () {
      document.querySelectorAll('.product-check').forEach(chk => chk.checked = true);
      updateSelectedCount();
    };
  }

  if (unselectAllBtn) {
    unselectAllBtn.onclick = function () {
      document.querySelectorAll('.product-check').forEach(chk => chk.checked = false);
      updateSelectedCount();
    };
  }

  async function bulkAddSelected() {
    if (!catalogId) {
      alert('Primero crea o selecciona un catálogo.');
      return;
    }

    const selected = Array.from(document.querySelectorAll('.product-check:checked'));

    if (!selected.length) {
      alert('Debes seleccionar al menos un producto.');
      return;
    }

    const pageInput = document.getElementById('bulkPageNumber');
    const qtyInput = document.getElementById('bulkQuantity');

    const pageNumber = Number(pageInput?.value || 1);
    const quantity = Number(qtyInput?.value || 1);

    if (!pageNumber || pageNumber < 1) {
      alert('Debes indicar una página válida.');
      return;
    }

    if (!quantity || quantity < 1) {
      alert('Debes indicar una cantidad válida.');
      return;
    }

    if (bulkAddBtn) {
      bulkAddBtn.disabled = true;
      bulkAddBtn.textContent = 'Agregando...';
    }

    let okCount = 0;
    let failCount = 0;

    for (const chk of selected) {
      const code = (chk.dataset.code || '').trim();
      const color = (chk.dataset.color || '').trim();
      const key = (chk.dataset.key || `${code}-${color}`).trim();

      if (!code) {
        failCount++;
        continue;
      }

      try {
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
            quantity: quantity,
            page_number: pageNumber
          })
        });

        let data = null;
        const ct = res.headers.get('content-type') || '';

        if (ct.includes('application/json')) {
          data = await res.json();
        } else {
          failCount++;
          continue;
        }

        if (!res.ok || !data || !data.ok || !data.product) {
          failCount++;
          continue;
        }

        const itemKey = `${data.product.code}-${data.product.color}-${data.page_number}`;
        const panel = document.getElementById('cartPanel');

        if (panel) {
          document.getElementById('cartEmpty')?.remove();

          const existingCart = document.getElementById(`cart-item-${itemKey}`);
          if (existingCart) {
            const b = document.getElementById(`cart-qty-${itemKey}`);
            if (b) b.textContent = `${data.quantity} u`;
          } else {
            panel.insertAdjacentHTML(
              'afterbegin',
              renderCartRow(data.product, data.quantity, catalogId, data.page_number)
            );
          }
        }

        const qtyBox = document.getElementById(`qty-${key}`);
        const pageBox = document.getElementById(`page-${key}`);
        if (qtyBox) qtyBox.value = quantity;
        if (pageBox) pageBox.value = pageNumber;

        chk.checked = false;
        okCount++;

      } catch (e) {
        console.error('Error bulk add:', code, color, e);
        failCount++;
      }
    }

    updateCartCount();
    updateSelectedCount();

    if (bulkAddBtn) {
      bulkAddBtn.disabled = false;
      bulkAddBtn.textContent = 'Agregar seleccionados';
    }

    if (okCount > 0 && failCount === 0) {
      alert(`Se agregaron ${okCount} productos correctamente.`);
    } else if (okCount > 0 && failCount > 0) {
      alert(`Se agregaron ${okCount} productos y fallaron ${failCount}.`);
    } else {
      alert('No se pudo agregar ningún producto.');
    }
  }

  if (bulkAddBtn) {
    bulkAddBtn.onclick = bulkAddSelected;
  }

  updateSelectedCount();
}

document.addEventListener('DOMContentLoaded', initBulkActions);
</script>

<script>
async function loadProductsSection(url) {
  const container = document.getElementById('productsSection');
  if (!container) return;

  try {
    container.style.opacity = '0.5';
    container.innerHTML = '<div class="alert alert-info">Cargando productos...</div>';

    const res = await fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const html = await res.text();
    container.innerHTML = html;

    initBulkActions();

  } catch (err) {
    console.error(err);
    container.innerHTML = '<div class="alert alert-danger">Error al cargar productos</div>';
  } finally {
    container.style.opacity = '1';
  }
}


document.addEventListener('click', function(e){
  const link = e.target.closest('.pagination a');
  if(!link) return;

  e.preventDefault();
  loadProductsSection(link.href);
});

</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('catalogHeaderForm');
  if (!form) return;

  form.querySelectorAll('select').forEach(select => {
    select.addEventListener('change', () => {
      form.submit();
    });
  });
});
</script>

<script>
document.addEventListener('click', function(e){
  const btn = e.target.closest('#clearProductsFilter');
  if(!btn) return;

  const form = document.getElementById('productsFilterForm');
  if(!form) return;

  const pageInput = form.querySelector('input[name="filter_page"]');
  if(pageInput) pageInput.value = '';

  const url = new URL(form.action, window.location.origin);
  const formData = new FormData(form);

  for (const [key, value] of formData.entries()) {
    if (value !== '') {
      url.searchParams.set(key, value);
    }
  }

  loadProductsSection(url.toString());
});
</script>

<script>
document.addEventListener('submit', function(e){
  const form = e.target.closest('#productsFilterForm');
  if (!form) return;

  e.preventDefault();

  const url = new URL(form.action, window.location.origin);
  const formData = new FormData(form);

  for (const [key, value] of formData.entries()) {
    if (value !== null && value !== '') {
      url.searchParams.set(key, value);
    }
  }

  loadProductsSection(url.toString());
});
</script>

<script>
document.addEventListener('mouseover', function(e){
  const img = e.target.closest('.hover-img');
  if(!img) return;

  if (!img.dataset.loaded) {
    img.src = img.dataset.src;
    img.dataset.loaded = '1';
  }
});
</script>
@endsection