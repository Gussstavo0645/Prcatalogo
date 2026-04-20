@extends('layouts.public')

@section('content')
<section class="about-page py-5">
    <div class="container">

        <div class="text-center mb-5">
            <a href="{{ route('catalogs.index') }}">
                <img src="{{ asset('imagenes/LOGO.png') }}" alt="Marle" class="log">
            </a>
            <h1 class="about-title mt-4">En marlen lamur</h1>
            <p class="about-subtitle">
                Comprometidos con la belleza, la calidad y el bienestar de nuestros clientes.
            </p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="about-card">
                    <h4>Nuestra Misión</h4>
                    <h2>"Tu oportunidad de crecimiento con el respaldo de la mejor calidad."</h2>
                    <img src="{{ asset('imagenes/MISION1.png') }}" class="mission-img">
                    <p>
                        Somos una empresa reconocida por sus productos de alta calidad y excelente servicio,
                        cuya finalidad es generar fuentes de ingresos a los hogares guatemaltecos a través
                        de la venta de una amplia variedad de cosméticos, artículos de higiene y perfumería
                        fina a precios competitivos.
                    </p>
                  
                </div>
                
            </div>

            <div class="col-12 col-lg-10">
                <div class="about-card">
                    <h4>Visión</h4>
                    <p>
                        Ser una empresa que contribuye con la prosperidad económica e independencia de la sociedad,
                        brindando productos y servicio de excelente calidad, con la finalidad de alcanzar la
                        satisfacción y la preferencia de nuestros consumidores finales, consultores y colaboradores.
                    </p>
                </div>
            </div>

              <div class="col-12 col-lg-10">
                <div class="about-card">
                    <h4>Nuestra Trayectoria</h4>
                    <p>
                       "Somos Marlen Lamur, una empresa con más de 50 años de herencia y pasión en el mundo de la belleza.
                        Nacimos con una visión y un corazón profundamente guatemalteco, dedicándonos a transformar materias primas de calidad mundial en herramientas de progreso.
                         Más que fabricantes de cosméticos, somos una comunidad que impulsa el liderazgo femenino, brindando a miles de familias la oportunidad de construir su propio destino a través 
                         del emprendimiento y la confianza."
                    </p>
                    <div class="video-preview">
    <a href="https://www.youtube.com/watch?v=15S8axaDLMc" target="_blank">
        <img src="https://img.youtube.com/vi/15S8axaDLMc/hqdefault.jpg" alt="Video Marlen Lamur">
        <span class="play-btn">▶</span>
    </a>
</div>
            </div>
        </div>
        </div>

      

    </div>
</section>
@endsection