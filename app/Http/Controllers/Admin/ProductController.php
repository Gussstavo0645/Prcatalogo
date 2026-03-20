<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Catalogo;
use Illuminate\Support\Facades\DB;




class ProductController extends Controller
{
    public function index()
    {
        $products = Product::select('id','name','price','code') // sin blob
    ->orderBy('name')
    ->paginate(12);


        // Tu blade usa $catalog en varios lugares, así evitamos 500
        $catalog = null;

        return view('admin.products.index', compact('products', 'catalog'));
    }

    // Guardar producto desde tu formulario (BLOB + code obligatorio)
    public function store(Request $r)
{
    $data = $r->validate([
        'name'         => 'required|string|max:255',
        'code'         => 'nullable|string|max:255',
        'description'  => 'nullable|string',
        'price'        => 'required|numeric|min:0',
        'image'        => 'nullable|image|max:4096',
        'catalog_id'   => 'nullable|exists:catalogos,id',
        'categories'   => 'nullable|array',
        'categories.*' => 'integer|exists:categories,id',
        'barcode_type'  => 'nullable|string|max:50',
'barcode_value' => 'nullable|string|max:255',

    ]);

    // Generar code automático si no viene
    if (!empty($data['code'])) {
        $code = $data['code'];
    } else {
        do {
            $code = strtoupper(Str::random(10));
        } while (Product::where('code', $code)->exists());
    }

    $p = new Product();
    $p->name = $data['name'];
    $p->price = $data['price'];
    $p->code = $code;
    $p->description = $data['description'] ?? null;
    $p->barcode_type  = $data['barcode_type'] ?? null;
$p->barcode_value = $data['barcode_value'] ?? null;


    //  Guardar imagen como BLOB
    if ($r->hasFile('image')) {
        $file = $r->file('image');
        $p->mime = $file->getClientMimeType();
        $p->image_blob = file_get_contents($file->getRealPath());
    }

    $p->save();

    //  Guardar categorías (many to many)
    $p->categories()->sync($data['categories'] ?? []);

    return back()->with('swal_product_created', [
        'title' => 'Producto registrado con éxito',
        'text'  => mb_convert_encoding((string)$p->name, 'UTF-8', 'UTF-8'),
        'view'  => route('catalogs.index'),

        //  Datos completos para "Seguir editando"
        'product' => [
            'id' => $p->id,
            'name' => $p->name,
            'code' => $p->code,
            'price' => $p->price,
            'description' => $p->description ?? '',
            'categories_ids' => $p->categories()
                                  ->pluck('categories.id')
                                  ->map(fn($x)=>(int)$x)
                                  ->toArray(),
              'barcode_type'  => $p->barcode_type,
'barcode_value' => $p->barcode_value,

        ],

        'update_url' => route('admin.products.update', $p->id),
    ]);
}

public function update(Request $r, Product $product)
{
    $data = $r->validate([
        'name'         => 'required|string|max:255',
        'price'        => 'required|numeric|min:0',
        'description'  => 'nullable|string',
        'categories'   => 'nullable|array',
        'categories.*' => 'exists:categories,id',
        'image'        => 'nullable|image|max:4096',
        'barcode_type'  => 'nullable|string|max:50',
'barcode_value' => 'nullable|string|max:255',

    ]);

    $product->name = $data['name'];
    $product->price = $data['price'];
    $product->description = $data['description'] ?? null;
   $product ->barcode_type  = $data['barcode_type'] ?? null;
$product->barcode_value = $data['barcode_value'] ?? null;



    if ($r->hasFile('image')) {
        $file = $r->file('image');
        $product->mime = $file->getClientMimeType();
        $product->image_blob = file_get_contents($file->getRealPath());
    }

    $product->save();

    $product->categories()->sync($data['categories'] ?? []);

    return back()->with('swal_product_created', [
        'title' => 'Cambios guardados',
        'text'  => mb_convert_encoding((string) $product->name, 'UTF-8', 'UTF-8'),
        'product' => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'description' => $product->description ?? '',
            'categories_ids' => $product->categories()->pluck('categories.id')->toArray(),
            'barcode_type'  => $product->barcode_type,
'barcode_value' => $product->barcode_value,

        ],
        'update_url' => route('admin.products.update', $product->id),
        'view' => route('catalogs.index'),
    ]);
}

