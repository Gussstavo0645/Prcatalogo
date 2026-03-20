@extends('layouts.app')

@php
  //  solo verifica si hay paginas blob sin traer binario
  $hasBlobPages = $catalog->paginas()
      ->whereNotNull('archivo_binario')
      ->exists();

  //  trae solo columnas seguras no archivos binarios
  $pages = $catalog->paginas()
      ->select('id','catalog_id','page_number','mime') // agrega las que uses
      ->whereNotNull('archivo_binario')
      ->orderBy('page_number')
      ->get();

 $productosPorPagina = $productosPorPagina ?? collect();

  $total = $hasBlobPages ? $pages->count() : $productosPorPagina->count();
@endphp

@section('content')
<style>
  #flipbook-wrap{display:grid;place-items:center;padding:16px;margin-bottom:32px;}
  #flipbook{ width: 920px; height: 600px; overflow:hidden; position:relative; }
  #flipbook { cursor: grab; }
#flipbook:active { cursor: grabbing; }
  #flipbook .page{
    width:460px; height:600px; background:#fff;
    position:relative; padding:0 !important; /* pagina full */
    overflow:hidden;
    
  }

  .products-overlay,
.product-mini,
.product-mini button{
  touch-action: manipulation;
}

  .flip-controls{display:flex;gap:12px;justify-content:center;align-items:center;margin-top:10px;}

  /* imagen full */
  .page-img{
  position:absolute;
  inset:0;
  width:100%;
  height:100%;
  object-fit:cover;
  object-position:center;
  display:block;
  pointer-events:none;
}

/* grif 3X3 */
.products-overlay{
  position:absolute;
  left:10px;
  right:10px;
  top:50px;
  bottom:10px;
  z-index:5;

  display:grid;
  grid-template-columns:repeat(3, 1fr);
  grid-template-rows:repeat(3, 1fr);  /*  fuerza 3 filas */
  gap:10px;

  align-content:stretch;
  overflow:hidden;
}

/* card */
.product-mini{
  height:auto;              /*  deja que el grid mande */
  min-height:0;
  display:flex;
  flex-direction:column;
  background:rgba(255,255,255,.92);
  border-radius:12px;
  padding:8px;
  box-shadow:0 4px 10px rgba(0,0,0,.22);
  overflow:hidden;          /*  mejor que visible */
}

/* imagen */
.product-mini img{
  width:100%;
  height:80px;
  object-fit:contain;
  border-radius:20px;
  background:#fff;
  display:block;
}

.p-code{
  min-height: 12px;
  font-size:15px;
  line-height:1;
  color:#D203DD;
  margin-top:5px;
  font-weight: 700
}

/* texto del nombre en el mini card (panel y overlay) */
.p-name{

  font-size: 11px !important;
  font-weight: 700 !important;
  line-height: 1 !important;
   margin-top:2px;
  min-height:22px;

  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow:hidden;
}

/* precio */
.p-price{
  font-size: 15px !important;
  line-height: 1.1 !important;
}


/*  boton azul abajo siempre */
.product-mini .p-qty{
  margin-top:auto;         /* lo empuja al fondo */
}

.product-mini button.badge{
  height: 20px;        /* alto */
  font-size: 12px;     /* letra */
  padding: 3px 0;      /* espaciointerno */
  border-radius: 10px;  /* bordes */
}

.page-badge{
  position:absolute;
  pointer-events: none;
  top:8px;
  left:8px;
  z-index:6;
  background: rgba(0,0,0,.45);
  color:#fff;
  padding:4px 8px;
  border-radius:10px;
  font-size:12px;
}
.page .page-badge{ opacity: 0; }
.page.is-visible .page-badge{ opacity: 1; }

.cart-fab{
  position: fixed;
  right: 18px;
  bottom: 18px;
  z-index: 9999;
  background: #0d6efd;
  color: #fff;
  border-radius: 999px;
  padding: 12px 14px;
  cursor: pointer;
  box-shadow: 0 10px 25px rgba(0,0,0,.25);
  user-select: none;
  display:flex;
  align-items:center;
  gap:10px;
  font-weight: 700;
}

.cart-count{
  background:#fff;
  color:#0d6efd;
  border-radius:999px;
  padding:2px 8px;
  font-size:12px;
}

