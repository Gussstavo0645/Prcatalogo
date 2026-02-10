<?php
// app/Http/Controllers/CatalogController.php
namespace App\Http\Controllers;
use App\Models\Catalogo;
 use App\Models\PaginaCatalogo;
class CatalogoController extends Controller {

    public function index() {
        $catalogs = catalogo::where('is_public', true)->latest()->get();
        return view('catalogo.index', compact('catalogs'));

    }


    public function show($slug)
{
    $catalog = Catalogo::where('slug', $slug)->firstOrFail();
    $pages = $catalog->paginas()->get(); // ya viene orderBy

    return view('catalogo.show', compact('catalog', 'pages'));
}

public function pageImage(PaginaCatalogo $pagina)
{
    abort_unless($pagina->archivo_binario, 404);

    return response($pagina->archivo_binario)
        ->header('Content-Type', $pagina->mime ?? 'image/jpeg');
}


    
}