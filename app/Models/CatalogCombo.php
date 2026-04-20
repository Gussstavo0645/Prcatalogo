<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogCombo extends Model
{
    protected $table = 'catalog_combos';

    protected $fillable = [
        'catalog_id',
        'code',
        'color',
        'name',
        'price',
        'image_path',
        'page_number',
        'position',
    ];

    public function items()
    {
        return $this->hasMany(CatalogComboItem::class, 'combo_id');
    }

    public function catalog()
    {
        return $this->belongsTo(Catalogo::class, 'catalog_id');
    }
}
