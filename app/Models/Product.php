<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    public $timestamps = true;

     protected $fillable = [
        'code',
        'name',
        'price',
        'color',
        'image_blob',
        'mime',
    ];

    protected $hidden = ['image_blob']; // evita error UTF-8 en json



    protected $casts = [
        'price' => 'decimal:2',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return route('products.image', $this->id);
    }

    public function catalogos()
    {
        return $this->belongsToMany(
            Catalogo::class,
            'catalog_products',
            'product_id',
            'catalog_id'
        )
        ->withPivot(['quantity','position'])
        ->withTimestamps();
    }

    public function categories()
{
    
    return $this->belongsToMany(\App\Models\Category::class);
}

}