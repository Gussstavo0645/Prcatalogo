@extends('layouts.public')

@section('content')
<div class="catalog-home">

    <!-- HERO -->
    <section class="catalog-hero" id="tour-bienvenida">
        <img src="{{ asset('imagenes/LOGO1.png') }}" alt="Marlen Lamur" class="hero-logo">

        <div class="catalog-hero-content">

            <span class="brand-badge">Belleza y cuidado personal</span>

            <h1 class="catalog-title">Bienvenidos</h1>

            <p class="catalog-subtitle">
                Descubre nuestras colecciones, promociones y novedades en una experiencia digital moderna.
            </p>

        <div class="hero-actions">
    <a href="{{ route('catalogo.quisomos') }}" class="hero-btn hero-btn-glass">
        <i class="bi bi-stars"></i>
        Quiénes somos
    </a>

    <a href="{{ route('login') }}" class="hero-btn hero-btn-glass">
        <i class="bi bi-person-circle"></i>
        Iniciar sesión
    </a>

    <button type="button" id="btnTourInicio" class="hero-btn hero-btn-main">
        <i class="bi bi-bag-heart"></i>
        ¿Cómo comprar?
    </button>

    @auth
        <a href="{{ route('admin.catalogs.create') }}" class="hero-btn hero-btn-panel">
            <i class="bi bi-grid-1x2-fill"></i>
            Ir al panel
        </a>
    @endauth
</div>

<div class="hero-socials hero-socials-premium" id="tour-redes">
    <span class="hero-socials-text">Síguenos</span>

    <a href="https://www.tiktok.com/@marlenlamurgt?_r=1&_t=ZS-96IrgluU5Y8"
       target="_blank"
       class="hero-social-btn"
       aria-label="TikTok">
        <i class="bi bi-tiktok"></i>
    </a>

    <a href="https://www.facebook.com/marlenlamurgt#"
       target="_blank"
       class="hero-social-btn"
       aria-label="Facebook">
        <i class="bi bi-facebook"></i>
    </a>

    <a href="https://www.instagram.com/marlenlamurgt/"
       target="_blank"
       class="hero-social-btn"
       aria-label="Instagram">
        <i class="bi bi-instagram"></i>
    </a>
</div>

</div>
    </section>
        
<div class="home-premium-row">
    <!-- SECCIÓN PREMIUM DE COLECCIONES DESTACADAS -->
    <section class="featured-showcase">

        <div class="featured-top">
            <div>
                <span class="featured-kicker">Marlen Lamur</span>
                <h2>Descubre nuestras colecciones</h2>
            </div>

            <div class="featured-tabs">
                <button type="button" class="featured-tab active" data-slide="0">
                    Perfumes
                </button>

                <button type="button" class="featured-tab" data-slide="1">
                    Maquillaje
                </button>

                <button type="button" class="featured-tab" data-slide="2">
                    Cuidado
                </button>
            </div>
        </div>

        <div class="featured-scene">

            <div class="featured-orb featured-orb-left"></div>
            <div class="featured-orb featured-orb-right"></div>

            <!-- TEXTO -->
<div class="featured-copy">
    <span id="featuredCategory" class="featured-category">
        PERFUMERÍA
    </span>

    <h3 id="featuredTitle" class="featured-title">
        Fragancias para cada estilo
    </h3>

    <div class="featured-line"></div>

    <p id="featuredDescription" class="featured-description">
        Descubre perfumes para hombre y mujer, con aromas únicos que acompañan cada momento y dejan huella.
    </p>

    <a href="#catalogos-disponibles" class="featured-btn">
        Ver catálogo
        <i class="bi bi-arrow-right"></i>
    </a>
</div>

            <!-- PRODUCTOS -->
            <div class="featured-products">

                <img
                    id="featuredMainImage"
                    src="{{ asset('imagenes/morton1.png') }}"
                    alt="Producto destacado"
                    class="featured-main-img"
                >

                <img
                    id="featuredFloatOne"
                    src="{{ asset('imagenes/force1.png') }}"
                    alt="Producto decorativo"
                    class="featured-float-img float-one"
                >

                <img
                    id="featuredFloatTwo"
                    src="{{ asset('imagenes/cuidado3.png') }}"
                    alt="Producto decorativo"
                    class="featured-float-img float-two"
                >

            </div>
        </div>

        <!-- NAVEGACIÓN -->
        <div class="featured-navigation">

            <button type="button" id="featuredPrev" class="featured-nav-btn">
                <i class="bi bi-arrow-left"></i>
            </button>

            <div class="featured-dots">
                <button type="button" class="featured-dot active" data-slide="0"></button>
                <button type="button" class="featured-dot" data-slide="1"></button>
                <button type="button" class="featured-dot" data-slide="2"></button>
            </div>

            <button type="button" id="featuredNext" class="featured-nav-btn">
                <i class="bi bi-arrow-right"></i>
            </button>

        </div>

    </section>
        <!-- PREMIOS AL CONTADO - TARJETA LATERAL -->
    <aside class="cash-rewards-side">

        <span class="cash-rewards-side-kicker">
            MARLEN LAMUR
        </span>

        <h2>Premios al contado</h2>

