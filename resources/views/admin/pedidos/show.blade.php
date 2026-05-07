@extends('layouts.app')

@section('content')

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            Pedido #{{ $pedido->id }}
        </h3>

        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-secondary">
            Volver
        </a>
    </div>

    {{-- DATOS DEL CLIENTE --}}
    <div class="card mb-3 shadow-sm">
        <div class="card-header bg-dark text-white">
            Información del cliente
        </div>

        <div class="card-body">
            <div class="row">

                <div class="col-md-4">
                    <strong>Nombre:</strong><br>
                    {{ $pedido->Nombre }}
                </div>

                <div class="col-md-4">
    <strong>Teléfono:</strong><br>
    {{ $pedido->Telefono }}
</div>

<div class="col-md-4">
    <strong>Tienda:</strong><br>
    @if($pedido->store)
        {{ $pedido->store->name }} <br>
        <small class="text-muted">
            WhatsApp: {{ $pedido->store->whatsapp_number }}
        </small>
    @else
        <span class="text-danger">Sin tienda</span>
    @endif
</div>
                

                <div class="col-md-4">
                    <strong>Estado:</strong><br>

                    <form method="POST" action="{{ route('admin.pedidos.estado', $pedido->id) }}">
                        @csrf
                        @method('PATCH')

                        <select name="status" onchange="this.form.submit()" class="form-select">
                            <option value="pendiente" {{ $pedido->status=='pendiente'?'selected':'' }}>
                                Pendiente
                            </option>
                            <option value="confirmado" {{ $pedido->status=='confirmado'?'selected':'' }}>
                                Confirmado
                            </option>
                            <option value="enviado" {{ $pedido->status=='enviado'?'selected':'' }}>
                                Enviado
                            </option>
                            <option value="entregado" {{ $pedido->status=='entregado'?'selected':'' }}>
                                Entregado
                            </option>
                            <option value="cancelado" {{ $pedido->status=='cancelado'?'selected':'' }}>
                                Cancelado
                            </option>
                        </select>
                    </form>

                </div>

            </div>
        </div>
    </div>

    {{-- PRODUCTOS --}}
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            Productos del pedido
        </div>

        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="80">Imagen</th>
                        <th>Producto</th>
                        <th>Código</th>
                        <th>Color</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

@php
$itemsAgrupados = $pedido->items->groupBy(function ($item) {
    return $item->combo_group ?: 'normal_' . $item->id;
});
@endphp

                <tbody>
                    @foreach($itemsAgrupados as $grupo => $items)

    @php
        $primero = $items->first();
    @endphp

    {{-- 🔥 SI ES COMBO --}}
    @if($primero->is_combo_component)

        <tr style="background:#e9f3ff; font-weight:bold;">
            <td colspan="7">
                🧩 {{ $primero->combo_name }}
            </td>
        </tr>

        @foreach($items as $item)

     @php
  $code = trim((string) $item->product_code);
  $color = trim((string) $item->product_color);
@endphp

<tr>
    <td>
<img
  src="{{ url('/catalogo/producto-thumb/'.$code.'/'.$color) }}"
  width="60"
  class="rounded"
  style="object-fit:contain;background:#fff;"
  onerror="this.onerror=null;this.src='https://via.placeholder.com/60x60?text=Sin+foto';"
>
                </td>

                <td>{{ $item->product_name }}</td>
                <td>{{ $item->product_code }}</td>
                <td>{{ $item->product_color }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Q {{ number_format($item->price, 2) }}</td>
                <td><strong>Q {{ number_format($item->subtotal, 2) }}</strong></td>
            </tr>
        @endforeach

    {{-- 🔹 PRODUCTO NORMAL --}}
    @else

        @foreach($items as $item)

          @php
  $code = trim((string) $item->product_code);
  $color = trim((string) $item->product_color);
@endphp

<tr>
    <td>
<img
  src="{{ url('/catalogo/producto-thumb/'.$code.'/'.$color) }}"
  width="60"
  class="rounded"
  style="object-fit:contain;background:#fff;"
  onerror="this.onerror=null;this.src='https://via.placeholder.com/60x60?text=Sin+foto';"
>
                </td>

                <td>{{ $item->product_name }}</td>
                <td>{{ $item->product_code }}</td>
                <td>{{ $item->product_color }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Q {{ number_format($item->price, 2) }}</td>
                <td><strong>Q {{ number_format($item->subtotal, 2) }}</strong></td>
            </tr>
        @endforeach

    @endif

@endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer text-end">
            <h4 class="mb-0">
                Total:
                <span class="text-success">
                    Q {{ number_format($pedido->total, 2) }}
                </span>
            </h4>
        </div>
    </div>

</div>

@endsection