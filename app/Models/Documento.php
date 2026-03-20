<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $fillable = [
        'nombre',
        'mime',
        'archivo_binario'
    ];

    // 👇 MUY IMPORTANTE
    protected $hidden = ['archivo_binario'];
}