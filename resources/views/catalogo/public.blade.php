@extends('layouts.public')

@php
  $pagesRender = collect($pagesRender ?? []);
  $hasBlobPages = $pagesRender->count() > 0;
  $total = $hasBlobPages ? $pagesRender->count() : 0;
  $initialTake = 6;
@endphp

@section('content')
<div class="catalog-body ">

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
  <div id="flipbook"
       data-slug="{{ $catalog->slug }}"
       data-total="{{ $total }}"
       data-loaded="{{ min($initialTake, $total) }}">

    @if($hasBlobPages)
      @foreach($pagesRender->take($initialTake) as $renderPage)
        @include('catalogo.parcial.pagina', ['renderPage' => $renderPage])
      @endforeach
    @else
      <div class="page p-3 d-flex align-items-center justify-content-center">
        <div class="alert alert-info m-0">
          Este catálogo aún no tiene páginas ni productos.
        </div>
      </div>
    @endif

  </div>
<button id="btnFullscreen" class="fullscreen-btn">
  ⛶
</button>
 <div class="flip-controls">
  <button id="prev" class="btn btn-outline-secondary">⟵ Anterior</button>
  <span id="page-indicator" class="text-muted small">1 / {{ max(1, $total) }}</span>
  <button id="next" class="btn btn-outline-secondary">Siguiente ⟶</button>
</div>

<div id="cartFab" class="cart-fab">
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
    <div class="d-flex justify-content-between align-items-center mb-2">
      <strong>Total: <span id="cartTotal">Q 0.00</span></strong>
    </div>

    <div class="d-grid gap-2">
      <button type="button" class="btn btn-success" onclick="checkout()">
        Ir a pagar
      </button>

      <button type="button" class="btn btn-outline-danger" onclick="clearCart()">
        Vaciar carrito
      </button>
    </div>
  </div>
</div>

<div id="imgModal">
  <div class="img-modal-box">
    <button type="button" class="img-close" id="imgModalClose">&times;</button>
    <img id="imgModalSrc" alt="Zoom producto">
  </div>
</div>
</div>


{{-- ======= CARRITO UI ======= --}}

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

{{--ZOOM DE IMAGEN--}}

@endsection

  
