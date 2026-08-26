@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Subir catálogo PDF</h1>
            <p class="text-muted mb-0">
                Publica un nuevo catálogo para visualizarlo y descargarlo.
            </p>
        </div>

        <a href="{{ route('admin.catalogos-pdf.index') }}"
           class="btn btn-outline-light">
            Regresar
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>No se pudo guardar el catálogo:</strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <form
                action="{{ route('admin.catalogos-pdf.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="row g-4">

                    <div class="col-md-8">
                        <label for="titulo" class="form-label">
                            Título del catálogo *
                        </label>

                        <input
                            type="text"
                            name="titulo"
                            id="titulo"
                            value="{{ old('titulo') }}"
                            class="form-control @error('titulo') is-invalid @enderror"
                            placeholder="Ejemplo: Marlen Lamur Agosto 2026"
                            required
                        >

                        @error('titulo')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="catalog_id" class="form-label">
                            Catálogo digital relacionado
                        </label>

                        <select
                            name="catalog_id"
                            id="catalog_id"
                            class="form-select @error('catalog_id') is-invalid @enderror"
                        >
                            <option value="">
                                Ninguno
                            </option>

                            @foreach($catalogosDigitales as $catalogoDigital)
                                <option
                                    value="{{ $catalogoDigital->id }}"
                                    @selected(
                                        old('catalog_id') == $catalogoDigital->id
                                    )
                                >
                                    #{{ $catalogoDigital->id }}
                                    - {{ $catalogoDigital->title }}
                                </option>
                            @endforeach
                        </select>

                        @error('catalog_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="descripcion" class="form-label">
                            Descripción
                        </label>

                        <textarea
                            name="descripcion"
                            id="descripcion"
                            rows="4"
                            class="form-control @error('descripcion') is-invalid @enderror"
                            placeholder="Escribe una descripción breve del catálogo..."
                        >{{ old('descripcion') }}</textarea>

                        @error('descripcion')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="mes" class="form-label">
                            Mes *
                        </label>

                        <select
                            name="mes"
                            id="mes"
                            class="form-select @error('mes') is-invalid @enderror"
                            required
                        >
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

                            @foreach($meses as $numero => $nombre)
                                <option
                                    value="{{ $numero }}"
                                    @selected(
                                        old('mes', now()->month) == $numero
                                    )
                                >
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>

                        @error('mes')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="anio" class="form-label">
                            Año *
                        </label>

                        <input
                            type="number"
                            name="anio"
                            id="anio"
                            value="{{ old('anio', now()->year) }}"
                            min="2020"
                            max="{{ now()->year + 2 }}"
                            class="form-control @error('anio') is-invalid @enderror"
                            required
                        >

                        @error('anio')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="numero_paginas" class="form-label">
                            Número de páginas
                        </label>

                        <input
                            type="number"
                            name="numero_paginas"
                            id="numero_paginas"
                            value="{{ old('numero_paginas') }}"
                            min="1"
                            max="2000"
                            class="form-control @error('numero_paginas') is-invalid @enderror"
                            placeholder="Ejemplo: 100"
                        >

                        @error('numero_paginas')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="portada" class="form-label">
                            Imagen de portada *
                        </label>

                        <input
                            type="file"
                            name="portada"
                            id="portada"
                            accept=".jpg,.jpeg,.png,.webp,image/*"
                            class="form-control @error('portada') is-invalid @enderror"
                            required
                        >

                        <div class="form-text">
                            Formatos JPG, PNG o WEBP. Máximo 10 MB.
                        </div>

                        @error('portada')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="archivo_pdf" class="form-label">
                            Archivo PDF *
                        </label>

                        <input
                            type="file"
                            name="archivo_pdf"
                            id="archivo_pdf"
                            accept=".pdf,application/pdf"
                            class="form-control @error('archivo_pdf') is-invalid @enderror"
                            required
                        >

                        <div class="form-text">
                            Solo archivos PDF. Máximo 100 MB.
                        </div>

                        @error('archivo_pdf')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="orden" class="form-label">
                            Orden
                        </label>

                        <input
                            type="number"
                            name="orden"
                            id="orden"
                            value="{{ old('orden', 0) }}"
                            min="0"
                            class="form-control @error('orden') is-invalid @enderror"
                        >
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="destacado" value="0">

                            <input
                                type="checkbox"
                                name="destacado"
                                id="destacado"
                                value="1"
                                class="form-check-input"
                                @checked(old('destacado'))
                            >

                            <label for="destacado" class="form-check-label">
                                Catálogo destacado
                            </label>
                        </div>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="activo" value="0">

                            <input
                                type="checkbox"
                                name="activo"
                                id="activo"
                                value="1"
                                class="form-check-input"
                                @checked(old('activo', true))
                            >

                            <label for="activo" class="form-check-label">
                                Publicar inmediatamente
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a
                                href="{{ route('admin.catalogos-pdf.index') }}"
                                class="btn btn-outline-secondary"
                            >
                                Cancelar
                            </a>

                            <button type="submit" class="btn btn-primary">
                                Publicar catálogo PDF
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection