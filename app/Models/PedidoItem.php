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
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedidos_id');
    }
}