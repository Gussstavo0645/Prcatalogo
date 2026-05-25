<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvExistencias extends Model
{
    protected $connection = 'admin_ml';
    protected $table = 'inv_existencias';

    public $timestamps = false;

    public function bodega()
    {
        return $this->belongsTo(Bodega::class, 'Bodega', 'Codbodega');
    }

    public function inventario()
    {
        return $this->belongsTo(Inventariom::class, 'Codigo', 'Codigo');
    }
}