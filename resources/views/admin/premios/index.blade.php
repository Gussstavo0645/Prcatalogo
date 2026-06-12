@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Premios al contado</h2>
                <p class="text-muted mb-0">Administra los premios mensuales desde masterpremios.</p>
            </div>

            <a href="{{ route('admin.premios.create') }}" class="btn btn-primary">
                + Nuevo premio
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('admin.premios.index') }}" class="card card-body mb-4">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Mes</label>
                    <input type="text" name="mes" value="{{ $mes }}" class="form-control"
                        placeholder="06/2026">
                </div>

                <div class="col-md-3">
                    <button class="btn btn-dark w-100">
                        Buscar
                    </button>
                </div>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Mes</th>
                            <th>Producto premio</th>
                            <th>Cantidad</th>
                            <th>Desde</th>
                            <th>Hasta</th>
                            <th width="180">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($premios as $premio)
                            <tr>
                                <td class="fw-bold">{{ $premio->CODTPRODUCTO }}</td>
                                <td>{{ $premio->DESCRIP_PREMIO }}</td>
                                <td>{{ $premio->MESOPE }}</td>
                                <td>{{ $premio->CODOFERTA }}</td>
                                <td>Q{{ number_format($premio->VALORMIN, 2) }}</td>
                                <td>Q{{ number_format($premio->VALORMAX, 2) }}</td>
                                <td>
                                    <a href="{{ route('admin.premios.edit', [
                                        'codigo' => trim($premio->CODTPRODUCTO),
                                        'mes' => $premio->MESOPE,
                                    ]) }}"
                                        class="btn btn-sm btn-warning">
                                        Editar
                                    </a>

                                    <form
                                        action="{{ route('admin.premios.destroy', [$premio->CODTPRODUCTO, $premio->MESOPE]) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('¿Seguro que deseas eliminar este premio?')">
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
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No hay premios registrados para este mes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
