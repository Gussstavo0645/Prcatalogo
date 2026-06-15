<?php

namespace App\Http\Controllers;

use App\Models\CatalogCombo;
use App\Models\Catalogo;
use App\Models\PaginaCatalogo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\Request;



class CatalogoController extends Controller
{
    /*LISTA CATALOGOS
    */
public function index()
{
    $catalogos = Catalogo::where('is_public', 1)
        ->orderByDesc('id')
        ->get();

    $catalogoBase = $catalogos->first();

    // Mes y tipo base del catálogo más reciente
    $mesPremios = trim((string) ($catalogoBase->mesyope ?? now()->format('m/Y')));
    $mesCatalogo = trim((string) ($catalogoBase->mesyope ?? $mesPremios));
    $tipoCatalogo = trim((string) ($catalogoBase->tipocatalogo ?? 'N'));

    if ($mesCatalogo === '') {
        $mesCatalogo = $mesPremios;
    }

    if ($tipoCatalogo === '') {
        $tipoCatalogo = 'N';
    }

    $premiosBase = collect();
    $masVendidos = collect();

    $admin = DB::connection('admin_ml');

    try {
        // PREMIOS AL CONTADO
        $premiosBase = $admin
            ->table('masterpremios as mp')
            ->select(
                'mp.CODOFERTA as codigo_premio',
                'mp.DESCRIP_PREMIO as descripcion_rango',
                'mp.MESOPE',
                'mp.CODTPRODUCTO as rango_premio',
                'mp.VALORMIN',
                'mp.VALORMAX'
            )
            ->whereRaw('TRIM(mp.MESOPE) = ?', [$mesPremios])
            ->whereIn(DB::raw('TRIM(mp.CODTPRODUCTO)'), ['C1', 'C2'])
            ->orderBy('mp.VALORMIN')
            ->get();





            // TODO: MÁS VENDIDOS REALES
// Actualmente este slider muestra productos del inventario para probar el diseño.
// Pendiente: cambiar esta consulta para tomar los productos realmente más vendidos
// desde las tablas de pedidos:
// - web_catalogo_pedido_items
// - web_catalogo_pedidos
//
// La idea será agrupar por código/color y ordenar por la cantidad total vendida:
//
// SELECT code, color, SUM(quantity) AS total_vendido
// FROM web_catalogo_pedido_items
// GROUP BY code, color
// ORDER BY total_vendido DESC
//
// Luego cruzar esos códigos contra admin_ml.inventario para traer:
// - nombre
// - precio
// - imagen
//
// Cuando ya haya suficientes pedidos reales, reemplazar esta consulta temporal.

        // PRODUCTOS MÁS VENDIDOS PARA EL SLIDER
        $masVendidos = $admin->table('inventario as i')
            ->select(
                DB::raw('TRIM(i.Codprod) as code'),
                DB::raw('TRIM(i.color) as color'),
                'i.Descripcion as name',
                'i.Precventa as price'
            )
            ->whereRaw('TRIM(i.mesyope) = ?', [$mesCatalogo])
            ->whereRaw('TRIM(i.tipocatalogo) = ?', [$tipoCatalogo])
            ->whereRaw("TRIM(i.Descripcion) <> ''")
            ->where('i.Precventa', '>', 0)
            ->orderBy('i.pagina')
            ->limit(12)
            ->get();

    } finally {
        $this->cerrarAdmin();
    }


    // ==============================
    // CALIFICACIONES REALES DE PRODUCTOS
    // BD local: catalogo.product_reviews
    // ==============================
    $ratings = DB::table('product_reviews')
        ->select(
            'code',
            'color',
            DB::raw('ROUND(AVG(rating), 1) as avg_rating'),
            DB::raw('COUNT(*) as total_reviews')
        )
        ->where('approved', 1)
        ->groupBy('code', 'color')
        ->get()
        ->keyBy(function ($item) {
            return trim((string) $item->code) . '-' . trim((string) $item->color);
        });

    $masVendidos = $masVendidos->map(function ($prod) use ($ratings) {
        $code = trim((string) ($prod->code ?? ''));
        $color = trim((string) ($prod->color ?? ''));

        $key = $code . '-' . $color;

        $prod->avg_rating = (float) ($ratings[$key]->avg_rating ?? 0);
        $prod->total_reviews = (int) ($ratings[$key]->total_reviews ?? 0);

        return $prod;
    });

    // Fotos subidas desde tu admin en la BD local
    $fotosPremios = DB::table('premios_publicos')
        ->where('mesope', $mesPremios)
        ->where('activo', 1)
        ->get()
        ->keyBy(function ($foto) {
            return trim((string) $foto->rango_premio);
        });

    // Unimos los premios con su foto pública
    $premiosContado = $premiosBase->map(function ($premio) use ($fotosPremios) {
        $foto = $fotosPremios->get(trim((string) $premio->rango_premio));

        $premio->foto_publica = $foto->foto_publica ?? null;

        return $premio;
    });

    return view('catalogo.index', compact(
        'catalogos',
        'premiosContado',
        'masVendidos'
    ));
}










