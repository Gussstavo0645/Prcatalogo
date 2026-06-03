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

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="card mb-3 shadow-sm">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">

        <div>
            <strong>Estado admin_ml:</strong><br>

            @if(($pedido->estado_admin_ml ?? 'no_enviado') === 'enviado')
                <span class="badge bg-success">Enviado a admin_ml</span>
                <small class="text-muted d-block">
                    ID web admin_ml: {{ $pedido->admin_ml_web_pedido_id }}
                </small>

            @elseif(($pedido->estado_admin_ml ?? 'no_enviado') === 'error')
                <span class="badge bg-danger">Error al enviar</span>

                @if($pedido->error_admin_ml)
                    <small class="text-danger d-block">
                        {{ $pedido->error_admin_ml }}
                    </small>
                @endif

            @else
                <span class="badge bg-secondary">No enviado</span>
            @endif
        </div>

        <div>
            @if(($pedido->estado_admin_ml ?? 'no_enviado') === 'enviado')
                <button class="btn btn-success" disabled>
                    Enviado a admin_ml
                </button>

            @elseif(($pedido->estado_admin_ml ?? 'no_enviado') === 'error')
                <form action="{{ route('admin.pedidos.enviarAdminMl', $pedido) }}"
                      method="POST"
                      onsubmit="return confirm('Este pedido tuvo error. ¿Deseas intentar enviarlo nuevamente?');">
                    @csrf

                    <button type="submit" class="btn btn-warning">
                        Reintentar envío a admin_ml
                    </button>
                </form>

            @else
                <form action="{{ route('admin.pedidos.enviarAdminMl', $pedido) }}"
                      method="POST"
                      onsubmit="return confirm('¿Enviar este pedido a admin_ml?');">
                    @csrf

                    <button type="submit" class="btn btn-primary">
                        Enviar a admin_ml
                    </button>
                </form>
            @endif
        </div>

    </div>
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
                <div class="col-md-4 mt-3">
    <strong>Estado del pago:</strong><br>

    @if(($pedido->pago_estado ?? 'pendiente') == 'pagado')

        <span class="badge bg-success">
            Pago validado
        </span>

    @else

        <span class="badge bg-warning text-dark mb-2">
            Pago pendiente
        </span>

        <form action="{{ route('admin.pedidos.validarPago', $pedido->id) }}" method="POST" class="mt-2">
            @csrf
            @method('PATCH')

            <button type="submit" class="btn btn-success btn-sm"
                    onclick="return confirm('¿Validar el pago de este pedido?')">
                Validar pago
            </button>
        </form>

    @endif
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
    $itemsPedido = $pedido->items;

    // Solo productos que son componentes de un combo
    $combosAgrupados = $itemsPedido
        ->filter(function ($item) {
            return (int)($item->is_combo_component ?? 0) === 1
                && !empty($item->combo_group);
        })
        ->groupBy('combo_group');

    // Productos normales, como el 786
    $productosNormales = $itemsPedido
        ->filter(function ($item) {
            return (int)($item->is_combo_component ?? 0) === 0;
        });
@endphp

<tbody>

    {{-- COMBOS --}}
    @foreach($combosAgrupados as $grupo => $itemsCombo)

        @php
            $primero = $itemsCombo->first();
        @endphp

        <tr style="background:#e9f3ff; font-weight:bold;">
            <td colspan="7">
                🧩 {{ $primero->combo_name ?? ('Combo ' . $primero->combo_code . '-' . $primero->combo_color) }}
            </td>
        </tr>

        @foreach($itemsCombo->unique(fn($item) => trim((string)$item->product_code).'-'.trim((string)$item->product_color))->values() as $item)

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
                <td>Q {{ number_format((float)$item->price, 2) }}</td>
                <td><strong>Q {{ number_format((float)$item->subtotal, 2) }}</strong></td>
            </tr>

        @endforeach

    @endforeach


    {{-- PRODUCTOS INDIVIDUALES --}}
    @if($productosNormales->count() > 0)

        <tr style="background:#f4f4f8; font-weight:bold;">
            <td colspan="7">
                Productos individuales
            </td>
        </tr>

        @foreach($productosNormales as $item)

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
                <td>Q {{ number_format((float)$item->price, 2) }}</td>
                <td><strong>Q {{ number_format((float)$item->subtotal, 2) }}</strong></td>
            </tr>

        @endforeach

    @endif

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