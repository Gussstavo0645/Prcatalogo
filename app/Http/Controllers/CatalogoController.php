<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\PaginaCatalogo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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

    $mes = '03/2026';
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
        $code = trim((string)($item->code ?? ''));
        $color = trim((string)($item->color ?? ''));
        $key = $code . '|' . $color;

        // 1. intento exacto: code + color
        $inv = $inventarioMap->get($key);

        // 2. fallback: solo code
        $nombre = trim((string)($inv->name ?? ''));
        if (!$inv || $nombre === '') {
            $inv = $inventarioByCode->get($code);
            $nombre = trim((string)($inv->name ?? ''));
        }

        // código para mostrar
        $displayCode = $code;
        if ($code !== '' && $color !== '' && $color !== '0' && !str_contains($code, '-')) {
            $displayCode = $code . '-' . $color;
        }

        return (object) [
            'code' => $code,
            'color' => $color,
            'display_code' => $displayCode,
            'name' => $nombre !== '' ? $nombre : 'Producto sin descripción',
            'price' => (float)($inv->price ?? 0),
            'quantity' => (int)($item->quantity ?? 1),
            'page_number' => (int)($item->page_number ?? 1),
            'position' => (int)($item->position ?? 1),
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
    $data = Cache::remember("catalogo_publico_{$slug}", 300, function () use ($slug) {
        $catalog = Catalogo::where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        $mes = '03/2026';
        $tipo = 'N';

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

            return compact('catalog', 'pagesRender');
        }

        $codes = $catalogItems->pluck('code')
            ->filter()
            ->map(fn($v) => trim((string) $v))
            ->unique()
            ->values();

        $baseCodes = $catalogItems->pluck('code')
            ->filter()
            ->map(function ($v) {
                $v = trim((string) $v);
                return str_contains($v, '-') ? trim(explode('-', $v, 2)[0]) : $v;
            })
            ->filter()
            ->unique()
            ->values();

        $allCodes = $codes->merge($baseCodes)->unique()->values();

        $inventario = DB::connection('admin_ml')
            ->table('inventario as i')
            ->where('i.mesyope', $mes)
            ->where('i.tipocatalogo', $tipo)
            ->whereIn('i.Codprod', $allCodes)
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
            $code = trim((string) ($item->code ?? ''));
            $color = trim((string) ($item->color ?? ''));

            $lookupCode = $code;
            $lookupColor = $color;

            if (str_contains($code, '-')) {
                $partes = explode('-', $code, 2);
                $codigoBase = trim((string) ($partes[0] ?? ''));
                $colorDesdeCodigo = trim((string) ($partes[1] ?? ''));

                if ($codigoBase !== '' && $colorDesdeCodigo !== '') {
                    $lookupCode = $codigoBase;
                    $lookupColor = $colorDesdeCodigo;
                }
            }

            $key = $lookupCode . '|' . $lookupColor;
            $inv = $inventarioMap->get($key);

            $nombre = trim((string) ($inv->name ?? ''));
            if (!$inv || $nombre === '') {
                $inv = $inventarioByCode->get($lookupCode);
                $nombre = trim((string) ($inv->name ?? ''));
            }

            $displayCode = $code;
            if (!str_contains($code, '-') && $lookupColor !== '' && $lookupColor !== '0') {
                $displayCode = $code . '-' . $lookupColor;
            }

            return (object) [
                'code' => $lookupCode,
                'color' => $lookupColor,
                'display_code' => $displayCode,
                'name' => $nombre !== '' ? $nombre : 'Producto sin descripción',
                'price' => (float) ($inv->price ?? 0),
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

        return compact('catalog', 'pagesRender');
    });

    return view('catalogo.public', $data);
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

    $row = null;

    if ($colorBusqueda !== '' && $colorBusqueda !== '0') {
        $row = DB::connection('admin_ml')
            ->table('inv_fotos')
            ->where('codigo', $codigoBusqueda)
            ->where('color', $colorBusqueda)
            ->select('foto')
            ->first();
    }

    if (!$row || empty($row->foto)) {
        $row = DB::connection('admin_ml')
            ->table('inv_fotos')
            ->where('codigo', $codigoBusqueda)
            ->whereNotNull('foto')
            ->select('foto')
            ->first();
    }

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

        // Si el código viene tipo DSI-8 o 1012-6
        if (str_contains($code, '-')) {
            $partes = explode('-', $code, 2);

            $codigoBase = trim((string) ($partes[0] ?? ''));
            $colorDesdeCodigo = trim((string) ($partes[1] ?? ''));

            if ($codigoBase !== '' && $colorDesdeCodigo !== '') {
                $codigoBusqueda = $codigoBase;
                $colorBusqueda = $colorDesdeCodigo;
            }
        }

        $row = null;

        // 1) intento exacto: codigo + color
        if ($colorBusqueda !== '' && $colorBusqueda !== '0') {
            $row = DB::connection('admin_ml')
                ->table('inv_fotos')
                ->where('codigo', $codigoBusqueda)
                ->where('color', $colorBusqueda)
                ->select('foto')
                ->first();
        }

        // 2) fallback: solo por código base
        if (!$row || empty($row->foto)) {
            $row = DB::connection('admin_ml')
                ->table('inv_fotos')
                ->where('codigo', $codigoBusqueda)
                ->whereNotNull('foto')
                ->select('foto')
                ->first();
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

}