<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventariom extends Model
{
    protected $connection = 'admin_ml';
    protected $table = 'inventariom';
    public $timestamps = false;
}
