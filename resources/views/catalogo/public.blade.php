@extends('layouts.public')

@php
    $pagesRender = collect($pagesRender ?? []);
    $hasBlobPages = $pagesRender->count() > 0;
    $total = $hasBlobPages ? $pagesRender->count() : 0;
    $initialTake = 6;
@endphp

@section('content')
    <div class="catalog-body ">

        
        {{-- =====================================================
     CABECERA PROFESIONAL DEL CATÁLOGO
===================================================== --}}
        <section class="catalog-commerce-head">

            <div class="commerce-head-top">

    <div class="commerce-brand">

      <a href="{{ url('/') }}" class="btn-volver-catalogos">
    <i class="bi bi-arrow-left"></i>
    Volver al inicio
</a>

        <span class="commerce-kicker">
            Catálogo digital
        </span>

                    <div class="commerce-title-line">
                        <h3 id="tour-titulo-catalogo" class="commerce-title">
                            {{ $catalog->title }}
                        </h3>

                        @if (!empty($catalog->description))
                            <span class="commerce-period">
                                {{ $catalog->description }}
                            </span>
                        @endif
                    </div>

                    <p class="commerce-caption">
                        Selecciona tu tienda, consulta tu acumulado y realiza tu pedido directamente desde el catálogo.
                    </p>
                </div>

                <button type="button" id="btnTourCatalogo" class="commerce-help-btn">
                    <i class="bi bi-question-circle"></i>
                    ¿Cómo comprar?
                </button>
            </div>

            <div class="commerce-controls">

                {{-- TIENDA --}}
                <div class="commerce-control commerce-store-control" id="tour-seleccionar-tienda">
                    <label class="commerce-label">
                        <i class="bi bi-shop"></i>
                        Tienda que atenderá tu pedido
                    </label>

                    <select id="store_id" class="form-select commerce-select" required>
                        <option value="">Seleccionar tienda</option>

                        @foreach ($catalog->tiendas as $tienda)
                            <option value="{{ $tienda->id }}" data-whatsapp="{{ $tienda->whatsapp_number }}">
                                {{ $tienda->name }}
                            </option>
                        @endforeach
                    </select>

                    <input type="hidden" id="catalog_id" value="{{ $catalog->id }}">
                </div>


                {{-- ACUMULADO --}}
                <div class="commerce-control commerce-rewards-control" id="tour-acumulado">
                    <label class="commerce-label">
                        <i class="bi bi-gift-fill"></i>
                        Consulta tu acumulado del mes
                    </label>

                    <div class="commerce-rewards-form">
                        <input type="text" id="consultaCodCliente" class="form-control commerce-rewards-input"
                            placeholder="Ej: 1-11, OFZONA10">

                        <button type="button" class="commerce-rewards-btn" id="btnConsultarAcumulado">
                            Consultar
                        </button>
                    </div>

                </div>

            </div>

        </section>

        {{-- =====================================================
     MODAL PREMIUM DE ACUMULADO
===================================================== --}}
        <div class="modal fade" id="modalAcumulado" tabindex="-1" aria-labelledby="modalAcumuladoLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content acumulado-modal-content">

                    <div class="modal-header acumulado-modal-header">
                        <div>
                            <span class="acumulado-modal-kicker">Premios del mes</span>
                            <h5 class="modal-title" id="modalAcumuladoLabel">
                                🎁 Acumulado y opciones de canje
                            </h5>
                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body acumulado-modal-body">
                        <div id="resultadoAcumuladoModal">
                            <div class="text-center py-4 text-muted">
                                Ingresa tu código y presiona consultar.
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer acumulado-modal-footer">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">
                            Cerrar
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <div id="flipbook-wrap" data-tour="catalogo">
            <button id="btnExitFullscreen" type="button" class="btn-exit-fullscreen">
  ✕
</button>
  <button id="btnFullscreen" class="fullscreen-btn">
                ⛶
            </button>
            <div id="flipbook"
     data-slug="{{ $catalog->slug }}"
     data-total="{{ $total }}"
     data-loaded="{{ min($initialTake, $total) }}"
     data-mesyope="{{ $catalog->mesyope }}"
     data-tipocatalogo="{{ $catalog->tipocatalogo }}">

                @if ($hasBlobPages)
                    @foreach ($pagesRender->take($initialTake) as $renderPage)
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
          
            <button type="button" id="fsPrev" class="fs-page-arrow fs-page-arrow-left">
    ‹
