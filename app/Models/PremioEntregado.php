<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremioEntregado extends Model
{
    protected $table = 'premios_entregados';

    protected $fillable = [
        'pedido_id',
        'Codcliente',
        'store_id',
        'mesope',
        'codtproducto',
        'producto',
        'color',
        'cantidad',
        'total_acumulado',
        'estado',
        'user_id',
        'fecha_entrega',
        'observacion',

        // Nuevos campos para canje múltiple
        'cantidad_c1',
        'cantidad_c2',
        'monto_base_acumulado',
        'monto_usado',
        'saldo_restante',
        'fecha_canje',
    ];

    protected $casts = [
        'cantidad_c1' => 'integer',
        'cantidad_c2' => 'integer',
        'monto_base_acumulado' => 'decimal:2',
        'monto_usado' => 'decimal:2',
        'saldo_restante' => 'decimal:2',
        'fecha_canje' => 'datetime',
        'fecha_entrega' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}