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
                    {{ $pedido->nombre_cliente }}
                </div>

                <div class="col-md-4">
                    <strong>Teléfono:</strong><br>
                    {{ $pedido->telefono_cliente }}
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

                <tbody>
                    @foreach($pedido->items as $item)
                        <tr>
                            <td>
                                @if(!empty($item->product_code))
                                    <img
                                        src="{{ route('catalog.product.image', ['code' => $item->product_code, 'color' => $item->product_color]) }}"
                                        width="60"
                                        class="rounded"
                                        style="object-fit:contain;background:#fff;"
                                        onerror="this.src='https://via.placeholder.com/60x60?text=Sin+foto';"
                                    >
                                @endif
                            </td>

                            <td>
                                {{ $item->product_name ?? 'Producto no disponible' }}
                            </td>

                            <td>
                                {{ $item->product_code ?? '-' }}
                            </td>

                            <td>
                                {{ $item->product_color ?? '-' }}
                            </td>

                            <td>
                                {{ $item->quantity }}
                            </td>

                            <td>
                                Q {{ number_format($item->price, 2) }}
                            </td>

                            <td>
                                <strong>
                                    Q {{ number_format($item->subtotal, 2) }}
                                </strong>
                            </td>
                        </tr>
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