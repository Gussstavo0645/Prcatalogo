@extends('layouts.app')

@section('content')
<div class="container py-4">
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif

  <h3>Nuevo catálogo</h3>
  <form action="{{ route('admin.catalogs.store') }}" method="POST" class="mb-4">
    @csrf
    <div class="row g-2">
      <div class="col-md-4">
        <input name="title" class="form-control" placeholder="Título" required value="{{ old('title') }}">
      </div>
      <div class="col-md-6">
        <input name="description" class="form-control" placeholder="Descripción" value="{{ old('description') }}">
      </div>
      <div class="col-md-2 form-check mt-2">
        <input type="checkbox" class="form-check-input" name="is_public" id="is_public" {{ old('is_public', true) ? 'checked' : '' }}>
        <label for="is_public" class="form-check-label">Público</label>
      </div>
    </div>
    <button class="btn btn-primary mt-3">Crear</button>
  </form>

  <hr>
  <h4>Subir páginas</h4>

  @if($catalog)
    <form action="{{ route('admin.catalogs.pages.store', $catalog) }}"
          method="POST" enctype="multipart/form-data">
      @csrf
      <div class="mb-2">
        <input type="file" name="pages[]" multiple accept="image/*" class="form-control" required>
        <small class="text-muted">Sube las páginas en orden (page-001, page-002...).</small>
      </div>
      <button class="btn btn-success">Subir páginas a: {{ $catalog->title }}</button>
    </form>
  @else
    <div class="alert alert-info">
      Primero crea un catálogo (arriba) y luego podrás subir sus páginas aquí.
    </div>
  @endif
</div>
@endsection
