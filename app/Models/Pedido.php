<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'store_id',
        'CodCliente',
        'Nombre',
        'Telefono',
        'nit',
        'dpi',
        'cliente_correo',
        'cliente_contraseña',
        'pago_metodo',
        'total',
        'status',
        'pago_estado',
        'pago_gateway',
        'pago_referencia',
        'pago_transaccion_id',
        'pago_token',
        'pago_monto',
        'pago_moneda',
        'pago_respuesta',
        'pagado_en',
    ];

    public function items()
    {
        return $this->hasMany(PedidoItem::class, 'pedidos_id');
    }

    public function store()
{
    return $this->belongsTo(Store::class, 'store_id');
}

protected $casts = [
    'pago_respuesta' => 'array',
    'pagado_en' => 'datetime',
];
}