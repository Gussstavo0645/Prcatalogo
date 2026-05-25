@extends('layouts.app')

@section('content')
<div class="container py-4">

  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Bodegas e inventario</h3>

    {{--<a href="{{ route('admin.stores.create') }}" class="btn btn-primary">
      + Nueva bodega
    </a>--}}
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <form method="GET" action="{{ route('admin.stores.index') }}" class="row g-2 align-items-end">

       <div class="col-md-4">
  <label class="form-label fw-bold">Bodega</label>
  <select name="bodega" class="form-select">
    <option value="">Seleccione una bodega</option>

    @foreach($bodegas as $bodega)
      <option value="{{ $bodega->Codbodega }}"
        {{ (string)$bodegaSeleccionada === (string)$bodega->Codbodega ? 'selected' : '' }}>
        {{ str_pad($bodega->Codbodega, 3, '0', STR_PAD_LEFT) }} - {{ $bodega->Nombodega }}
      </option>
    @endforeach
  </select>
</div>

<div class="col-md-3">
  <label class="form-label fw-bold">Buscar producto</label>
  <input type="text"
         name="q"
         class="form-control"
         value="{{ $buscar }}"
         placeholder="Ej: 1011-1, código o descripción">
</div>

<div class="col-md-2">
  <label class="form-label fw-bold d-block">Stock</label>

  <div class="form-check">
    <input class="form-check-input"
           type="checkbox"
           name="solo_stock"
           value="1"
           id="solo_stock"
           {{ request('solo_stock') ? 'checked' : '' }}>

    <label class="form-check-label" for="solo_stock">
      Solo con stock
    </label>
  </div>
</div>

<div class="col-md-2">
  <button class="btn btn-dark w-100">
    Consultar
  </button>
</div>

<div class="col-md-1">
  <a href="{{ route('admin.stores.index') }}" class="btn btn-secondary w-100">
    X
  </a>
</div>

      </form>
    </div>
  </div>

  @if(empty($bodegaSeleccionada))
    <div class="alert alert-info">
      Selecciona una bodega para ver sus productos y stock.
    </div>
  @else

    <div class="card shadow-sm">
      <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <div>
         <strong>Bodega:</strong>
{{ str_pad($bodegaActual->Codbodega ?? $bodegaSeleccionada, 3, '0', STR_PAD_LEFT) }}
-
{{ $bodegaActual->Nombodega ?? 'Sin nombre' }}
        </div>

       {{-- <div>
          <a href="{{ route('admin.stores.edit', ['store' => $bodegaSeleccionada]) }}" class="btn btn-sm btn-warning">
            Editar bodega
          </a> 
        </div>--}}
      </div>

      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead class="table-dark">
            <tr>
              <th>Ubicación Interna</th>
              <th>Código</th>
              <th>Color</th>
              <th>Descripción</th>
              <th>Stock Actual</th>
            </tr>
          </thead>

          <tbody>
            @forelse($productos as $producto)
              <tr>
                <td>{{ $producto->Ubicacion ?? '-' }}</td>
                <td>{{ $producto->Codigo }}</td>
                <td>{{ $producto->Color ?? '-' }}</td>
                <td>{{ $producto->Descripcion ?? '-' }}</td>
              
               <td>
  @if(($producto->stock_total ?? 0) > 0)
    <span class="badge bg-success">
      {{ $producto->stock_total }}
    </span>
  @else
    <span class="badge bg-secondary">
      Sin stock
    </span>
  @endif
</td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">
                  No hay productos para esta bodega.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($productos)
        <div class="card-footer">
          {{ $productos->links() }}
        </div>
      @endif
    </div>

  @endif

</div>
@endsection