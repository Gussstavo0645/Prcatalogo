@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="mb-1">Catálogos PDF</h1>
            <p class="text-muted mb-0">
                Administra los catálogos disponibles para visualizar y descargar.
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('catalogos-pdf.index') }}"
               class="btn btn-outline-dark"
               target="_blank">
                Ver módulo público
            </a>

            <a href="{{ route('admin.catalogos-pdf.create') }}"
               class="btn btn-primary">
                Subir catálogo PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Cerrar">
            </button>
        </div>
    @endif

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

    @if($catalogos->count())
        <div class="row g-4">
            @foreach($catalogos as $catalogo)
                <div class="col-sm-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">

                        <div class="position-relative bg-light text-center">
                            <img
                                src="{{ asset('storage/' . $catalogo->portada) }}"
                                alt="Portada de {{ $catalogo->titulo }}"
                                class="card-img-top"
                                style="height: 360px; object-fit: contain;"
                            >

                            <div class="position-absolute top-0 start-0 p-2">
                                @if($catalogo->destacado)
                                    <span class="badge bg-warning text-dark">
                                        Destacado
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column">

                            <h5 class="card-title mb-1">
                                {{ $catalogo->titulo }}
                            </h5>

                            <div class="text-muted small mb-3">
                                {{ $meses[$catalogo->mes] ?? $catalogo->mes }}
                                {{ $catalogo->anio }}

                                @if($catalogo->numero_paginas)
                                    · {{ $catalogo->numero_paginas }} páginas
                                @endif
                            </div>

                            <p class="card-text text-muted">
                                {{ \Illuminate\Support\Str::limit(
                                    $catalogo->descripcion,
                                    130
                                ) }}
                            </p>

                            <div class="mb-3">
                                @if($catalogo->activo)
                                    <span class="badge bg-success">
                                        Publicado
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        Oculto
                                    </span>
                                @endif
                            </div>

                            <div class="d-flex gap-2 flex-wrap mt-auto">

                                @if($catalogo->activo)
                                    <a
                                        href="{{ route(
                                            'catalogos-pdf.ver',
                                            $catalogo
                                        ) }}"
                                        class="btn btn-dark btn-sm"
                                        target="_blank"
                                    >
                                        Ver PDF
                                    </a>
                                @endif

                                <a
                                    href="{{ route(
                                        'admin.catalogos-pdf.edit',
                                        $catalogo
                                    ) }}"
                                    class="btn btn-warning btn-sm"
                                >
                                    Editar
                                </a>

                                <form
                                    action="{{ route(
                                        'admin.catalogos-pdf.destroy',
                                        $catalogo
                                    ) }}"
                                    method="POST"
                                    onsubmit="return confirm(
                                        '¿Seguro que deseas eliminar este catálogo PDF?'
                                    )"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-outline-danger btn-sm"
                                    >
                                        Eliminar
                                    </button>
                                </form>

                            </div>
                        </ </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $catalogos->links() }}
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <h4>No hay catálogos PDF</h4>

                <p class="text-muted">
                    Todavía no se ha publicado ningún catálogo descargable.
                </p>

                <a
                    href="{{ route('admin.catalogos-pdf.create') }}"
                    class="btn btn-primary"
                >
                    Subir el primer catálogo
                </a>
            </div>
        </div>
    @endif

</div>
@endsection