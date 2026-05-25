<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InvExistencias;

class Bodega extends Model
{
    protected $connection = 'admin_ml';
    protected $table = 'bodega';
    protected $primaryKey = 'Codbodega';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'Nombodega',
        
    ];

    public function existencias()
    {
        return $this->hasMany(InvExistencias::class, 'Bodega', 'Codbodega');
    }
}