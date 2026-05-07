<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    protected $table = 'pedidos_items';

    protected $fillable = [
        'pedidos_id',
        'product_code',
        'product_color',
        'product_name',
        'quantity',
        'price',
        'subtotal',
        'is_combo_component',
        'combo_code',
        'combo_color',
        'combo_name',
        'combo_group',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedidos_id');
    }
}