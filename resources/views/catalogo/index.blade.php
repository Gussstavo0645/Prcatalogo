{{-- resources/views/catalogs/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-3">Catálogos</h1>

    <div class="row g-3">
        @foreach($catalogs as $c)
            <div class="col-md-4">
                <a class="card h-100 text-decoration-none" href="{{ route('catalog.show', $c->slug) }}">
                    <div class="card-body">
                        <h5 class="card-title">{{ $c->title }}</h5>
                        {{ \Illuminate\Support\Str::limit($c->description, 120) }}
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection