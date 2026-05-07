@extends('layouts.app')

@section('content')
<div class="container py-4">

  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Tiendas</h3>

    <a href="{{ route('admin.stores.create') }}" class="btn btn-primary">
      + Nueva tienda
    </a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover mb-0">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>WhatsApp</th>
            <th>Activo</th>
            <th width="160">Acciones</th>
          </tr>
        </thead>

        <tbody>
          @forelse($stores as $store)
            <tr>
              <td>{{ $store->id }}</td>
            <td>{{ $store->name }}</td>
              
              <td>{{ $store->whatsapp_number ?? '-' }}</td>
              <td>
                @if($store->activo)
                  <span class="badge bg-success">Sí</span>
                @else
                  <span class="badge bg-secondary">No</span>
                @endif
              </td>
              <td>
                <a href="{{ route('admin.stores.edit', $store->id) }}" class="btn btn-sm btn-warning">
                  Editar
                </a>

                <form action="{{ route('admin.stores.destroy', $store->id) }}"
                      method="POST"
                      class="d-inline"
                      onsubmit="return confirm('¿Eliminar esta tienda?');">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-danger">
                    Eliminar
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">
                No hay tiendas creadas.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection