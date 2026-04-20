<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\Catalogo;
use App\Models\PaginaCatalogo;
use App\Models\Product;

use function Symfony\Component\Clock\now;

class AdminCatalogo extends Controller
{
    // AHORA: si viene ?catalog=ID, lo carga
public function create(Request $r)
{
    set_time_limit(120);
    ini_set('memory_limit', '1024M');


    $catalogs = Catalogo::select('id', 'title', 'description', 'is_public', 'slug')
    ->orderByDesc('id')
    ->get();

    $meses = DB::connection('admin_ml')
    ->table('inventario')
    ->selectRaw('TRIM(mesyope) as mesyope')
    ->whereNotNull('mesyope')
    ->whereRaw("TRIM(mesyope) <> ''")
    ->distinct()
    ->orderByDesc('mesyope')
    ->pluck('mesyope');

$tipos = DB::connection('admin_ml')
    ->table('inventario')
    ->selectRaw('TRIM(tipocatalogo) as tipocatalogo')
    ->whereNotNull('tipocatalogo')
    ->whereRaw("TRIM(tipocatalogo) <> ''")
    ->distinct()
    ->orderBy('tipocatalogo')
    ->pluck('tipocatalogo');

$mes = trim((string) $r->input('mesyope', ($meses->first() ?? '')));
$tipo = trim((string) $r->input('tipocatalogo', ''));
    $pageFilter = $r->input('filter_page');

    $catalog = null;
    $catalogProducts = collect();

    if ($r->filled('catalog')) {
        $catalog = Catalogo::findOrFail($r->input('catalog'));
    }

    // IMPORTANTE:
    // create() ya no debe consultar inventario pesado para el grid de búsqueda
    $products = collect();

    if ($catalog) {
        $items = DB::table('catalog_products as cp')
            ->where('cp.catalog_id', $catalog->id)
            ->select([
                'cp.code',
                'cp.color',
                'cp.quantity',
                'cp.page_number',
                'cp.position',
            ])
            ->orderBy('cp.page_number')
            ->orderByRaw('COALESCE(cp.position, 999999)')
            ->get();

        if ($items->isNotEmpty()) {
            $codes = $items->pluck('code')->filter()->unique()->values()->all();

          $inventarioQuery = DB::connection('admin_ml')
    ->table('inventario as i')
    ->whereIn('i.Codprod', $codes);

if ($mes !== '') {
    $inventarioQuery->whereRaw('TRIM(i.mesyope) = ?', [$mes]);
}

if ($tipo !== '') {
    $inventarioQuery->whereRaw('TRIM(i.tipocatalogo) = ?', [$tipo]);
}

$inventario = $inventarioQuery
    ->select([
        'i.Codprod as code',
        'i.color as color',
        'i.Descripcion as name',
        'i.Precventa as price',
    ])
    ->get();

            $inventarioMap = $inventario->keyBy(function ($row) {
                return trim((string) $row->code) . '|' . trim((string) $row->color);
            });

            $catalogProducts = $items->map(function ($item) use ($inventarioMap) {
                $key = trim((string) $item->code) . '|' . trim((string) $item->color);
                $inv = $inventarioMap->get($key);

                return (object) [
                    'code' => $item->code,
                    'color' => $item->color,
                    'name' => $inv->name ?? 'Producto no encontrado',
                    'price' => (float) ($inv->price ?? 0),
                    'quantity' => $item->quantity,
                    'page_number' => $item->page_number,
                    'position' => $item->position,
                ];
            });
        }
    }

    return view('admin.catalogo.create', compact(
    'catalogs',
    'catalog',
    'products',
    'catalogProducts',
    'mes',
    'tipo',
    'pageFilter',
    'meses',
    'tipos'
));
}

public function show($slug)
{
    $catalog = Catalogo::where('slug', $slug)->firstOrFail();

    $mes = '04/2026';
    $tipo = 'N';
   

    $pages = $catalog->paginas()
        ->select('id','catalog_id','page_number','mime','thumb_path','meta','created_at','updated_at')
        ->orderBy('page_number')
        ->get();

    // 1) productos del catálogo local
    $catalogItems = DB::table('catalog_products as cp')
        ->where('cp.catalog_id', $catalog->id)
        ->select([
            'cp.code',
            'cp.color',
            'cp.quantity',
            'cp.page_number',
            'cp.position',
        ])
        ->orderBy('cp.page_number')
        ->orderByRaw('COALESCE(cp.position, 999999)')
        ->get();

    if ($catalogItems->isEmpty()) {
        $productosPorPagina = collect();
        $pagesRender = [];

        foreach ($pages as $pagina) {
            $pagesRender[] = [
                'pagina' => $pagina,
                'page_number_label' => (int) $pagina->page_number,
                'items' => collect(),
                'chunk_index' => 0,
            ];
        }

       return view('catalogo.show', compact('catalog', 'pagesRender'));
    }

    // 2) traer inventario desde admin_ml
    $codes = $catalogItems->pluck('code')
        ->filter()
        ->map(fn($v) => trim((string)$v))
        ->unique()
        ->values();
$inventario = DB::connection('admin_ml')
        ->table('inventario as i')
        ->where('i.mesyope', $mes)
        ->where('i.tipocatalogo', $tipo)
        ->whereIn('i.Codprod', $codes)
        ->select([
            'i.Codprod as code',
            'i.color as color',
            'i.Descripcion as name',
            'i.Precventa as price',
        ])
        ->get();
  

    // 3) indexar inventario por code|color
    $inventarioMap = $inventario->keyBy(function ($row) {
        return trim((string)$row->code) . '|' . trim((string)$row->color);
    });

    $inventarioByCode = $inventario
        ->groupBy(function ($row) {
            return trim((string)$row->code);
        })
        ->map(function ($rows) {
            return $rows->first(function ($row) {
                return trim((string)($row->name ?? '')) !== '';
            }) ?? $rows->first();
        });

  $productos = $catalogItems->map(function ($item) use ($inventarioMap, $inventarioByCode) {
    $codeOriginal = trim((string) ($item->code ?? ''));
    $colorOriginal = trim((string) ($item->color ?? ''));

    $lookupCode = $codeOriginal;
    $lookupColor = $colorOriginal;

    if (str_contains($codeOriginal, '-')) {
        $partes = explode('-', $codeOriginal, 2);
        $lookupCode = trim((string) ($partes[0] ?? ''));
        if ($lookupColor === '') {
            $lookupColor = trim((string) ($partes[1] ?? ''));
        }
    }

    $key = $lookupCode . '|' . $lookupColor;

    // 1) exacto por código + color
    $invExact = $inventarioMap->get($key);

    // 2) fallback solo para nombre, nunca para precio
    $invByCode = $inventarioByCode->get($lookupCode);

    $name = trim((string) ($invExact->name ?? ''));
    if ($name === '') {
        $name = trim((string) ($invByCode->name ?? ''));
    }

    $price = $invExact ? (float) ($invExact->price ?? 0) : 0;

    return (object) [
        'code' => $lookupCode,
        'color' => $lookupColor,
        'display_code' => $lookupCode . ($lookupColor !== '' ? '-' . $lookupColor : ''),
        'name' => $name !== '' ? $name : 'Producto sin descripción',
        'price' => $price,
        'quantity' => (int) ($item->quantity ?? 1),
        'page_number' => (int) ($item->page_number ?? 1),
        'position' => (int) ($item->position ?? 1),
    ];
});

    $productosPorPagina = $productos
        ->sortBy([
            ['page_number', 'asc'],
            ['position', 'asc'],
        ])
        ->groupBy(function ($item) {
            return (int) $item->page_number;
        })
        ->map(function ($items) {
            return $items->sortBy('position')->values();
        });

    // AQUÍ VA LO NUEVO
    $pagesRender = [];

    foreach ($pages as $pagina) {
        $pageNum = (int) $pagina->page_number;

        $items = $productosPorPagina[$pageNum] ?? collect();

        if ($items->count() > 0) {
            $chunks = $items->chunk(9);

            foreach ($chunks as $chunkIndex => $chunk) {
                $pagesRender[] = [
                    'pagina' => $pagina,
                    'page_number_label' => $pageNum,
                    'items' => $chunk->values(),
                    'chunk_index' => $chunkIndex,
                ];
            }
        } else {
            $pagesRender[] = [
                'pagina' => $pagina,
                'page_number_label' => $pageNum,
                'items' => collect(),
                'chunk_index' => 0,
            ];
        }
    }

    return view('catalogo.show', compact('catalog', 'pagesRender'));
}

