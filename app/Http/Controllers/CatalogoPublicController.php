<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Catalogo;
use Illuminate\Support\Facades\DB;

class CatalogoPublicController extends Controller
{
    public function show(string $slug){

    $catalog = Catalogo :: where('slug', $slug)
    ->where('is_public',1)
    ->firstOrFail();  

    $productos = DB::table('catalog_products as cp')
        ->join('products as p', 'p.id', '=', 'cp.product_id')
        ->where('cp.catalog_id', $catalog->id)
        ->select('p.id','p.name','p.price','cp.quantity')
        ->orderByRaw('COALESCE(cp.position, 999999)') // por si position es null
        ->orderBy('p.name')
        ->get();

        $productosPorPagina = $productos->chunk(9);

    return view('catalogo.public', [
  'catalog' => $catalog,
  'publicView' => true,
  'productosPorPagina' => $productosPorPagina,
]);
    }
    
}
