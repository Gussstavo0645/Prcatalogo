<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogComboItem extends Model
{
    protected $table = 'catalog_combo_items';

    protected $fillable = [
        'combo_id',
        'product_code',
        'product_color',
        'quantity',
    ];

    public function combo()
    {
        return $this->belongsTo(CatalogCombo::class, 'combo_id');
    }
}