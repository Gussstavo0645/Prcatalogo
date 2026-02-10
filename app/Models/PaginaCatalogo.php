<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaginaCatalogo extends Model

{   
    protected $table = 'catalog_pages';   
    protected $fillable = ['catalog_id', 'page_number', 'archivo_binario', 'mime','thumb_path', 'meta'];
    protected $casts = ['meta' => 'array'];
  

    // Relacioncada pagina pertenece a un catalogo
    public function catalogo()
    {
        return $this->belongsTo(\App\Models\Catalogo::class, 'catalog_id');
    }

}
