<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioF extends Model
{
    protected $connection = 'admin_ml';
    protected $table = 'inventario';
    protected $primaryKey = 'Codprod';
    public $timestamps = false;

}