public function destroy(Product $product)
{
    try {
        // borrar relaciones en la pivote (si existen)
        DB::table('catalog_products')
            ->where('product_id', $product->id)
            ->delete();

        // borrar el producto
        $product->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Producto eliminado correctamente.'
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'ok' => false,
            'message' => 'No se pudo eliminar el producto.',
            'error' => $e->getMessage()   // <- importante
        ], 500);
    }
}
public function image(Product $product)
{
    $row = Product::select('id','mime','image_blob')->findOrFail($product->id);

    if (empty($row->image_blob)) abort(404);

    return response($row->image_blob)
        ->header('Content-Type', $row->mime ?? 'image/jpeg');
}

 
  public function importFromAdminMl(Request $r)
    {
        $r->validate([
            'mesyope'      => ['required', 'regex:/^\d{2}\/\d{4}$/'],
            'tipocatalogo' => ['required', 'in:N,E,F,C'],
        ], [
            'mesyope.required' => 'El mes del catálogo es obligatorio.',
            'mesyope.regex'    => 'El mes debe tener formato MM/YYYY. Ejemplo: 03/2026.',
            'tipocatalogo.required' => 'El tipo de catálogo es obligatorio.',
            'tipocatalogo.in'       => 'El tipo de catálogo no es válido.',
        ]);

        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $mes  = $r->input('mesyope');
        $tipo = $r->input('tipocatalogo');

        $nuevos = 0;
        $actualizados = 0;

        DB::purge('admin_ml');
        DB::reconnect('admin_ml');

        $rows = DB::connection('admin_ml')
            ->table('inventario as i')
            ->where('i.mesyope', $mes)
            ->where('i.tipocatalogo', $tipo)
            ->select([
                'i.Codprod as code',
                'i.Descripcion as name',
                'i.Precventa as price',
                'i.color as color',
            ])
            ->get();

        foreach ($rows as $row) {
            $code  = trim((string) $row->code);
            $color = trim((string) ($row->color ?? ''));

            if ($code === '') {
                continue;
            }

            $product = Product::where('code', $code)
                ->where('color', $color)
                ->first();

            if ($product) {
                $product->update([
                    'name'  => $row->name,
                    'price' => $row->price,
                ]);
                $actualizados++;
            } else {
                Product::create([
                    'code'  => $code,
                    'name'  => $row->name,
                    'price' => $row->price,
                    'color' => $color,
                ]);
                $nuevos++;
            }
        }

        return redirect()
            ->back()
            ->with('ok', "Importación completada. Nuevos: {$nuevos}, actualizados: {$actualizados}. Mes: {$mes}, tipo: {$tipo}");
    }

public function importImagesFromAdminMl(Request $r)
    {
        $r->validate([
            'mesyope'      => ['required', 'regex:/^\d{2}\/\d{4}$/'],
            'tipocatalogo' => ['required', 'in:N,E,F,C'],
        ], [
            'mesyope.required' => 'El mes del catálogo es obligatorio.',
            'mesyope.regex'    => 'El mes debe tener formato MM/YYYY. Ejemplo: 03/2026.',
            'tipocatalogo.required' => 'El tipo de catálogo es obligatorio.',
            'tipocatalogo.in'       => 'El tipo de catálogo no es válido.',
        ]);

        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $mes  = $r->input('mesyope');
        $tipo = $r->input('tipocatalogo');

        $actualizadas = 0;
        $sinCoincidencia = 0;
        $sinImagen = 0;

        DB::purge('admin_ml');
        DB::reconnect('admin_ml');

        $rows = DB::connection('admin_ml')
    ->table('inventario as i')
    ->leftJoin('inv_fotos as f', function($join){
        $join->on('i.Codprod', '=', 'f.codigo')
             ->on('i.Color', '=', 'f.color');
    })
    ->where('i.mesyope', $mes)
    ->where('i.tipocatalogo', $tipo)
    ->select([
        'i.Codprod as code',
        'i.color as color',
        'f.foto as image_blob', // 🔥 AQUÍ ESTÁ LA CLAVE
    ])
    ->get();

        foreach ($rows as $row) {
            $code  = trim((string) $row->code);
            $color = trim((string) ($row->color ?? ''));

            if ($code === '') {
                continue;
            }

            if (empty($row->image_blob)) {
                $sinImagen++;
                continue;
            }

            $product = Product::where('code', $code)
                ->where('color', $color)
                ->first();

            if (!$product) {
                $sinCoincidencia++;
                continue;
            }

            $product->image_blob = $row->image_blob;
            $product->mime = 'image/jpeg';
            $product->save();

            $actualizadas++;
        }

        return redirect()
            ->back()
            ->with('ok', "Imágenes importadas. Actualizadas: {$actualizadas}, sin coincidencia: {$sinCoincidencia}, sin imagen: {$sinImagen}. Mes: {$mes}, tipo: {$tipo}");
    }

}

