<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Catalogo extends Model
{
    protected $table = 'catalogs';
    protected $fillable = ['title','slug','description','is_public','type','public_token','mesyope','tipo','tipocatalogo',];

    protected static function booted()
    {
        static::creating(function ($catalog) {

            // slug desde title
            if (empty($catalog->slug) && !empty($catalog->title)) {
                $base = Str::slug($catalog->title);
                $slug = $base;
                $i = 2;

                while (self::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i++;
                }

                $catalog->slug = $slug;
            }

            // token público tipo ILF6L
            if (empty($catalog->public_token)) {
                do {
                    $token = strtoupper(Str::random(5));
                } while (self::where('public_token', $token)->exists());

                $catalog->public_token = $token;
            }
        });
    }

    public function paginas()
    {
        return $this->hasMany(PaginaCatalogo::class, 'catalog_id')
                    ->orderBy('page_number');
    }

    public function productos()
    {
           return $this->belongsToMany(
        Product::class,
        'catalog_products',
        'catalog_id',
        'product_id'
    )
    ->withPivot(['quantity','page_number','position']) // incluye page_number
    ->withTimestamps()
    ->orderBy('catalog_products.page_number')          //  primero por pagina
    ->orderBy('catalog_products.position');            //  luego por posicion
    }

  


}
