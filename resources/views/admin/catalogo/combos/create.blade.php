@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Crear combo para catálogo: {{ $catalog->title }}</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.catalogos.combos.store', $catalog->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card mb-3">
            <div class="card-header">Datos del combo</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Código combo</label>
                    <input type="text" name="combo_code" class="form-control" value="{{ old('combo_code') }}" placeholder="930">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Color combo</label>
                    <input type="text" name="combo_color" class="form-control" value="{{ old('combo_color') }}" placeholder="13">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Página</label>
                    <input type="number" name="page_number" class="form-control" value="{{ old('page_number', 1) }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Posición</label>
                    <input type="number" name="position" class="form-control" value="{{ old('position', 1) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Imagen del combo</label>
                    <input type="file" name="image" class="form-control">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Productos del combo</div>
            <div class="card-body">
                <div id="combo-items">
                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <input type="text" name="items[0][product_code]" class="form-control" placeholder="Código producto">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="items[0][product_color]" class="form-control" placeholder="Color">
                        </div>
                        <div class="col-md-4">
                            <input type="number" name="items[0][quantity]" class="form-control" placeholder="Cantidad" value="1" min="1">
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-secondary" onclick="addComboItemRow()">Agregar otra fila</button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Guardar combo</button>
    </form>
</div>

<script>
let comboIndex = 1;

function addComboItemRow() {
    const wrap = document.getElementById('combo-items');

    const row = document.createElement('div');
    row.className = 'row g-2 mb-2';
    row.innerHTML = `
        <div class="col-md-4">
            <input type="text" name="items[${comboIndex}][product_code]" class="form-control" placeholder="Código producto">
        </div>
        <div class="col-md-4">
            <input type="text" name="items[${comboIndex}][product_color]" class="form-control" placeholder="Color">
        </div>
        <div class="col-md-4">
            <input type="number" name="items[${comboIndex}][quantity]" class="form-control" placeholder="Cantidad" value="1" min="1">
        </div>
    `;
    wrap.appendChild(row);
    comboIndex++;
}
</script>
@endsection