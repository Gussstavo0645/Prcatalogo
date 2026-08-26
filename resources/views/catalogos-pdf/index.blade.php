@extends('layouts.public')

@section('content')
@php
    $meses = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];
@endphp

<style>
    .pdf-public-page {
        min-height: 100vh;
        padding: 70px 20px;
        background:
            radial-gradient(circle at 15% 15%, rgba(227, 38, 154, .13), transparent 30%),
            radial-gradient(circle at 85% 20%, rgba(112, 70, 255, .14), transparent 34%),
            #f7f5fc;
        color: #211d2b;
    }

    .pdf-container {
        width: min(1180px, 100%);
        margin: 0 auto;
    }

    .pdf-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 28px;
        color: #5e5670;
        font-weight: 700;
        text-decoration: none;
    }

    .pdf-back:hover {
        color: #d92391;
    }

    .pdf-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(300px, .85fr);
        align-items: center;
        gap: 70px;
        padding: 58px;
        border: 1px solid rgba(91, 69, 132, .12);
        border-radius: 32px;
        background: rgba(255, 255, 255, .82);
        box-shadow: 0 24px 70px rgba(48, 32, 79, .12);
        backdrop-filter: blur(12px);
    }

    .pdf-badge {
        display: inline-flex;
        margin-bottom: 18px;
        padding: 9px 15px;
        border-radius: 999px;
        background: rgba(217, 35, 145, .1);
        color: #c51c81;
        font-size: .85rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .pdf-title {
        margin: 0 0 20px;
        font-size: clamp(2.4rem, 5vw, 4.6rem);
        line-height: 1.02;
        font-weight: 900;
        color: #211d2b;
    }

    .pdf-description {
        max-width: 670px;
        margin-bottom: 20px;
        color: #625b70;
        font-size: 1.1rem;
        line-height: 1.75;
    }

    .pdf-meta {
        margin-bottom: 28px;
        color: #7b728b;
        font-weight: 700;
    }

    .pdf-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pdf-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 50px;
        padding: 12px 23px;
        border-radius: 13px;
        font-weight: 800;
        text-decoration: none;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .pdf-button:hover {
        transform: translateY(-2px);
    }

    .pdf-button-primary {
        color: white;
        background: linear-gradient(135deg, #db218f, #7149ff);
        box-shadow: 0 12px 28px rgba(151, 48, 204, .25);
    }

    .pdf-button-primary:hover {
        color: white;
    }

    .pdf-button-secondary {
        color: #31283f;
        border: 1px solid rgba(73, 56, 102, .2);
        background: white;
    }

    .pdf-button-secondary:hover {
        color: #d92391;
    }

    .pdf-cover-wrap {
        position: relative;
        display: flex;
        justify-content: center;
    }

    .pdf-cover-glow {
        position: absolute;
        inset: 12% 6%;
        border-radius: 40%;
        background: linear-gradient(135deg, #ff45ab, #7149ff);
        filter: blur(45px);
        opacity: .22;
    }

    .pdf-cover {
        position: relative;
        width: min(100%, 390px);
        max-height: 560px;
        border-radius: 20px;
        object-fit: contain;
        background: white;
        box-shadow: 0 25px 55px rgba(36, 25, 59, .24);
    }

    .pdf-history {
        padding-top: 75px;
    }

    .pdf-history-heading {
        margin-bottom: 30px;
    }

    .pdf-history-heading h2 {
        margin-bottom: 8px;
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 900;
    }

    .pdf-history-heading p {
        color: #71687e;
    }

    .pdf-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 26px;
    }

    .pdf-card {
        overflow: hidden;
        border: 1px solid rgba(91, 69, 132, .12);
        border-radius: 22px;
        background: white;
        box-shadow: 0 16px 42px rgba(48, 32, 79, .1);
    }

    .pdf-card-image {
        width: 100%;
        height: 390px;
        object-fit: contain;
        background: #f1eef7;
    }

    .pdf-card-body {
        padding: 24px;
    }

    .pdf-card-date {
        margin-bottom: 8px;
        color: #cf2088;
        font-size: .82rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .pdf-card-title {
        margin-bottom: 18px;
        font-size: 1.25rem;
        font-weight: 800;
    }

    .pdf-empty {
        padding: 70px 25px;
        border-radius: 26px;
        background: white;
        text-align: center;
        box-shadow: 0 20px 55px rgba(48, 32, 79, .1);
    }

    @media (max-width: 900px) {
        .pdf-hero {
            grid-template-columns: 1fr;
            gap: 45px;
            padding: 36px;
        }

        .pdf-cover-wrap {
            order: -1;
        }

        .pdf-cover {
            max-height: 480px;
        }

        .pdf-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .pdf-public-page {
            padding: 35px 14px;
        }

        .pdf-hero {
            padding: 25px 18px;
            border-radius: 22px;
        }

        .pdf-actions,
        .pdf-button {
            width: 100%;
        }

        .pdf-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="pdf-public-page">
    <div class="pdf-container">

        <a href="{{ route('catalogs.index') }}" class="pdf-back">
            ← Regresar a los catálogos
        </a>

        @if($destacado)
            <section class="pdf-hero">
                <div>
                    <span class="pdf-badge">
                        Catálogo mensual
                    </span>

                    <h1 class="pdf-title">
                        {{ $destacado->titulo }}
                    </h1>

                    <p class="pdf-description">
                        {{ $destacado->descripcion
                            ?: 'Explora nuestro catálogo mensual y descubre productos, promociones y novedades disponibles para ti.' }}
                    </p>

                    <div class="pdf-meta">
                        {{ $meses[$destacado->mes] ?? $destacado->mes }}
                        {{ $destacado->anio }}

                        @if($destacado->numero_paginas)
                            · {{ $destacado->numero_paginas }} páginas
                        @endif
                    </div>

                    <div class="pdf-actions">
                        <a
                            href="{{ route('catalogos-pdf.ver', $destacado) }}"
                            class="pdf-button pdf-button-primary"
                            target="_blank"
                            rel="noopener"
                        >
                            Ver catálogo PDF
                        </a>

                        <a
                            href="{{ route('catalogos-pdf.descargar', $destacado) }}"
                            class="pdf-button pdf-button-secondary"
                        >
                            Descargar PDF
                        </a>
                    </div>
                </div>

                <div class="pdf-cover-wrap">
                    <div class="pdf-cover-glow"></div>

                    <img
                        src="{{ asset('storage/' . $destacado->portada) }}"
                        alt="Portada de {{ $destacado->titulo }}"
                        class="pdf-cover"
                    >
                </div>
            </section>

            @if($anteriores->count())
                <section class="pdf-history">
                    <div class="pdf-history-heading">
                        <h2>Catálogos anteriores</h2>
                        <p>
                            Consulta y descarga nuestras ediciones anteriores.
                        </p>
                    </div>

                    <div class="pdf-grid">
                        @foreach($anteriores as $catalogo)
                            <article class="pdf-card">
                                <img
                                    src="{{ asset('storage/' . $catalogo->portada) }}"
                                    alt="Portada de {{ $catalogo->titulo }}"
                                    class="pdf-card-image"
                                >

                                <div class="pdf-card-body">
                                    <div class="pdf-card-date">
                                        {{ $meses[$catalogo->mes] ?? $catalogo->mes }}
                                        {{ $catalogo->anio }}
                                    </div>

                                    <h3 class="pdf-card-title">
                                        {{ $catalogo->titulo }}
                                    </h3>

                                    <div class="pdf-actions">
                                        <a
                                            href="{{ route('catalogos-pdf.ver', $catalogo) }}"
                                            class="pdf-button pdf-button-primary"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            Ver PDF
                                        </a>

                                        <a
                                            href="{{ route('catalogos-pdf.descargar', $catalogo) }}"
                                            class="pdf-button pdf-button-secondary"
                                        >
                                            Descargar
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @else
            <div class="pdf-empty">
                <h1>Catálogos PDF</h1>
                <p class="text-muted">
                    Próximamente encontrarás aquí nuestros catálogos descargables.
                </p>

                <a
                    href="{{ route('catalogs.index') }}"
                    class="pdf-button pdf-button-primary"
                >
                    Regresar
                </a>
            </div>
        @endif

    </div>
</div>
@endsection