    public function edit($catalog)
{
     $catalog = \App\Models\Catalogo::with(['productos'])->findOrFail($catalog);
      $products = \App\Models\Product::orderBy('id', 'desc')->paginate(9);


    $catalogProducts = $catalog->productos->map(function ($p) {
        $p->page_number = $p->pivot->page_number ?? 1;
        $p->quantity    = $p->pivot->quantity ?? 1;
        $p->position    = $p->pivot->position ?? 1;
        $p->code        = $p->pivot->code ?? $p->code;
        $p->color       = $p->pivot->color ?? $p->color;
        return $p;
    });


 //   return view('catalogo.index', compact('catalog', 'products'));

     return view('admin.catalogo.index', compact('catalog', 'products', 'catalogProducts'));
}

    public function addPages(Catalogo $catalog)
    {
       $pages = $catalog->paginas()
    ->select('id','catalog_id','page_number','mime','thumb_path','meta','created_at','updated_at')
    ->orderBy('page_number')
    ->get();
        $next  = (int) ($catalog->paginas()->max('page_number') ?? 0) + 1;

        return view('admin.catalogo.create', compact('catalog', 'pages', 'next'));
    }

    public function storePages(Request $r, Catalogo $catalog)
    {
        $r->validate([
            'pages'   => 'required|array|min:1',
            'pages.*' => 'file|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        DB::transaction(function () use ($r, $catalog) {
            $next = (int) ($catalog->paginas()->max('page_number') ?? 0) + 1;

            foreach ($r->file('pages', []) as $file) {

                $fromName = $this->extractNumber($file->getClientOriginalName());
                $n = $fromName ?? $next;

                while ($catalog->paginas()->where('page_number', $n)->exists()) {
                    $n++;
                }

                $binary = file_get_contents($file->getRealPath());

                PaginaCatalogo::create([
                    'catalog_id'      => $catalog->id,
                    'page_number'     => $n,
                    'archivo_binario' => $binary,
                    'mime'            => $file->getMimeType(),
                    'thumb_path'      => null,
                    'meta'            => null,
                ]);

                $next = $n + 1;
            }
        });

        return back()->with('ok', 'Páginas subidas correctamente');
    }

