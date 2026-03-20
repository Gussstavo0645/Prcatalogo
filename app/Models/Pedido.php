<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'nombre_cliente',
        'telefono_cliente',
        'cliente_correo',
        'cliente_contraseña',
        'direccion',
        'ciudad',
        'entrega_tipo',
        'notas',
        'pago_metodo',
        'requiere_factura',
        'total',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(PedidoItem::class, 'pedidos_id');
    }
}