</button>

<button type="button" id="fsNext" class="fs-page-arrow fs-page-arrow-right">
    ›
</button>
            <div class="flip-controls" id="tour-controles-paginas">
                <button type="button" id="prev" class="btn btn-outline-secondary">
                    ⟵ Anterior
                </button>

                <span id="page-indicator" class="text-muted small">
                    1 / {{ max(1, $total) }}
                </span>

                <button type="button" id="next" class="btn btn-outline-secondary">
                    Siguiente ⟶
                </button>
            </div>

           

        </div>
        </div> {{-- cierre de flipbook-wrap --}}

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
    <div class="img-modal-box quick-view-box">

        <button type="button" class="img-close" id="imgModalClose">&times;</button>

        <div class="quick-view-image">
            <img id="imgModalSrc" src="" alt="Producto">
        </div>

        <div class="quick-view-info">
            <span class="quick-code" id="quickCode">Código</span>

            <h3 id="quickName">Nombre del producto</h3>

            <strong class="quick-price" id="quickPrice">Q 0.00</strong>
            
            <div id="quickRating" class="quick-rating"></div>

            <div class="quick-qty-box">
                <span>Cantidad</span>

                <div class="quick-qty-controls">
                    <button type="button" id="quickQtyMinus">-</button>
                    <strong id="quickQtyValue">1</strong>
                    <button type="button" id="quickQtyPlus">+</button>
                </div>
            </div>

            <button type="button" class="quick-add-btn" id="quickAddBtn">
                AGREGAR AL CARRITO
            </button>
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
                            .step-pill {
                                display: flex;
                                align-items: center;
                                gap: 8px;
                                font-weight: 700;
                                color: #6c757d
                            }

                            .step-num {
                                width: 26px;
                                height: 26px;
                                border-radius: 999px;
                                display: grid;
                                place-items: center;
                                border: 2px solid #ced4da;
                                color: #6c757d;
                                font-size: 13px
                            }

                            .step-pill.active {
                                color: #0d6efd
                            }

                            .step-pill.active .step-num {
                                border-color: #0d6efd;
                                background: #0d6efd;
                                color: #fff
                            }

                            .step-pill.done {
                                color: #198754
                            }

                            .step-pill.done .step-num {
                                border-color: #198754;
                                background: #198754;
                                color: #fff
                            }

                            .step-line {
                                height: 2px;
                                background: #e9ecef
                            }

                            .wizard-step {
                                display: none
                            }

                            .wizard-step.active {
                                display: block
                            }
                        </style>

                        {{-- STEP 1 --}}
                        <div class="wizard-step active" id="step1">
                            <h6 class="mb-3">Información del cliente</h6>

                            <div class="row g-2">

                                <div class="col-md-5">
                                    <label class="form-label">Código de cliente *</label>

                                    <div class="input-group">
                                        <input type="text" class="form-control" id="cliCodCliente"
                                            placeholder="Ingrese su código">
                                        <button type="button" class="btn btn-primary" id="btnBuscarCliente">
                                            Buscar
                                        </button>
                                    </div>

                                    <small id="clienteStatus" class="text-muted d-block">
                                        Ingrese su código para continuar.
                                    </small>

                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="chkClienteNoInscrito">
                                        <label class="form-check-label" for="chkClienteNoInscrito">
                                            Cliente no inscrito
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-7">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="cliNombre"
                                        placeholder="Nombre completo" >
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Teléfono / WhatsApp *</label>
                                    <input type="text" class="form-control" id="cliTelefono"
                                        placeholder="Ej: 5555-5555" disabled>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">NIT</label>
                                    <input type="text" class="form-control" id="cliNit" placeholder="CF o NIT"
                                        disabled>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">DPI</label>
                                    <input type="text" class="form-control" id="cliDpi" placeholder="DPI"
                                        disabled>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Correo opcional</label>
                                    <input type="email" class="form-control" id="cliCorreo"
                                        placeholder="correo@ejemplo.com" disabled>
                                </div>

                            </div>
                        </div>

                        {{-- STEP 2 --}}
                        <div class="wizard-step" id="step2">
                            <h6 class="mb-3">Información de entrega</h6>
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label">Dirección *</label>
                                    <input type="text" class="form-control" id="entDireccion"
                                        placeholder="Zona, colonia, calle, referencia">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ciudad *</label>
                                    <input type="text" class="form-control" id="entCiudad" value="Guatemala">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de entrega *</label>
                                    <select class="form-select" id="entTipo">
                                        <option value="recoger">Recoger en tienda</option>
                                        <option value="envio" selected> </option>
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
    <option value="neopay">Pagar en línea con tarjeta NeoPay</option>
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
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button type="button" class="btn btn-outline-secondary" id="btnBack">
                            Atrás
                        </button>

                        <button type="button" class="btn btn-primary" id="btnNext">
                            Siguiente
                        </button>

                        <button type="button" class="btn btn-success d-none" id="btnConfirm">
                            Confirmar pedido
                        </button>
                    </div>

                </div>
            </div>
        </div>



    @endsection

    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const driver = window.driver.js.driver;

                const tourCatalogo = driver({
                    showProgress: true,
                    animate: true,
                    smoothScroll: true,
                    nextBtnText: 'Siguiente',
                    prevBtnText: 'Atrás',
                    doneBtnText: 'Finalizar',
                    progressText: '@{{ current }} de @{{ total }}',
                    popoverClass: "driverjs-theme",
                    stagePadding: 4,

                    steps: [{
                            element: '#tour-titulo-catalogo',
                            popover: {

                                title: 'Catálogo digital',
                                description: 'Aquí estás viendo el catálogo disponible del mes. Sigue estos pasos para realizar tu pedido.'
                            }
                        },
                        {
                            element: '#tour-seleccionar-tienda',
                            popover: {

                                title: '1. Selecciona tu tienda',
                                description: 'Antes de comprar, elige la tienda que atenderá tu pedido y recibirá el resumen por WhatsApp.'
                            }
                        },
                        {
                            element: '#tour-acumulado',
                            popover: {

                                title: '2. Consulta tus premios',
                                description: 'Si eres cliente inscrito, ingresa tu código para revisar cuánto llevas acumulado este mes y qué premio puedes alcanzar.'
                            }
                        },
                        {
                            element: '#flipbook-wrap',
                            popover: {

                                title: '3. Explora el catálogo',
                                description: 'Aquí verás los productos. Puedes presionar una imagen para ampliarla y usar el botón Agregar para enviarla al carrito.'
                            }
                        },
                        {
                            element: '#tour-controles-paginas',
                            popover: {

                                title: '4. Cambia de página',
                                description: 'Usa estos botones para avanzar o regresar dentro del catálogo.'
                            }
                        },
                        {
                            element: '#btnFullscreen',
                            popover: {

                                title: '5. Ver en pantalla completa',
                                description: 'Este botón amplía el catálogo para que puedas verlo con mayor comodidad.'
                            }
                        },
                        {
                            element: '#cartFab',
                            popover: {

                                title: '6. Revisa tu carrito',
                                description: 'Aquí aparecerán los productos que agregues. Presiónalo para revisar tu compra y avanzar al pago.'
                            }
                        },
                        {
                            element: '#tour-whatsapp',
                            popover: {

                                title: '7. Contacto por WhatsApp',
                                description: 'También puedes escribir directamente por WhatsApp si necesitas ayuda.'
                            }
                        }
                    ],

                    onDestroyed: () => {
                        localStorage.setItem('tour_catalogo_visto', '1');
                    }
                });

                // Mostrar automáticamente solo la primera vez
                if (!localStorage.getItem('tour_catalogo_visto')) {
                    setTimeout(() => {
                        tourCatalogo.drive();
                    }, 1000);
                }

                // Volver a ver el tour con el botón
                const btnTourCatalogo = document.getElementById('btnTourCatalogo');

                if (btnTourCatalogo) {
                    btnTourCatalogo.addEventListener('click', function() {
                        tourCatalogo.drive();
                    });
                }

            });
        </script>
    @endsection