    /*VER CATALOGO
    */
    public function show($slug)
    {
        
        $catalog = Catalogo::where('slug', $slug)->firstOrFail();

        $mes = trim((string) ($catalog->mesyope ?? ''));
        $tipo = trim((string) ($catalog->tipocatalogo ?? ''));

        if ($mes === '' || $tipo === '') {
            abort(500, 'Este catálogo no tiene mesyope o tipocatalogo definido.');
        }


        $pages = $catalog->paginas()
            ->select('id', 'catalog_id', 'page_number', 'mime', 'thumb_path', 'meta', 'created_at', 'updated_at')
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
        
    // ==============================
    // 2. PREPARAR CÓDIGOS
    // ==============================
    $codes = $catalogItems->pluck('code')
        ->filter()
        ->map(function ($v) {
            $v = trim((string) $v);
            return str_contains($v, '-') ? trim(explode('-', $v, 2)[0]) : $v;
        })
        ->unique()
        ->values()
        ->all();

    $inventario = collect();
    $existenciasPorProducto = collect();

    // ==============================
    // 3. SOLO AQUÍ ABRIMOS admin_ml
    // ==============================
    $admin = DB::connection('admin_ml');

    try {
        if (!empty($codes)) {
            $inventario = $admin->table('inventario as i')
                ->whereRaw('TRIM(i.mesyope) = ?', [$mes])
                ->whereRaw('TRIM(i.tipocatalogo) = ?', [$tipo])
                ->whereIn(DB::raw('TRIM(i.Codprod)'), $codes)
                ->select([
                    'i.Codprod as code',
                    'i.color as color',
                    'i.Descripcion as name',
                    'i.Precventa as price',
                ])
                ->get();

            $existencias = $admin
                ->table('inv_existencias as e')
                ->leftJoin('bodega as b', 'e.Bodega', '=', 'b.Codbodega')
                ->select(
                    DB::raw('TRIM(e.Codigo) as Codigo'),
                    DB::raw('TRIM(e.Color) as Color'),
                    'e.Bodega',
                    'b.Nombodega as tienda',
                    DB::raw('SUM(e.Saldo) as stock')
                )
                ->whereIn(DB::raw('TRIM(e.Codigo)'), $codes)
                ->where('e.Saldo', '>', 0)
                ->whereRaw("UPPER(TRIM(b.Nombodega)) <> 'MAL ESTADO'")
                ->groupBy(
                    DB::raw('TRIM(e.Codigo)'),
                    DB::raw('TRIM(e.Color)'),
                    'e.Bodega',
                    'b.Nombodega'
                )
                ->havingRaw('SUM(e.Saldo) > 0')
                ->orderBy('b.Nombodega')
                ->get();

            $existenciasPorProducto = $existencias
                ->groupBy(function ($row) {
                    return trim((string) $row->Codigo) . '|' . trim((string) $row->Color);
                })
                ->map(function ($rows) {
                    return $rows->map(function ($row) {
                        return [
                            'tienda' => $row->tienda ?: 'Sin nombre',
                            'stock' => (int) $row->stock,
                        ];
                    })->values();
                });
        }

    } finally {
        $this->cerrarAdmin();
    }

    // ==============================
    // 4. DESDE AQUÍ admin_ml YA ESTÁ CERRADO
    // ==============================
    $inventarioMap = $inventario->keyBy(function ($row) {
        return trim((string) $row->code) . '|' . trim((string) $row->color);
    });

    $inventarioByCode = $inventario
        ->groupBy(function ($row) {
            return trim((string) $row->code);
        })
        ->map(function ($rows) {
            return $rows->first(function ($row) {
                return trim((string) ($row->name ?? '')) !== '';
            }) ?? $rows->first();
        });

    $productos = $catalogItems->map(function ($item) use ($inventarioMap, $inventarioByCode, $existenciasPorProducto) {
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

        $invExact = $inventarioMap->get($key);

        if (!$invExact && $lookupColor === '0') {
            $invExact = $inventarioMap->get($lookupCode . '|');
        }

        if (!$invExact && $lookupColor === '') {
            $invExact = $inventarioMap->get($lookupCode . '|0');
        }

        $invByCode = $inventarioByCode->get($lookupCode);

        $name = trim((string) ($invExact->name ?? ''));

        if ($name === '') {
            $name = trim((string) ($invByCode->name ?? ''));
        }

        $price = $invExact
            ? (float) ($invExact->price ?? 0)
            : (float) ($invByCode->price ?? 0);

        $existencias = $existenciasPorProducto->get($key);

        if (!$existencias && $lookupColor === '0') {
            $existencias = $existenciasPorProducto->get($lookupCode . '|');
        }

        if (!$existencias && $lookupColor === '') {
            $existencias = $existenciasPorProducto->get($lookupCode . '|0');
        }

        if (!$existencias) {
            $existencias = $existenciasPorProducto
                ->filter(function ($rows, $stockKey) use ($lookupCode) {
                    return str_starts_with($stockKey, $lookupCode . '|');
                })
                ->flatten(1)
                ->values();
        }

        return (object) [
            'code' => $lookupCode,
            'color' => $lookupColor,
            'display_code' => $lookupCode . ($lookupColor !== '' ? '-' . $lookupColor : ''),
            'name' => $name !== '' ? $name : 'Producto sin descripción',
            'price' => $price,
            'quantity' => (int) ($item->quantity ?? 1),
            'page_number' => (int) ($item->page_number ?? 1),
            'position' => (int) ($item->position ?? 1),
            'existencias' => $existencias ?: collect(),
        ];
    });

    // ==============================
    // 5. COMBOS LOCALES
    // ==============================
    $combos = CatalogCombo::where('catalog_id', $catalog->id)
        ->get()
        ->map(function ($combo) {
            return (object) [
                'code' => trim((string) $combo->code),
                'color' => trim((string) $combo->color),
                'display_code' => trim((string) $combo->code) . (trim((string) $combo->color) !== '' ? '-' . trim((string) $combo->color) : ''),
                'name' => $combo->name ?: 'Combo sin descripción',
                'price' => (float) ($combo->price ?? 0),
                'quantity' => 1,
                'page_number' => (int) ($combo->page_number ?? 1),
                'position' => (int) ($combo->position ?? 1),
                'is_combo' => true,
                'image_path' => $combo->image_path,
                'existencias' => collect(),
            ];
        });

    $productos = $productos->concat($combos);

    // ==============================
    // 6. ARMAR PRODUCTOS POR PÁGINA
    // ==============================
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

    $pagesRender = [];

    foreach ($pages as $pagina) {
        $pageNum = (int) $pagina->page_number;

        $items = $productosPorPagina->get($pageNum, collect());

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
    /*
    IMAGEN PAGINA (archivo_binario)
    */
    public function pageImage(PaginaCatalogo $page)
    {
        abort_if(is_null($page->archivo_binario), 404);

        $dir = storage_path('app/public/pages_cache');
        $path = $dir . "/page_{$page->id}.jpg";

        if (file_exists($path)) {
            return response()->file($path, [
                'Cache-Control' => 'public, max-age=86400',
                'Content-Type' => 'image/jpeg',
            ]);
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $binary = $page->archivo_binario;

        if (is_resource($binary)) {
            $binary = stream_get_contents($binary);
        }

        file_put_contents($path, $binary);

        return response()->file($path, [
            'Cache-Control' => 'public, max-age=86400',
            'Content-Type' => $page->mime ?? 'image/jpeg',
        ]);
    }


    public function showPublic($slug)
    {
        $catalog = Catalogo::where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        $mes = trim((string) ($catalog->mesyope ?? ''));
        $tipo = trim((string) ($catalog->tipocatalogo ?? ''));

        $ratingsVersion = DB::table('product_reviews')->max('updated_at') ?? 'no-reviews';

$cacheKey = "public_pages_render_catalog_{$catalog->id}_{$catalog->updated_at->timestamp}_ratings_" . md5((string) $ratingsVersion);

$pagesRender = Cache::remember($cacheKey, 300, function () use ($catalog) {
    return $this->buildPublicPagesRender($catalog);
});


        return view('catalogo.public', compact('catalog', 'pagesRender'));
    }

    public function productoImagen($code, $color = null)
    {

    $admin = DB::connection('admin_ml');
    try{
        $code = trim((string) $code);
        $color = trim((string) ($color ?? ''));

        $codigoBusqueda = $code;
        $colorBusqueda = $color;

        if (str_contains($code, '-')) {
            $partes = explode('-', $code, 2);

            $codigoBase = trim((string) ($partes[0] ?? ''));
            $colorDesdeCodigo = trim((string) ($partes[1] ?? ''));

            if ($codigoBase !== '' && $colorDesdeCodigo !== '') {
                $codigoBusqueda = $codigoBase;
                $colorBusqueda = $colorDesdeCodigo;
            }
        }

        $query = $admin
            ->table('inv_fotos')
            ->where('codigo', $codigoBusqueda);

        if ($colorBusqueda !== '' && $colorBusqueda !== '0') {
            $query->where('color', $colorBusqueda);
        }

        $row = $query->select('foto')->first();

        if (!$row || empty($row->foto)) {
            abort(404);
        }

        return response($row->foto, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400',
        ]);

    } finally {
        $this->cerrarAdmin();
    }
    }

    public function productoImagenLarge($code, $color = null)
    {

   
        $code = trim((string) $code);
        $color = trim((string) ($color ?? ''));

        $cacheKey = "producto_large_{$code}_{$color}";

        $imageBinary = Cache::remember($cacheKey, 86400, function () use ($code, $color) {
 $admin = DB::connection('admin_ml');
    try{
            $codigoBusqueda = $code;
            $colorBusqueda = $color;

            if (str_contains($code, '-')) {
                $partes = explode('-', $code, 2);

                $codigoBusqueda = trim((string) ($partes[0] ?? ''));
                $colorBusqueda = trim((string) ($partes[1] ?? ''));
            }

            $query = $admin
                ->table('inv_fotos')
                ->where('codigo', $codigoBusqueda);

            if ($colorBusqueda !== '' && $colorBusqueda !== '0') {
                $query->where('color', $colorBusqueda);
            }

            $row = $query->select('foto')->first();
            } finally {
                $this->cerrarAdmin();
            }

            if (!$row || empty($row->foto)) {
                return null;
            }

            $manager = new ImageManager(new Driver());

            return (string) $manager->read($row->foto)
                ->scale(width: 800)
                ->toJpeg(85);
        });

        abort_if(!$imageBinary, 404);

        return response($imageBinary)
            ->header('Content-Type', 'image/jpeg')
            ->header('Cache-Control', 'public, max-age=86400');
            
    }

    public function productoThumb($code, $color = null)
    {
        
        $code = trim((string) $code);
        $color = trim((string) ($color ?? ''));

        $codigoBusqueda = $code;
        $colorBusqueda = $color;

        if (str_contains($code, '-')) {
            $partes = explode('-', $code, 2);

            $codigoBase = trim((string) ($partes[0] ?? ''));
            $colorDesdeCodigo = trim((string) ($partes[1] ?? ''));

            if ($codigoBase !== '' && $colorDesdeCodigo !== '') {
                $codigoBusqueda = $codigoBase;
                $colorBusqueda = $colorDesdeCodigo;
            }
        }

        $safeCode = preg_replace('/[^A-Za-z0-9_\-]/', '_', $codigoBusqueda);
        $safeColor = preg_replace('/[^A-Za-z0-9_\-]/', '_', $colorBusqueda !== '' ? $colorBusqueda : '0');

        $dir = storage_path('app/public/thumbs');
        $path = $dir . "/{$safeCode}_{$safeColor}.jpg";

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Durante pruebas, puedes comentar este bloque para forzar regeneración
        if (file_exists($path) && filesize($path) > 0) {
            return response()->file($path, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
        $admin = DB::connection('admin_ml');
        try{

        $query = $admin->table('inv_fotos')->whereRaw('TRIM(codigo) = ?', [$codigoBusqueda]);

        if ($colorBusqueda !== '' && $colorBusqueda !== '0') {
            $query->whereRaw('TRIM(color) = ?', [$colorBusqueda]);
        }

        $row = $query->select('foto')->first();

        } finally {
        $this->cerrarAdmin();
    }

        if (!$row || empty($row->foto)) {
            abort(404);
        }

        $binary = $row->foto;

        if (is_resource($binary)) {
            $binary = stream_get_contents($binary);
        }

        $manager = new ImageManager(new Driver());

        $encoded = $manager->read($binary)
            ->scale(width: 200)
            ->toJpeg(75);

        file_put_contents($path, $encoded->toString());

        return response()->file($path, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    
    }

    protected function buildPublicPagesRender($catalog)
{
    // ==============================
    // 1. CONSULTAS LOCALES
    // ==============================
    $mes = trim((string) ($catalog->mesyope ?? ''));
    $tipo = trim((string) ($catalog->tipocatalogo ?? ''));

    $pages = $catalog->paginas()
        ->select('id', 'catalog_id', 'page_number', 'mime')
        ->orderBy('page_number')
        ->get();

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

        $ratings = DB::table('product_reviews')
    ->select(
        'code',
        'color',
        DB::raw('ROUND(AVG(rating), 1) as avg_rating'),
        DB::raw('COUNT(*) as total_reviews')
    )
    ->where('approved', 1)
    ->groupBy('code', 'color')
    ->get()
    ->keyBy(function ($row) {
        return trim((string) $row->code) . '|' . trim((string) ($row->color ?? ''));
    });

    // ==============================
    // 2. PREPARAR CÓDIGOS
    // ==============================
    $codes = $catalogItems->pluck('code')
        ->filter()
        ->map(function ($v) {
            $v = trim((string) $v);
            return str_contains($v, '-') ? trim(explode('-', $v, 2)[0]) : $v;
        })
        ->unique()
        ->values()
        ->all();

    $inventario = collect();
    $existenciasPorProducto = collect();

    // ==============================
    // 3. SOLO AQUÍ ABRIMOS admin_ml
    // ==============================
    if (!empty($codes) && $mes !== '' && $tipo !== '') {
        $admin = DB::connection('admin_ml');

        try {
            $inventario = $admin
                ->table('inventario as i')
                ->whereIn(DB::raw('TRIM(i.Codprod)'), $codes)
                ->whereRaw('TRIM(i.mesyope) = ?', [$mes])
                ->whereRaw('TRIM(i.tipocatalogo) = ?', [$tipo])
                ->select([
                    'i.Codprod as code',
                    'i.color as color',
                    'i.Descripcion as name',
                    'i.Precventa as price',
                    'i.mesyope',
                    'i.tipocatalogo',
                ])
                ->get();

            $existencias = $admin
                ->table('inv_existencias as e')
                ->leftJoin('bodega as b', 'e.Bodega', '=', 'b.Codbodega')
                ->select(
                    DB::raw('TRIM(e.Codigo) as Codigo'),
                    DB::raw('TRIM(e.Color) as Color'),
                    'e.Bodega',
                    'b.Nombodega as tienda',
                    DB::raw('SUM(e.Saldo) as stock')
                )
                ->whereIn(DB::raw('TRIM(e.Codigo)'), $codes)
                ->where('e.Saldo', '>', 0)
                ->whereRaw("UPPER(TRIM(b.Nombodega)) <> 'MAL ESTADO'")
                ->groupBy(
                    DB::raw('TRIM(e.Codigo)'),
                    DB::raw('TRIM(e.Color)'),
                    'e.Bodega',
                    'b.Nombodega'
                )
                ->havingRaw('SUM(e.Saldo) > 0')
                ->orderBy('b.Nombodega')
                ->get();

            $existenciasPorProducto = $existencias
                ->groupBy(function ($row) {
                    return trim((string) $row->Codigo) . '|' . trim((string) $row->Color);
                })
                ->map(function ($rows) {
                    return $rows->map(function ($row) {
                        return [
                            'tienda' => $row->tienda ?: 'Sin nombre',
                            'stock' => (int) $row->stock,
                        ];
                    })->values();
                });

        } finally {
            $this->cerrarAdmin();
        }
    }

    // ==============================
    // 4. DESDE AQUÍ admin_ml YA ESTÁ CERRADO
    // ==============================
    $inventarioMap = $inventario->keyBy(function ($row) {
        return trim((string) $row->code) . '|' . trim((string) $row->color);
    });

$productos = $catalogItems->map(function ($item) use ($inventarioMap, $existenciasPorProducto, $ratings) {        $codeOriginal = trim((string) ($item->code ?? ''));
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

        $invExact = $inventarioMap->get($key);

        if (!$invExact && $lookupColor === '0') {
            $invExact = $inventarioMap->get($lookupCode . '|');
        }

        if (!$invExact && $lookupColor === '') {
            $invExact = $inventarioMap->get($lookupCode . '|0');
        }

        $name = trim((string) ($invExact->name ?? ''));
        $price = $invExact ? (float) ($invExact->price ?? 0) : 0;

        $existencias = $existenciasPorProducto->get($key);

        if (!$existencias && $lookupColor === '0') {
            $existencias = $existenciasPorProducto->get($lookupCode . '|');
        }

        if (!$existencias && $lookupColor === '') {
            $existencias = $existenciasPorProducto->get($lookupCode . '|0');
        }

        if (!$existencias) {
            $existencias = $existenciasPorProducto
                ->filter(function ($rows, $stockKey) use ($lookupCode) {
                    return str_starts_with($stockKey, $lookupCode . '|');
                })
                ->flatten(1)
                ->values();
        }

        $ratingKey = $lookupCode . '|' . $lookupColor;

$rating = $ratings->get($ratingKey);

if (!$rating && $lookupColor === '0') {
    $rating = $ratings->get($lookupCode . '|');
}

if (!$rating && $lookupColor === '') {
    $rating = $ratings->get($lookupCode . '|0');
}

       return (object) [
    'code' => $lookupCode,
    'color' => $lookupColor,
    'display_code' => $lookupCode . ($lookupColor !== '' ? '-' . $lookupColor : ''),
    'name' => $name !== '' ? $name : 'Producto sin descripción',
    'price' => $price,
    'quantity' => (int) ($item->quantity ?? 1),
    'page_number' => (int) ($item->page_number ?? 1),
    'position' => (int) ($item->position ?? 1),
    'existencias' => $existencias ?: collect(),

    'avg_rating' => $rating
        ? (float) $rating->avg_rating
        : 0,

    'total_reviews' => $rating
        ? (int) $rating->total_reviews
        : 0,
];
    });

    // ==============================
    // 5. COMBOS LOCALES
    // ==============================
    $combos = CatalogCombo::where('catalog_id', $catalog->id)
        ->get()
        ->map(function ($combo) use ($ratings) {
            $comboCode = trim((string) $combo->code);
            $comboColor = trim((string) $combo->color);

            $comboRating = $ratings->get($comboCode . '|' . $comboColor);

            return (object) [
                'code' => $comboCode,
                'color' => $comboColor,
                'display_code' => $comboCode . ($comboColor !== '' ? '-' . $comboColor : ''),
                'name' => $combo->name ?: 'Combo sin descripción',
                'price' => (float) ($combo->price ?? 0),
                'quantity' => 1,
                'page_number' => (int) ($combo->page_number ?? 1),
                'position' => (int) ($combo->position ?? 1),
                'is_combo' => true,
                'image_path' => $combo->image_path,
                'existencias' => collect(),

                'avg_rating' => $comboRating
                    ? (float) $comboRating->avg_rating
                    : 0,

                'total_reviews' => $comboRating
                    ? (int) $comboRating->total_reviews
                    : 0,
            ];
        });

    $productos = $productos->concat($combos);

    // ==============================
    // 6. ARMAR PRODUCTOS POR PÁGINA
    // ==============================
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

    $pagesRender = [];

    foreach ($pages as $pagina) {
        $pageNum = (int) $pagina->page_number;

        $items = $productosPorPagina->get($pageNum, collect());

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

    return $pagesRender;
}

public function pagesBlock(Request $request, $slug)
{
    $catalog = Catalogo::where('slug', $slug)
        ->where('is_public', true)
        ->firstOrFail();

    $offset = max(0, (int) $request->get('offset', 0));
    $limit = max(1, min(12, (int) $request->get('limit', 6)));

    $ratingsVersion = DB::table('product_reviews')->max('updated_at') ?? 'no-reviews';

    $cacheKey = "public_pages_render_catalog_{$catalog->id}_{$catalog->updated_at->timestamp}_ratings_" . md5((string) $ratingsVersion);

    $pagesRender = Cache::remember($cacheKey, 300, function () use ($catalog) {
        return $this->buildPublicPagesRender($catalog);
    });

    $slice = collect($pagesRender)->slice($offset, $limit)->values();

    $html = '';
    foreach ($slice as $renderPage) {
        $html .= view('catalogo.parcial.pagina', compact('renderPage'))->render();
    }

    return response()->json([
        'html' => $html,
        'count' => $slice->count(),
        'next_offset' => $offset + $slice->count(),
        'has_more' => ($offset + $slice->count()) < collect($pagesRender)->count(),
    ]);
}
   private function cerrarAdmin(): void
{
    DB::disconnect('admin_ml');
}
}