<p>
    Compra al contado, acumula durante el mes vigente y alcanza recompensas especiales.
</p>

 <div class="cash-rewards-showcase">

    <div class="reward-premium-card reward-premium-main">
        <span class="reward-tag">Premio 1</span>

        <div class="reward-image-stage">
            <img src="{{ asset('imagenes/PREMI1.png') }}"
     class="reward-zoom-img"
     data-full="{{ asset('imagenes/PREMI1.png') }}">
                
        </div>

        <div class="reward-info">
            <strong>Acumula Q425</strong>
            <span>Obtén este premio al alcanzar un acumulado de Q425.00 en compras al contado.</span>
        </div>
    </div>

    <div class="reward-premium-card reward-premium-secondary">
        <span class="reward-tag">Premio 2</span>

        <div class="reward-image-stage">
            <img src="{{ asset('imagenes/PREMI2.png') }}"
     
     class="reward-zoom-img"
     data-full="{{ asset('imagenes/PREMI2.png') }}">
                 
        </div>

        <div class="reward-info">
            <strong>Acumula Q725</strong>
            <span>Al llegar a Q725.00 acumulados en compras al contado, podrás optar a este premio.</span>
        </div>
    </div>

</div>

   <div class="cash-rewards-mini-list">
    <div>
        <i class="bi bi-check-circle-fill"></i>
        Aplica únicamente en compras al contado
    </div>

    <div>
        <i class="bi bi-check-circle-fill"></i>
        El acumulado cuenta solo dentro del mes vigente
    </div>

    <div>
        <i class="bi bi-check-circle-fill"></i>
        Canjea el premio disponible según tu acumulado
    </div>
</div>

        <a href="#catalogos-disponibles" class="cash-rewards-side-btn">
            Ver catálogos
            <i class="bi bi-arrow-right"></i>
        </a>

    </aside>

</div>

    <!-- LISTADO -->
    <section class="catalog-list-wrap" id="catalogos-disponibles">

        <div class="section-head" id="tour-catalogos-disponibles">
            <h2>Catálogos disponibles</h2>
            <p>Explora nuestras líneas de productos.</p>
        </div>

        <div class="catalog-grid">
            @forelse($catalogos as $c)
              <a href="{{ route('catalog.public', $c->slug) }}"
   class="catalog-card-pro catalog-banner-pro"
   @if($loop->first) id="tour-primer-catalogo" @endif>

    <div class="catalog-card-overlay"></div>

    <div class="catalog-banner-content">

        <div class="catalog-banner-left">
            <div class="catalog-icon">
                <i class="bi bi-journal-richtext"></i>
            </div>

            <div class="catalog-banner-text">
                <span class="catalog-mini-label">Catálogo disponible</span>

                <h3>{{ $c->title }}</h3>

                <p>
                    {{ \Illuminate\Support\Str::limit($c->description, 120) }}
                </p>
            </div>
        </div>

        <span class="catalog-open"
              @if($loop->first) id="tour-ver-catalogo" @endif>
            Ver catálogo
            <i class="bi bi-arrow-right"></i>
        </span>

    </div>
</a>
            @empty
                <div class="empty-state">
                    <i class="bi bi-folder2-open"></i>
                    <h3>No hay catálogos disponibles</h3>
                    <p>Pronto aparecerán nuevos catálogos aquí.</p>
                </div>
            @endforelse
        </div>
    </section>

</div>

<!-- MODAL PARA AMPLIAR PREMIOS -->
<div id="imgModal" aria-hidden="true">
    <div class="img-modal-box">
        <button type="button" class="img-close" aria-label="Cerrar">&times;</button>

        <img id="imgModalSrc" src="" alt="Premio ampliado">
    </div>
</div>
@endsection


