<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Catalogo;

class Tienda extends Model
{
    protected $table = 'stores';

    protected $fillable = [
        'bodega_codigo',
        'nombre',
        'direccion',
        'telefono',
        'whatsapp',
        'activo',
    ];

    public function catalogos()
    {
        return $this->belongsToMany(
            Catalogo::class,
            'catalog_store',
            'store_id',
            'catalog_id'
        );
    }
}