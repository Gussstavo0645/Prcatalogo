@extends('layouts.app')

@section('content')
<div class="container py-4">

  <h3 class="mb-4">Crear tienda</h3>

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

      <form action="{{ route('admin.stores.store') }}" method="POST">
        @csrf

        <div class="row g-3">

          <div class="col-md-6">
            <label class="form-label fw-bold">Nombre *</label>
            <input type="text" name="name" class="form-control" required>
          </div>

        

          <div class="col-md-6">
            <label class="form-label fw-bold">WhatsApp</label>
            <input type="text" name="whatsapp_number" class="form-control" placeholder="502XXXXXXXX">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Dirección</label>
            <input type="text" name="address" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Activo</label>
            <div class="form-check">
              <input type="checkbox" name="is_active" class="form-check-input" checked>
              <label class="form-check-label">Sí</label>
            </div>
          </div>

        </div>

        <div class="mt-4 d-flex gap-2">
          <button class="btn btn-success">Guardar</button>

          <a href="{{ route('admin.stores.index') }}" class="btn btn-secondary">
            Cancelar
          </a>
        </div>

      </form>

    </div>
  </div>

</div>
@endsection