@extends('layouts.app')

@section('content')

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h3 class="mb-0">
            Pedidos de clientes
        </h3>

        <span class="badge bg-dark">
            Total: {{ $pedidos->total() }}
        </span>

    </div>


    <div class="card shadow-sm">

        <div class="card-body p-0">

            <table class="table table-hover mb-0">

                <thead class="table-dark">

                    <tr>

                        <th>ID</th>

                        <th>Cliente</th>

                        <th>Teléfono</th>

                        <th>Total</th>

                        <th>Productos</th>

                        <th>Estado</th>

                        <th>Fecha</th>

                        <th width="120">Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($pedidos as $pedido)

                        <tr>

                            <td>
                                <strong>#{{ $pedido->id }}</strong>
                            </td>

                            <td>
                                {{ $pedido->nombre_cliente }}
                            </td>

                            <td>
                                {{ $pedido->telefono_cliente }}
                            </td>

                            <td>
                                <strong>
                                    Q {{ number_format($pedido->total, 2) }}
                                </strong>
                            </td>

                            <td>

                                <span class="badge bg-info">
                                    {{ $pedido->items_count }}
                                </span>

                            </td>

                            <td>

                                @if($pedido->status == 'pendiente')

                                    <span class="badge bg-warning text-dark">
                                        Pendiente
                                    </span>

                                @elseif($pedido->status == 'confirmado')

                                    <span class="badge bg-primary">
                                        Confirmado
                                    </span>

                                @elseif($pedido->status == 'enviado')

                                    <span class="badge bg-info">
                                        Enviado
                                    </span>

                                @elseif($pedido->status == 'entregado')

                                    <span class="badge bg-success">
                                        Entregado
                                    </span>

                                @elseif($pedido->status == 'cancelado')

                                    <span class="badge bg-danger">
                                        Cancelado
                                    </span>

                                @endif

                            </td>

                            <td>

                                {{ $pedido->created_at->format('d/m/Y H:i') }}

                            </td>

                            <td>

                                <a href="{{ route('admin.pedidos.show', $pedido->id) }}"
                                   class="btn btn-sm btn-dark">

                                    Ver

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="text-center text-muted py-4">

                                No hay pedidos aún

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    <div class="mt-3">

        {{ $pedidos->links() }}

    </div>


</div>

@endsection