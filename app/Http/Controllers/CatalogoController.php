<?php

namespace App\Http\Controllers;

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
        $catalogs = Catalogo::where('is_public', true)->latest()->get();
        return view('catalogo.index', compact('catalogs'));
    }

    /*VER CATALOGO
    */
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

    $pagesRender = Cache::remember("catalogo_publico_{$catalog->id}", 300, function () use ($catalog) {
        return $this->buildPublicPagesRender($catalog);
    });

    return view('catalogo.public', compact('catalog', 'pagesRender'));
}

public function productoImagen($code, $color = null)
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

    $query = DB::connection('admin_ml')
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
}

public function productoImagenLarge($code, $color = null)
{
    $code = trim((string) $code);
    $color = trim((string) ($color ?? ''));

    $cacheKey = "producto_large_{$code}_{$color}";

    $imageBinary = Cache::remember($cacheKey, 86400, function () use ($code, $color) {

        $codigoBusqueda = $code;
        $colorBusqueda = $color;

        if (str_contains($code, '-')) {
            $partes = explode('-', $code, 2);

            $codigoBusqueda = trim((string) ($partes[0] ?? ''));
            $colorBusqueda = trim((string) ($partes[1] ?? ''));
        }

        $query = DB::connection('admin_ml')
            ->table('inv_fotos')
            ->where('codigo', $codigoBusqueda);

        if ($colorBusqueda !== '' && $colorBusqueda !== '0') {
            $query->where('color', $colorBusqueda);
        }

        $row = $query->select('foto')->first();

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

    $query = DB::connection('admin_ml')
        ->table('inv_fotos')
        ->whereRaw('TRIM(codigo) = ?', [$codigoBusqueda]);

    if ($colorBusqueda !== '' && $colorBusqueda !== '0') {
        $query->whereRaw('TRIM(color) = ?', [$colorBusqueda]);
    }

    $row = $query->select('foto')->first();

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
    $mes = $catalog->mesyope ?? '04/2026';
    $tipo = $catalog->tipocatalogo ?? 'N';

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

    if ($catalogItems->isEmpty()) {
        $pagesRender = [];

        foreach ($pages as $pagina) {
            $pagesRender[] = [
                'pagina' => $pagina,
                'page_number_label' => (int) $pagina->page_number,
                'items' => collect(),
                'chunk_index' => 0,
            ];
        }

        return $pagesRender;
    }

    $codes = $catalogItems->pluck('code')
        ->filter()
        ->map(function ($v) {
            $v = trim((string) $v);
            return str_contains($v, '-') ? trim(explode('-', $v, 2)[0]) : $v;
        })
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

        $invExact = $inventarioMap->get($key);
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

    $pagesRender = Cache::remember("catalogo_publico_{$catalog->id}", 300, function () use ($catalog) {
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
}