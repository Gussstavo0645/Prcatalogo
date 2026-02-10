<?php
// app/Models/Catalogo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    protected $table = 'catalogs';
    protected $fillable = ['title','slug','description','is_public'];

    // Relación 1:N con las páginas del catálogo
    public function paginas()
    {
        return $this->hasMany(\App\Models\PaginaCatalogo::class, 'catalog_id')
                    ->orderBy('page_number');
    }
}