    private function extractNumber(string $filename): ?int
    {
        if (preg_match('/(\d{1,4})/', $filename, $m)) {
            $num = (int) ltrim($m[1], '0'); // "001" -> 1
            return $num >= 0 ? $num : null;
        }
        return null;
    }

    /**
     *  AJAX: Agregar producto al catálogo POR PÁGINA (catalog_products)
     * Requiere: column page_number en catalog_products
     */
public function addProduct(Request $r, Catalogo $catalog)
{
    $data = $r->validate([
        'code'        => 'required|string|max:255',
        'color'       => 'nullable|string|max:255',
        'quantity'    => 'required|integer|min:1',
        'page_number' => 'required|integer|min:1',
    ]);

    $code = trim((string) $data['code']);
    $color = trim((string) ($data['color'] ?? ''));

    $existing = DB::table('catalog_products')
        ->where('catalog_id', $catalog->id)
        ->where('code', $code)
        ->where('color', $color)
        ->where('page_number', $data['page_number'])
        ->first();

    if ($existing) {
        $finalQty = $existing->quantity + $data['quantity'];

        DB::table('catalog_products')
            ->where('catalog_id', $catalog->id)
            ->where('code', $code)
            ->where('color', $color)
            ->where('page_number', $data['page_number'])
            ->update([
                'quantity' => $finalQty,
                'updated_at' => now(),
            ]);
    } else {
        $finalQty = $data['quantity'];

        DB::table('catalog_products')->insert([
            'catalog_id'  => $catalog->id,
            'code'        => $code,
            'color'       => $color,
            'quantity'    => $finalQty,
            'page_number' => $data['page_number'],
            'position'    => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    $inventario = DB::connection('admin_ml')
        ->table('inventario as i')
        ->where('i.mesyope', $catalog->mesyope)
        ->where('i.tipocatalogo', $catalog->tipocatalogo)
        ->whereRaw('TRIM(i.Codprod) = ?', [$code])
        ->whereRaw('TRIM(COALESCE(i.color, "")) = ?', [$color])
        ->select([
            'i.Codprod as code',
            'i.color as color',
            'i.Descripcion as name',
            'i.Precventa as price',
            'i.pagina as source_page',
        ])
        ->first();

    return response()->json([
        'ok' => true,
        'product' => [
            'code' => $code,
            'color' => $color,
            'name' => $inventario->name ?? 'Producto sin descripción',
            'price' => (float) ($inventario->price ?? 0),
            'source_page' => $inventario->source_page ?? null,
        ],
        'quantity' => $finalQty,
        'page_number' => $data['page_number'],
    ]);
}


    public function updateProductQty(Request $r, Catalogo $catalog, Product $product)
    {
        //  si quieres, después lo hacemos por page_number también
        $data = $r->validate([
            'quantity' => 'required|integer|min:1|max:999',
        ]);

        $catalog->productos()->updateExistingPivot($product->id, [
            'quantity' => (int) $data['quantity'],
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     *  Remover SOLO de una página (no de todo el catálogo)
     * El JS debe mandar ?page_number=#
     */
    public function removeProduct(Request $r, Catalogo $catalog)
{
    $data = $r->validate([
        'code' => 'required|string|max:255',
        'color' => 'nullable|string|max:255',
        'page_number' => 'nullable|integer|min:1',
    ]);

    $page = (int)($data['page_number'] ?? 1);

    DB::table('catalog_products')
        ->where('catalog_id', $catalog->id)
        ->where('code', $data['code'])
        ->where('color', $data['color'])
        ->where('page_number', $page)
        ->delete();

    return response()->json([
        'ok' => true,
        'page_number' => $page,
    ]);
}


public function destroyPage($catalogoId, $paginaId)
{
    $catalogo = \App\Models\Catalogo::findOrFail($catalogoId);

    $pagina = \App\Models\PaginaCatalogo::where('catalog_id', $catalogo->id)
        ->where('id', $paginaId)
        ->firstOrFail();

    $pageNumberEliminada = (int) $pagina->page_number;

    DB::transaction(function () use ($catalogo, $pagina, $pageNumberEliminada) {

        // 1) borrar productos asignados a esa página del catálogo
        DB::table('catalog_products')
            ->where('catalog_id', $catalogo->id)
            ->where('page_number', $pageNumberEliminada)
            ->delete();

        // 2) borrar la página
        $pagina->delete();

        // 3) reordenar páginas siguientes
        \App\Models\PaginaCatalogo::where('catalog_id', $catalogo->id)
            ->where('page_number', '>', $pageNumberEliminada)
            ->decrement('page_number');

        // 4) reordenar productos de páginas siguientes
        $productos = DB::table('catalog_products')
            ->where('catalog_id', $catalogo->id)
            ->where('page_number', '>', $pageNumberEliminada)
            ->orderBy('page_number')
            ->get();

        foreach ($productos as $prod) {
            DB::table('catalog_products')
                ->where('catalog_id', $catalogo->id)
                ->where('code', $prod->code)
                ->where('color', $prod->color)
                ->where('page_number', $prod->page_number)
                ->update([
                    'page_number' => $prod->page_number - 1
                ]);
        }
    });

    return redirect()
        ->back()
        ->with('success', 'Página eliminada correctamente.');
}

public function store(Request $r)
{
    $data = $r->validate([
        'title'        => 'required|string|max:255',
        'description'  => 'nullable|string|max:1000',
        'type'         => 'required|string|max:5',
        'is_public'    => 'nullable',
        'mesyope'      => 'required|string|max:7',
        'tipocatalogo' => 'required|string|max:5',
    ]);

    $catalog = Catalogo::create([
        'title'        => $data['title'],
        'description'  => $data['description'] ?? null,
        'type'         => $data['type'],
        'is_public'    => 0,
        'slug'         => Str::slug($data['title']) . '-' . strtolower(Str::random(6)),
        'mesyope'      => $data['mesyope'],
        'tipocatalogo' => $data['tipocatalogo'],
    ]);

    return redirect()->route('admin.catalogs.create', [
        'catalog'      => $catalog->id,
        'mesyope'      => $catalog->mesyope,
        'tipocatalogo' => $catalog->tipocatalogo,
    ])->with('ok', 'Catálogo creado correctamente.');
}

public function bulkAddProducts(Request $request, $catalog)
{
    $request->validate([
        'product_ids'   => 'required|array|min:1',
        'product_ids.*' => 'required|integer',
        'page_number'   => 'nullable|integer|min:1',
        'quantity'      => 'nullable|integer|min:1',
    ], [
        'product_ids.required' => 'Debes seleccionar al menos un producto.',
        'product_ids.min'      => 'Debes seleccionar al menos un producto.',
    ]);

    $catalogId  = (int) $catalog;
    $productIds = array_unique($request->product_ids);
    $pageNumber = (int) ($request->page_number ?? 1);
    $quantity   = (int) ($request->quantity ?? 1);

    $agregados = 0;
    $actualizados = 0;

    foreach ($productIds as $productId) {
        $existe = DB::table('catalog_products')
            ->where('catalog_id', $catalogId)
            ->where('product_id', $productId)
            ->first();

        if ($existe) {
            DB::table('catalog_products')
                ->where('catalog_id', $catalogId)
                ->where('product_id', $productId)
                ->update([
                    'page_number' => $pageNumber,
                    'quantity'    => $quantity,
                    'updated_at'  => now(),
                ]);

            $actualizados++;
        } else {
            DB::table('catalog_products')->insert([
                'catalog_id'  => $catalogId,
                'product_id'  => $productId,
                'page_number' => $pageNumber,
                'quantity'    => $quantity,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $agregados++;
        }
    }

    return back()->with(
        'success',
        "Proceso completado. Agregados: {$agregados}, actualizados: {$actualizados}."
    );
}

public function searchProducts(Request $r)
{
    $mes = trim((string) $r->input('mesyope', ''));
$tipo = trim((string) $r->input('tipocatalogo', ''));

    $pageFilter = trim((string) $r->input('filter_page'));
    $catalogId = $r->input('catalog');

    $catalog = null;
    if ($catalogId) {
        $catalog = Catalogo::select('id', 'title')->find($catalogId);
    }

  $productsQuery = DB::connection('admin_ml')
    ->table('inventario as i');

if ($mes !== '') {
    $productsQuery->whereRaw('TRIM(i.mesyope) = ?', [$mes]);
}

if ($tipo !== '') {
    $productsQuery->whereRaw('TRIM(i.tipocatalogo) = ?', [$tipo]);
}

  if ($pageFilter !== '') {
    $productsQuery->whereRaw('TRIM(i.pagina) = ?', [$pageFilter]);
}

    $products = $productsQuery
        ->select([
            'i.Codprod as code',
            'i.Descripcion as name',
            'i.Precventa as price',
            'i.color as color',
            'i.pagina as source_page',
        ])
        ->orderBy('i.pagina')
        ->orderBy('i.Descripcion')
        ->simplePaginate(20, ['*'], 'prod_page')
        ->appends([
            'catalog' => $catalogId,
            'mesyope' => $mes,
            'tipocatalogo' => $tipo,
            'filter_page' => $pageFilter,
        ]);

    return view('admin.catalogo.parcial.products_list', compact(
        'products',
        'catalog',
        'mes',
        'tipo',
        'pageFilter'
    ));
}



    public function index()
{
    $catalogs = Catalogo::orderByDesc('id')->get();
    return view('admin.catalogo.index', compact('catalogs'));
}


public function togglePublic($id)
{
    $catalog = Catalogo::findOrFail($id);

    $catalog->is_public = !$catalog->is_public;
    $catalog->save();

    return back()->with(
        'success',
        $catalog->is_public
            ? 'Catálogo publicado correctamente.'
            : 'Catálogo ocultado correctamente.'
    );
}
    
}