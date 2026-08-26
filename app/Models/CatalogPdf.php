<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogPdf extends Model
{
    use HasFactory;

    protected $table = 'catalog_pdfs';

    protected $fillable = [
        'catalog_id',
        'titulo',
        'slug',
        'descripcion',
        'mes',
        'anio',
        'portada',
        'archivo_pdf',
        'nombre_archivo_original',
        'numero_paginas',
        'destacado',
        'activo',
        'orden',
        'publicado_at',
    ];

    protected $casts = [
        'mes' => 'integer',
        'anio' => 'integer',
        'numero_paginas' => 'integer',
        'destacado' => 'boolean',
        'activo' => 'boolean',
        'orden' => 'integer',
        'publicado_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}