.cart-panel{
  position: fixed;
  right: 18px;
  bottom: 70px;
  width: 320px;
  height: 70vh; 
  max-height: 70vh;
  z-index: 9999;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 15px 35px rgba(0,0,0,.28);
  overflow: hidden;
  border: 1px solid rgba(0,0,0,.08);
    display: flex;             
  flex-direction: column;
}

.cart-head, .cart-foot{
  padding: 12px;
  background: #f8f9fa;
  border-bottom: 1px solid rgba(0,0,0,.06);
}
.cart-foot{ 
  flex: 0 0 auto;            /* footer fijo */
  padding: 12px;
  background: #f8f9fa;
  border-top: 1px solid rgba(0,0,0,.06);
  border-bottom: none; 
  }

.cart-items{
  padding: 10px;
  overflow:auto;
  max-height: 45vh;
   flex: 1 1 auto;
}

.cart-item{
  display:flex;
  gap:10px;
  border: 1px solid rgba(0,0,0,.08);
  border-radius: 12px;
  padding: 8px;
  margin-bottom: 10px;
  align-items:center;
}

.cart-item img{
  width: 48px;
  height: 48px;
  border-radius: 10px;
  object-fit: cover;
  background:#f3f3f3;
}

.cart-item .meta{
  flex:1;
  min-width:0;
}

.cart-item .name{
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
  overflow:hidden;
  text-overflow: ellipsis;
}

.cart-item .sub{
  font-size: 12px;
  color:#666;
}

.cart-item .qty{
  display:flex;
  align-items:center;
  gap:6px;
}

.cart-item button{
  white-space: nowrap;
}
</style>



<div class="container py-3">
  <h3 class="mb-1">{{ $catalog->title }}</h3>

  @if(!empty($catalog->description))
    <p class="text-muted">{{ $catalog->description }}</p>
  @endif

  {{--<div class="text-muted small">
    Páginas BLOB: {{ $pages->count() }} |
    Productos: {{ $productsFallback->count() }}
  </div>--}}
</div>

<div id="flipbook-wrap">
  <div id="flipbook">

  {{-- si hay paginas blob --}}
@if($hasBlobPages)

  @foreach($pages as $pagina)
    @php
      $pageNum = (int) $pagina->page_number;
      $items = $productosPorPagina[$pageNum] ?? collect();
    @endphp

    <div class="page {{ $pageNum === 1 ? 'page-cover' : '' }}"
         data-density="{{ $pageNum === 1 ? 'hard' : 'soft' }}">

      <div class="page-badge">Página {{ $pageNum }}</div>

      {{-- imagen de pagina completa --}}
      <img
        src="{{ route('catalog_pages.image', $pagina->id) }}"
        class="page-img"
        alt="Página {{ $pageNum }}"
      >

      {{-- productos encima de la hoja --}}
    @if($items->count() > 0)
  <div class="products-overlay">
    @foreach($items as $prod)
      @php
        $img = route('catalog.product.image', [
            'code' => trim((string) $prod->code),
            'color' => trim((string) ($prod->color ?? ''))
        ]);
      @endphp

      <div class="product-mini">
        <img
          src="{{ $img }}"
          alt="{{ $prod->name }}"
       {{--   style="width:100%;height:56px;object-fit:contain;border-radius:7px;display:block;background:#fff;"--}}
          onerror="this.onerror=null;this.src='https://via.placeholder.com/300x200?text=Sin+foto';"
        />

        <div class="p-code">Código: {{ $prod->display_code ?? $prod->code }}</div>
        <div class="p-name">{{ $prod->name }}</div>
        <div class="p-price">Q {{ number_format($prod->price, 2) }}</div>

        <button
          type="button"
          class="badge bg-primary mt-1 border-0 w-100 p-2"
          onclick="addToCart({
            id: '{{ $prod->code }}-{{ $prod->color }}',
            code: '{{ $prod->code }}',
            color: '{{ $prod->color }}',
            name: @js($prod->name),
            price: {{ (float) $prod->price }},
            qty: {{ (int) ($prod->quantity ?? 1) }},
            img: '{{ $img }}'
          })"
        >
          {{ $prod->quantity ?? 1 }} AGREGAR
        </button>
      </div>
    @endforeach
  </div>
@endif

    </div>
  @endforeach

    {{-- vacio --}}
    @else
      <div class="page p-3 d-flex align-items-center justify-content-center">
        <div class="alert alert-info m-0">
          Este catálogo aún no tiene páginas ni productos.
        </div>
      </div>
    @endif

  </div>

  <div class="flip-controls">
    <button id="prev" class="btn btn-outline-secondary">⟵ Anterior</button>
    <span id="page-indicator" class="text-muted small">1 / {{ max(1, $total) }}</span>
    <button id="next" class="btn btn-outline-secondary">Siguiente ⟶</button>
  </div>
</div>

{{-- ======= CARRITO UI ======= --}}
<div id="cartFab" class="cart-fab" onclick="toggleCart()">
  <span id="cartCountFab" class="cart-count">0</span>
  Carrito
</div>

<div id="cartPanel" class="cart-panel d-none">
  <div class="cart-head d-flex justify-content-between align-items-center">
    <div class="fw-semibold">Carrito</div>
    <button class="btn btn-sm btn-outline-secondary" onclick="toggleCart()">Cerrar</button>
  </div>

  <div id="cartItems" class="cart-items"></div>

  <div class="cart-foot">
    <div class="d-flex justify-content-between">
      <span class="text-muted">Total</span>
      <strong id="cartTotal">Q 0.00</strong>
    </div>

    <button class="btn btn-danger w-100 mt-2" onclick="clearCart()">Vaciar Carrito</button>
    <button class="btn btn-primary w-100 mt-2" onclick="checkout()">Ir a pagar</button>
  </div>
</div>