@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const driver = window.driver.js.driver;

    const pasosTour = [
        {
            element: '#tour-bienvenida',
            popover: {
                title: '¡Bienvenido a Marlen Lamur!',
                description: 'Aquí inicia la experiencia del catálogo digital. Desde esta página podrás consultar los catálogos disponibles y comenzar tu compra.'
            }
        },
        {
            element: '#tour-catalogos-disponibles',
            popover: {
                title: 'Catálogos disponibles',
                description: 'En esta sección encontrarás los catálogos activos para explorar productos y promociones.'
            }
        }
    ];

    // Solo agregamos estos pasos si existe al menos un catálogo visible
    if (document.querySelector('#tour-primer-catalogo')) {
        pasosTour.push(
            {
                element: '#tour-primer-catalogo',
                popover: {
                    title: 'Selecciona un catálogo',
                    description: 'Presiona sobre esta tarjeta para ingresar al catálogo digital.'
                }
            },
            {
                element: '#tour-ver-catalogo',
                popover: {
                    title: 'Abrir catálogo',
                    description: 'Desde aquí podrás ver productos, elegir una tienda, agregar al carrito y realizar tu pedido.'
                }
            }
        );
    }

    pasosTour.push(
        {
            element: '#tour-redes',
            popover: {
                title: 'Redes sociales',
                description: 'También puedes seguirnos en TikTok, Facebook e Instagram para ver novedades y promociones.'
            }
        },
        {
            element: '#tour-whatsapp',
            popover: {
                title: 'Contacto por WhatsApp',
                description: 'Presiona este botón para escribirnos directamente por WhatsApp.'
            }
        },
        {
            element: '#tour-telefono',
            popover: {
                title: 'Llamada directa',
                description: 'También puedes comunicarte con nosotros por teléfono.'
            }
        }
    );

    const tourInicio = driver({
        showProgress: true,
        animate: true,
        smoothScroll: true,
        nextBtnText: 'Siguiente',
        prevBtnText: 'Atrás',
        doneBtnText: 'Finalizar',
        progressText: '@{{current}} de @{{total}}',
        steps: pasosTour,
        onDestroyed: () => {
            localStorage.setItem('tour_inicio_visto', '1');
        }
    });

    // Mostrar automáticamente solo la primera vez
    if (!localStorage.getItem('tour_inicio_visto')) {
        setTimeout(() => {
            tourInicio.drive();
        }, 900);
    }

    // Botón para volver a mostrar el recorrido
    const btnTourInicio = document.getElementById('btnTourInicio');

    if (btnTourInicio) {
        btnTourInicio.addEventListener('click', function () {
            tourInicio.drive();
        });
    }

});
</script>




<script>
document.addEventListener('DOMContentLoaded', function () {
    const showcase = document.querySelector('.featured-showcase');

    if (!showcase) return;

    const slides = [
        {
    category: 'PERFUMERÍA',
    title: 'Fragancias para cada estilo',
    description: 'Descubre perfumes para hombre y mujer, con aromas únicos que acompañan cada momento y dejan huella.',
    main: "{{ asset('imagenes/morton1.png') }}",
    floatOne: "{{ asset('imagenes/force1.png') }}",
    floatTwo: "{{ asset('imagenes/midnight1.png') }}"
},
        {
            category: 'MAQUILLAJE',
            title: 'Color que resalta tu estilo',
            description: 'Explora tonos, acabados y esenciales que elevan cada look.',
            main: "{{ asset('imagenes/mac1.png') }}",
            floatOne: "{{ asset('imagenes/mac2.png') }}",
            floatTwo: "{{ asset('imagenes/mac3.png') }}"
        },
        {
            category: 'CUIDADO PERSONAL',
            title: 'Belleza que acompaña tu rutina',
            description: 'Productos pensados para cuidar, consentir y mantener tu piel radiante.',
            main: "{{ asset('imagenes/cuidado3.png') }}",
            floatOne: "{{ asset('imagenes/cuidado1.png') }}",
            floatTwo: "{{ asset('imagenes/cuidado2.png') }}"
        }
    ];

    let currentSlide = 0;
    let sliderTimer;

    const category = document.getElementById('featuredCategory');
    const title = document.getElementById('featuredTitle');
    const description = document.getElementById('featuredDescription');
    const mainImage = document.getElementById('featuredMainImage');
    const floatOne = document.getElementById('featuredFloatOne');
    const floatTwo = document.getElementById('featuredFloatTwo');

    const tabs = document.querySelectorAll('.featured-tab');
    const dots = document.querySelectorAll('.featured-dot');
    const prevBtn = document.getElementById('featuredPrev');
    const nextBtn = document.getElementById('featuredNext');

    function updateActiveControls(index) {
        tabs.forEach((tab, i) => {
            tab.classList.toggle('active', i === index);
        });

        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
    }

    function renderSlide(index) {
        showcase.classList.add('is-changing');

        setTimeout(() => {
            const slide = slides[index];

            category.textContent = slide.category;
            title.textContent = slide.title;
            description.textContent = slide.description;

            mainImage.src = slide.main;
            floatOne.src = slide.floatOne;
            floatTwo.src = slide.floatTwo;

            updateActiveControls(index);

            showcase.classList.remove('is-changing');
        }, 320);
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        renderSlide(currentSlide);
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        renderSlide(currentSlide);
    }

    function restartAutoplay() {
        clearInterval(sliderTimer);
        sliderTimer = setInterval(nextSlide, 5000);
    }

    nextBtn.addEventListener('click', function () {
        nextSlide();
        restartAutoplay();
    });

    prevBtn.addEventListener('click', function () {
        prevSlide();
        restartAutoplay();
    });

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', function () {
            currentSlide = index;
            renderSlide(currentSlide);
            restartAutoplay();
        });
    });

    dots.forEach((dot, index) => {
        dot.addEventListener('click', function () {
            currentSlide = index;
            renderSlide(currentSlide);
            restartAutoplay();
        });
    });

    restartAutoplay();
});
</script>
@endsection