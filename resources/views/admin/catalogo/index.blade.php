@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-3">Catálogos</h1>

    <div class="row g-3">
        @foreach($catalogs as $c)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $c->title }}</h5>

                        <p class="mb-2 text-muted">
                            {{ \Illuminate\Support\Str::limit($c->description, 120) }}
                        </p>

                        <div class="mb-3">
                            @if($c->is_public)
                                <span class="badge bg-success">Público</span>
                            @else
                                <span class="badge bg-secondary">Oculto</span>
                            @endif
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.catalogs.create', ['catalog' => $c->id]) }}"
                               class="btn btn-primary btn-sm">
                                Seleccionar
                            </a>

                            <a href="{{ route('admin.catalog.show', $c->slug) }}"
                               class="btn btn-dark btn-sm"
                               target="_blank">
                                Ver previa
                            </a>

                            <form action="{{ route('admin.catalogos.togglePublic', $c->id) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                @if($c->is_public)
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        Ocultar
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-success btn-sm">
                                        Publicar
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection