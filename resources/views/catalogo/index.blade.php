@extends('layouts.public')

@section('content')
<div class="catalog-home">

    <!-- HERO -->
<section class="catalog-hero">
   <img src="{{ asset('imagenes/LOGO1.png') }}" alt="Marlen Lamur" class="hero-logo">
    <div class="catalog-hero-content">
        
        <span class="brand-badge">Belleza y cuidado personal</span>
        <h1 class="catalog-title">Bienvenidos</h1>
        <p class="catalog-subtitle">
            Descubre nuestras colecciones, promociones y novedades en una experiencia digital moderna.
        </p>

        <div class="mt-3">
    <a href="{{ route('catalogo.quisomos') }}" class="btn btn-light">
        Quiénes somos
    </a>
</div>
    </div>
</section>

    <!-- LISTADO -->
    <section class="catalog-list-wrap">
        <div class="section-head">
            <h2>Catálogos disponibles</h2>
            <p>Explora nuestras líneas de productos.</p>
        </div>

        <div class="catalog-grid">
            @forelse($catalogos as $c)
                <a href="{{ route('catalog.public', $c->slug) }}" class="catalog-card-pro">

                    <div class="catalog-card-overlay"></div>

                    <div class="catalog-card-body">

                        <div class="catalog-icon">
                            <i class="bi bi-journal-richtext"></i>
                        </div>

                        <h3>{{ $c->title }}</h3>

                        <p>
                            {{ \Illuminate\Support\Str::limit($c->description, 120) }}
                        </p>

                        <span class="catalog-open">
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
@endsection