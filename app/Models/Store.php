<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Catalogo;

class Store extends Model
{
    protected $table = 'stores';

  protected $fillable = [
    'name',
    'address',
    'hours',
    'manager',
    'whatsapp_number',
    'is_active',
];

    public function catalogos()
    {
        return $this->belongsToMany(
            Catalogo::class,
            'bodega_codigo', 
            'catalog_store',
            'store_id',
            'catalog_id'
        );
    }
}