{{-- ======= MODAL WIZARD 3 PASOS ======= --}}
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Finalizar compra</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">

        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="step-pill" data-step="1"><span class="step-num">1</span> Información</div>
          <div class="step-line flex-grow-1"></div>
          <div class="step-pill" data-step="2"><span class="step-num">2</span> Entrega</div>
          <div class="step-line flex-grow-1"></div>
          <div class="step-pill" data-step="3"><span class="step-num">3</span> Pago</div>
        </div>

        <style>
          .step-pill{display:flex;align-items:center;gap:8px;font-weight:700;color:#6c757d}
          .step-num{width:26px;height:26px;border-radius:999px;display:grid;place-items:center;border:2px solid #ced4da;color:#6c757d;font-size:13px}
          .step-pill.active{color:#0d6efd}
          .step-pill.active .step-num{border-color:#0d6efd;background:#0d6efd;color:#fff}
          .step-pill.done{color:#198754}
          .step-pill.done .step-num{border-color:#198754;background:#198754;color:#fff}
          .step-line{height:2px;background:#e9ecef}
          .wizard-step{display:none}
          .wizard-step.active{display:block}
        </style>

        {{-- STEP 1 --}}
        <div class="wizard-step active" id="step1">
          <h6 class="mb-3">Información del cliente</h6>
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Nombre completo *</label>
              <input type="text" class="form-control" id="cliNombre" placeholder="Nombre y apellido">
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono / WhatsApp *</label>
              <input type="text" class="form-control" id="cliTelefono" placeholder="Ej: 5555-5555">
            </div>
            <div class="col-12">
              <label class="form-label">Correo (opcional)</label>
              <input type="email" class="form-control" id="cliCorreo" placeholder="correo@ejemplo.com">
            </div>
          </div>
        </div>

        <script>
document.addEventListener("DOMContentLoaded", function(){

  const campos = [
    document.getElementById("cliNombre"),
    document.getElementById("cliTelefono"),
    document.getElementById("cliCorreo"),
    document.getElementById("btnNext")
  ];

  campos.forEach((campo, index) => {
    campo.addEventListener("keydown", function(e){
      if(e.key === "Enter"){
        e.preventDefault();

        if(campos[index + 1]){
          campos[index + 1].focus();
        }

        else{
          btnNext.click();
        }
      }
    });
  });

});
</script>


        {{-- STEP 2 --}}
        <div class="wizard-step" id="step2">
          <h6 class="mb-3">Información de entrega</h6>
          <div class="row g-2">
            <div class="col-12">
              <label class="form-label">Dirección *</label>
              <input type="text" class="form-control" id="entDireccion" placeholder="Zona, colonia, calle, referencia">
            </div>
            <div class="col-md-6">
              <label class="form-label">Ciudad *</label>
              <input type="text" class="form-control" id="entCiudad" value="Guatemala">
            </div>
            <div class="col-md-6">
              <label class="form-label">Tipo de entrega *</label>
              <select class="form-select" id="entTipo">
                <option value="envio" selected>Envío a domicilio</option>
                <option value="recoger">Recoger en tienda</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Notas (opcional)</label>
              <textarea class="form-control" id="entNotas" rows="2" placeholder="Horario, color, referencia, etc."></textarea>
            </div>
          </div>
        </div>

         <script>
document.addEventListener("DOMContentLoaded", function(){

  const campos = [
    document.getElementById("entDireccion"),
    document.getElementById("entCiudad"),
    document.getElementById("entTipo"),
    document.getElementById("entNotas"),
    document.getElementById("btnNext")
  ];

  campos.forEach((campo, index) => {
    campo.addEventListener("keydown", function(e){
      if(e.key === "Enter"){
        e.preventDefault();

        if(campos[index + 1]){
          campos[index + 1].focus();
        }

        else{
          
          btnNext.click();
        }
      }
    });
  });

});
</script>

        {{-- STEP 3 --}}
        <div class="wizard-step" id="step3">
          <h6 class="mb-3">Método de pago</h6>
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Método *</label>
              <select class="form-select" id="pagoMetodo">
                <option value="efectivo" selected>Efectivo</option>
                <option value="transferencia">Transferencia</option>
                <option value="tarjeta">Tarjeta</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">¿Desea factura? *</label>
              <select class="form-select" id="pagoFactura">
                <option value="no" selected>No</option>
                <option value="si">Sí</option>
              </select>
            </div>

            <div class="col-12 mt-2">
              <div class="alert alert-light border mb-0">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Total</span>
                  <strong id="wizardTotal">Q 0.00</strong>
                </div>
                <div class="small text-muted">Al confirmar se creará tu pedido.</div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-outline-secondary" id="btnBack">Atrás</button>
        <button type="button" class="btn btn-primary" id="btnNext">Siguiente</button>
        <button type="button" class="btn btn-success d-none" id="btnConfirm">Confirmar pedido</button>
      </div>

    </div>
  </div>
</div>

@endsection

@section('scripts')

<script>
const CART_KEY = 'flipbook_cart_v1';

function getCart(){
  try {
    return JSON.parse(localStorage.getItem(CART_KEY) || '[]');
  } catch(e){
    return [];
  }
}

function setCart(items){
  localStorage.setItem(CART_KEY, JSON.stringify(items));
  renderCart();
}

function clearCart(){
  setCart([]);
}

function toggleCart(){
  document.getElementById('cartPanel')?.classList.toggle('d-none');
}

function openCart(){
  document.getElementById('cartPanel')?.classList.remove('d-none');
}

function addToCart(product) {
  let cart = getCart();

  const idx = cart.findIndex(item =>
    item.code === product.code && String(item.color) === String(product.color)
  );

  if (idx >= 0) {
    cart[idx].qty += Number(product.qty || 1);
  } else {
    cart.push({
      id: product.id,
      code: product.code,
      color: product.color,
      name: product.name,
      price: Number(product.price),
      qty: Number(product.qty || 1),
      img: product.img
    });
  }

  setCart(cart);
}

function removeFromCart(id){
  setCart(getCart().filter(x => x.id !== id));
}

function removeCartItem(index) {
  let cart = getCart();
  cart.splice(index, 1);
  setCart(cart);
}

function changeQty(id, delta){
  const cart = getCart();
  const it = cart.find(x => x.id === id);
  if(!it) return;
  it.qty = Math.max(1, (it.qty || 1) + delta);
  setCart(cart);
}

function renderCart() {
  const cart = getCart();
  const wrap = document.getElementById('cartItems');
  const totalEl = document.getElementById('cartTotal');

  if (!wrap) return;

  if (cart.length === 0) {
    wrap.innerHTML = '<p class="text-muted mb-0">Tu carrito está vacío.</p>';
    if (totalEl) totalEl.textContent = 'Q 0.00';
    return;
  }

  let total = 0;

  wrap.innerHTML = cart.map((item, index) => {
    const subtotal = Number(item.price) * Number(item.qty);
    total += subtotal;

    return `
      <div class="cart-item d-flex gap-2 align-items-center mb-2 p-2 border rounded">
        <img src="${item.img}" alt="${item.name}" style="width:60px;height:60px;object-fit:contain;background:#fff;border-radius:8px;">
        <div class="flex-grow-1">
          <div class="fw-bold">${item.name}</div>
          <div class="small text-muted">Código: ${item.code} | Color: ${item.color}</div>
          <div class="small">Q ${Number(item.price).toFixed(2)} x ${item.qty}</div>
          <div class="fw-semibold">Subtotal: Q ${subtotal.toFixed(2)}</div>
        </div>
        <button type="button" class="btn btn-sm btn-danger" onclick="removeCartItem(${index})">X</button>
      </div>
    `;
  }).join('');

  if (totalEl) totalEl.textContent = 'Q ' + total.toFixed(2);
}

/* ==========================
   WIZARD 3 PASOS
========================== */
let wizardStep = 1;
let checkoutModalInstance = null;

function wizardSetHeader(step){
  document.querySelectorAll('.step-pill').forEach(p => {
    const s = Number(p.dataset.step);
    p.classList.remove('active','done');
    if(s < step) p.classList.add('done');
    if(s === step) p.classList.add('active');
  });
}

function wizardShowStep(step){
  wizardStep = step;
  document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
  document.getElementById('step'+step)?.classList.add('active');

  const btnBack = document.getElementById('btnBack');
  const btnNext = document.getElementById('btnNext');
  const btnConfirm = document.getElementById('btnConfirm');

  if(btnBack) btnBack.style.display = (step === 1) ? 'none' : '';
  if(btnNext) btnNext.classList.toggle('d-none', step === 3);
  if(btnConfirm) btnConfirm.classList.toggle('d-none', step !== 3);

  wizardSetHeader(step);
}

function wizardTotal(){
  const cart = getCart();
  const total = cart.reduce((sum, x) => sum + (Number(x.price)||0) * (x.qty||1), 0);
  const el = document.getElementById('wizardTotal');
  if(el) el.textContent = 'Q ' + total.toFixed(2);
  return total;
}

function openCheckoutWizard(){
  const cart = getCart();
  if(cart.length === 0){
    alert('Tu carrito está vacío.');
    return;
  }
  wizardShowStep(1);
  wizardTotal();
  const modalEl = document.getElementById('checkoutModal');
  checkoutModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
  checkoutModalInstance.show();
}

function checkout(){
  openCheckoutWizard();
}

function validateStep1(){
  const nombre = (document.getElementById('cliNombre')?.value || '').trim();
  const tel = (document.getElementById('cliTelefono')?.value || '').trim();
  if(!nombre){ alert('Nombre requerido'); return false; }
  if(!tel){ alert('Teléfono requerido'); return false; }
  return true;
}

function validateStep2(){
  const dir = (document.getElementById('entDireccion')?.value || '').trim();
  const ciudad = (document.getElementById('entCiudad')?.value || '').trim();
  const tipo = (document.getElementById('entTipo')?.value || '').trim();
  if(!dir){ alert('Dirección requerida'); return false; }
  if(!ciudad){ alert('Ciudad requerida'); return false; }
  if(!tipo){ alert('Tipo de entrega requerido'); return false; }
  return true;
}

function validateStep3(){
  const metodo = (document.getElementById('pagoMetodo')?.value || '').trim();
  const factura = (document.getElementById('pagoFactura')?.value || '').trim();
  if(!metodo){ alert('Método de pago requerido'); return false; }
  if(!factura){ alert('Factura: seleccioná Sí o No'); return false; }
  return true;
}

/* ==========================
   PAGEFLIP INIT + LOCK OVERLAY
========================== */
(function () {
  const root = document.getElementById('flipbook');
  if (!root) return;

  const pages = root.querySelectorAll('.page');

  if (typeof St === 'undefined' || !St.PageFlip) {
    console.error('PageFlip NO está cargado. Revisa los <script> del layout.');
    return;
  }

  const pageFlip = new St.PageFlip(root, {
    width: 460,
    height: 600,
    size: 'fixed',
    showCover: true,
    startPage: 0,
    useShadow: true,
    maxShadowOpacity: 0.2,
    flippingTime: 800,
    mobileScrollSupport: true,
  });

  pageFlip.loadFromHTML(pages);

  function lockFlipOnOverlay(selector){
    root.querySelectorAll(selector).forEach((el) => {
      const stopMouse = (e) => e.stopPropagation();
      const stopTouchMove = (e) => {
        e.stopPropagation();
        if (e.cancelable) e.preventDefault();
      };

      el.addEventListener('mousedown', stopMouse, { capture:true });
      el.addEventListener('mousemove', stopMouse, { capture:true });
      el.addEventListener('mouseup', stopMouse, { capture:true });

      el.addEventListener('pointerdown', stopMouse, { capture:true });
      el.addEventListener('pointermove', stopTouchMove, { capture:true, passive:false });
      el.addEventListener('pointerup', stopMouse, { capture:true });

      el.addEventListener('touchstart', stopMouse, { capture:true, passive:false });
      el.addEventListener('touchmove', stopTouchMove, { capture:true, passive:false });
      el.addEventListener('touchend', stopMouse, { capture:true });
    });
  }

  lockFlipOnOverlay('.products-overlay');
  lockFlipOnOverlay('.product-mini');

  const prev = document.getElementById('prev');
  const next = document.getElementById('next');
  const indicator = document.getElementById('page-indicator');

  if (prev) prev.addEventListener('click', () => pageFlip.flipPrev());
  if (next) next.addEventListener('click', () => pageFlip.flipNext());

  const update = () => {
    if (indicator) {
      indicator.textContent = (pageFlip.getCurrentPageIndex() + 1) + ' / ' + pageFlip.getPageCount();
    }

    const idx = pageFlip.getCurrentPageIndex();
    pages.forEach(p => p.classList.remove('is-visible'));
    if (pages[idx]) pages[idx].classList.add('is-visible');
    if (pages[idx + 1]) pages[idx + 1].classList.add('is-visible');
  };

  update();
  pageFlip.on('init', update);
  pageFlip.on('flip', update);
})();

/* ==========================
   DOM READY (listeners)
========================== */
document.addEventListener('DOMContentLoaded', () => {
  renderCart();

  document.getElementById('btnNext')?.addEventListener('click', () => {
    if(wizardStep === 1 && !validateStep1()) return;
    if(wizardStep === 2 && !validateStep2()) return;
    wizardShowStep(Math.min(3, wizardStep + 1));
  });

  document.getElementById('btnBack')?.addEventListener('click', () => {
    wizardShowStep(Math.max(1, wizardStep - 1));
  });

  document.getElementById('btnConfirm')?.addEventListener('click', async () => {
    if(!validateStep3()) return;

    const cart = getCart();

    const items = cart.map(x => ({
      code: x.code,
      color: x.color,
      quantity: x.qty || 1,
      name: x.name,
      price: x.price
    }));

    const nombre_cliente   = (document.getElementById('cliNombre')?.value || '').trim();
    const telefono_cliente = (document.getElementById('cliTelefono')?.value || '').trim();
    const correo           = (document.getElementById('cliCorreo')?.value || '').trim();

    const direccion        = (document.getElementById('entDireccion')?.value || '').trim();
    const ciudad           = (document.getElementById('entCiudad')?.value || '').trim();
    const entrega_tipo     = (document.getElementById('entTipo')?.value || '').trim();
    const notas            = (document.getElementById('entNotas')?.value || '').trim();

    const pago_metodo      = (document.getElementById('pagoMetodo')?.value || '').trim();
    const requiere_factura = (document.getElementById('pagoFactura')?.value || '').trim();

    try{
      const res = await fetch("/pedido/finalizar", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
          nombre_cliente,
          telefono_cliente,
          correo,
          direccion,
          ciudad,
          entrega_tipo,
          notas,
          pago_metodo,
          requiere_factura,
          items
        })
      });

      const raw = await res.text();

      if(!res.ok){
        console.log('STATUS:', res.status);
        console.log('RESPUESTA:', raw);
        alert(`Error (${res.status}). Mirá consola (F12).`);
        return;
      }

      const data = JSON.parse(raw);
      clearCart();

      if(checkoutModalInstance) checkoutModalInstance.hide();

      alert(`Pedido #${data.pedido_id} creado. Total: Q ${Number(data.total).toFixed(2)}`);
    } catch(e){
      console.error(e);
      alert('Error de conexión. Mirá consola (F12).');
    }
  });
});
</script>
@endsection
