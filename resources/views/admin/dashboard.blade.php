@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="container py-4">

    <div class="mb-4">
        <h1 class="fw-bold">Dashboard</h1>
        <p class="text-muted mb-0">Resumen general del administrador</p>
    </div>
<form method="GET" action="{{ route('admin.dashboard') }}" class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">

            <div class="col-md-4">
                <label class="form-label">Fecha inicio</label>
                <input type="date"
                       name="fecha_inicio"
                       class="form-control"
                       value="{{ $fechaInicio ?? '' }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Fecha fin</label>
                <input type="date"
                       name="fecha_fin"
                       class="form-control"
                       value="{{ $fechaFin ?? '' }}">
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Filtrar
                </button>

                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                    Limpiar
                </a>
            </div>

        </div>
    </div>
</form>
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Total catálogos</p>
                    <h3 class="fw-bold">{{ $totalCatalogos ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Catálogos públicos</p>
                    <h3 class="fw-bold text-success">{{ $catalogosPublicos ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Catálogos ocultos</p>
                    <h3 class="fw-bold text-secondary">{{ $catalogosOcultos ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Tiendas activas</p>
                    <h3 class="fw-bold text-info">{{ $tiendasActivas ?? 0 }}</h3>
                </div>
            </div>
        </div>

    </div>

 <div class="row g-3 mb-4">

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <p class="text-muted mb-1">Pedidos pendientes</p>
                <h3 class="fw-bold text-warning">{{ $pedidosPendientes ?? 0 }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <p class="text-muted mb-1">Pedidos de hoy</p>
                <h3 class="fw-bold text-primary">{{ $pedidosHoy ?? 0 }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <p class="text-muted mb-1">Total vendido</p>
                <h3 class="fw-bold text-success">
                    Q {{ number_format($totalVendido ?? 0, 2) }}
                </h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <p class="text-muted mb-1">Pedidos cancelados</p>
                <h3 class="fw-bold text-danger">{{ $pedidosCancelados ?? 0 }}</h3>
            </div>
        </div>
    </div>

</div>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0 fw-bold">Gráfica de ventas</h5>
    </div>

    <div class="card-body">
        <canvas id="ventasChart" height="100"></canvas>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Últimos pedidos</h5>

        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-sm btn-outline-primary">
            Ver todos
        </a>
    </div>
        <div class="card-body p-0">

            @if(isset($ultimosPedidos) && $ultimosPedidos->count())

                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Tienda</th>
                                <th>Productos</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($ultimosPedidos as $pedido)
                                <tr>
                                    <td>
                                        <strong>{{ $pedido->nombre ?? $pedido->nombre_cliente ?? $pedido->Nombre ?? 'Sin nombre' }}</strong>
<br>
<small class="text-muted">
    {{ $pedido->telefono ?? $pedido->telefono_cliente ?? $pedido->Telefono ?? 'Sin teléfono' }}
</small>
                                    </td>

                                    <td>
                                        {{ $pedido->store->name ?? 'Sin tienda' }}
                                    </td>

                                    <td>
                                        {{ $pedido->items_count ?? 0 }}
                                    </td>

                                    <td>
                                        Q {{ number_format($pedido->total ?? 0, 2) }}
                                    </td>

                                    <td>
                                        @if($pedido->status == 'pendiente')
                                            <span class="badge bg-warning text-dark">Pendiente</span>
                                        @elseif($pedido->status == 'confirmado')
                                            <span class="badge bg-primary">Confirmado</span>
                                        @elseif($pedido->status == 'enviado')
                                            <span class="badge bg-info">Enviado</span>
                                        @elseif($pedido->status == 'entregado')
                                            <span class="badge bg-success">Entregado</span>
                                        @elseif($pedido->status == 'cancelado')
                                            <span class="badge bg-danger">Cancelado</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $pedido->status ?? 'Sin estado' }}</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ optional($pedido->created_at)->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @else

                <div class="p-4 text-center text-muted">
                    Todavía no hay pedidos registrados.
                </div>

            @endif

        </div>
    </div>

    <div class="row g-3 mt-4">

    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Ventas por tienda</h5>
            </div>

            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tienda</th>
                            <th>Pedidos</th>
                            <th>Total vendido</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($ventasPorTienda ?? [] as $venta)
                            <tr>
                                <td>{{ $venta->tienda ?? 'Sin tienda' }}</td>
                                <td>{{ $venta->total_pedidos }}</td>
                                <td>Q {{ number_format($venta->total_vendido ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">
                                    No hay ventas registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Pedidos por estado</h5>
            </div>

            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Estado</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($pedidosPorEstado ?? [] as $estado)
                            <tr>
                                <td>{{ ucfirst($estado->status ?? 'Sin estado') }}</td>
                                <td>{{ $estado->total }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-3">
                                    No hay pedidos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0 fw-bold">Productos más pedidos</h5>
    </div>

    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad pedida</th>
                    <th>Total vendido</th>
                </tr>
            </thead>

            <tbody>
                @forelse($productosMasPedidos ?? [] as $producto)
                    <tr>
                        <td>
    <strong>{{ $producto->producto ?? 'Sin producto' }}</strong>
    <br>
    <small class="text-muted">
        Código: {{ $producto->codigo ?? '-' }} | Color: {{ $producto->color ?? '-' }}
    </small>
</td>
                        <td>{{ $producto->cantidad_total }}</td>
                        <td>Q {{ number_format($producto->total_vendido ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-3">
                            No hay productos pedidos todavía.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ventasLabels = @json(($ventasPorDia ?? collect())->pluck('fecha'));
    const ventasData = @json(($ventasPorDia ?? collect())->pluck('total'));

    const ctx = document.getElementById('ventasChart');

    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ventasLabels,
                datasets: [{
                    label: 'Total vendido por día',
                    data: ventasData,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
</script>
@endpush