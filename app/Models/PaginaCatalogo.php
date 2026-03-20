<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaginaCatalogo extends Model
{
    protected $table = 'catalog_pages';

    protected $fillable = ['catalog_id', 'page_number', 'archivo_binario', 'mime','thumb_path', 'meta'];
    protected $casts = ['meta' => 'array'];

    // oculta el blob en dumps -json-excepciones
    protected $hidden = ['archivo_binario'];

    public function catalogo()
    {
        return $this->belongsTo(\App\Models\Catalogo::class, 'catalog_id');
    }
}