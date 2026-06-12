@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <h2 class="fw-bold mb-4">Editar premio al contado</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Revisa los campos:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('admin.premios.update', [
                'codigo' => trim($premio->CODOFERTA),
                'mes' => $premio->MESOPE,
            ]) }}"
            method="POST" enctype="multipart/form-data" class="card card-body shadow-sm">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Código</label>
                    <input type="text" class="form-control" value="{{ $premio->CODTPRODUCTO }}" disabled>
                </div>

                <div class="col-md-5 mb-3">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="Descripcion" class="form-control"
                        value="{{ old('Descripcion', $premio->DESCRIP_PREMIO) }}">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Mes</label>
                    <input type="text" class="form-control" value="{{ $premio->MESOPE }}" disabled>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Código producto premio</label>
                    <input type="text" name="Codprod" class="form-control"
                        value="{{ old('Codprod', $premio->CODOFERTA) }}">
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">Cantidad</label>
                    <input type="number" name="Cantidad" class="form-control"
                        value="{{ old('Cantidad', $premio->VALORMIN) }}">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Desde</label>
                    <input type="number" step="0.01" name="Desde" class="form-control"
                        value="{{ old('Desde', $premio->VALORMIN) }}">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Hasta</label>
                    <input type="number" step="0.01" name="Hasta" class="form-control"
                        value="{{ old('Hasta', $premio->VALORMAX) }}">
                </div>

                <div class="col-md-6 mb-3">
    <label class="form-label">Foto pública del premio</label>
    <input type="file" name="foto_publica" class="form-control" accept="image/*">
</div>

@if(!empty($premio->foto_publica))
    <div class="col-md-6 mb-3">
        <label class="form-label">Foto actual</label>
        <div>
            <img src="{{ asset('storage/' . $premio->foto_publica) }}"
                 style="width: 160px; height: 160px; object-fit: contain; background: #fff; border-radius: 12px;">
        </div>
    </div>
@endif
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-success">
                    Actualizar premio
                </button>

                <a href="{{ route('admin.premios.index', ['mes' => $premio->MESOPE]) }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
@endsection
