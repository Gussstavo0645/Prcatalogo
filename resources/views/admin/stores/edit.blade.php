@extends('layouts.app')

@section('content')
<div class="container py-4">

  <h3 class="mb-4">Editar tienda</h3>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card shadow-sm">
    <div class="card-body">

      <form action="{{ route('admin.stores.update', ['store' => $store->getKey()]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">

          <div class="col-md-6">
            <label class="form-label fw-bold">Nombre *</label>
            <input type="text" name="Nombodega" class="form-control" value="{{ $store->Nombodega }}" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Ubicación</label>
            <input type="text" name="ubicacion" class="form-control" value="{{ $store->ubicacion }}">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Código</label>
            <input type="text" name="Codigo" class="form-control" value="{{ $store->Codigo ?? '' }}">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Color</label>
            <input type="text" name="color" class="form-control" value="{{ $store->color ?? '' }}">
          </div>

        </div>

        <div class="mt-4 d-flex gap-2">
          <button class="btn btn-success">Actualizar</button>

          <a href="{{ route('admin.stores.index') }}" class="btn btn-secondary">
            Cancelar
          </a>
        </div>

      </form>

    </div>
  </div>

</div>
@endsection