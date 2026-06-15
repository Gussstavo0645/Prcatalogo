<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Catalogo;
use Illuminate\Support\Facades\DB;

class CatalogoPublicController extends Controller
{
    public function show(string $slug)
    {
        $catalog = Catalogo::where('slug', $slug)
            ->where('is_public', 1)
            ->firstOrFail();

        $mes = trim((string) ($catalog->mesyope ?? '03/2026'));
        $tipo = trim((string) ($catalog->tipocatalogo ?? 'N'));

        /*
        |--------------------------------------------------------------------------
        | Productos agregados al catálogo local
        |--------------------------------------------------------------------------
        | Aquí solo guardamos code, color, cantidad, página y posición.
        */
        $catalogItems = DB::table('catalog_products as cp')
            ->where('cp.catalog_id', $catalog->id)
            ->select(
                'cp.code',
                'cp.color',
                'cp.quantity',
                'cp.page_number',
                'cp.position'
            )
            ->orderBy('cp.page_number')
            ->orderByRaw('COALESCE(cp.position, 999999)')
            ->get();

        $codes = $catalogItems
            ->pluck('code')
            ->filter()
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Productos reales desde admin_ml
        |--------------------------------------------------------------------------
        */
        $adminProducts = collect();

        if ($codes->isNotEmpty()) {
            $adminProducts = DB::connection('admin_ml')
                ->table('inventario as i')
                ->whereIn('i.Codprod', $codes)
                ->where('i.mesyope', $mes)
                ->where('i.tipocatalogo', $tipo)
                ->select(
                    'i.Codprod as code',
                    'i.color',
                    'i.Descripcion as name',
                    'i.Precventa as price'
                )
                ->get()
                ->keyBy(function ($row) {
                    return trim((string) $row->code) . '|' . trim((string) ($row->color ?? ''));
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Promedio de calificaciones
        |--------------------------------------------------------------------------
        | Esto sigue viniendo de tu base local, tabla product_reviews.
        */
        $ratings = DB::table('product_reviews')
            ->select(
                'code',
                'color',
                DB::raw('AVG(rating) as avg_rating'),
                DB::raw('COUNT(*) as total_reviews')
            )
            ->where('approved', 1)
            ->groupBy('code', 'color')
            ->get()
            ->keyBy(function ($row) {
                return trim((string) $row->code) . '|' . trim((string) ($row->color ?? ''));
            });

        /*
        |--------------------------------------------------------------------------
        | Unimos catálogo local + admin_ml + calificaciones
        |--------------------------------------------------------------------------
        */
        $productos = $catalogItems->map(function ($item) use ($adminProducts, $ratings) {
            $code = trim((string) ($item->code ?? ''));
            $color = trim((string) ($item->color ?? ''));

            $key = $code . '|' . $color;

            $admin = $adminProducts->get($key);
            $rating = $ratings->get($key);

            return (object) [
                'code' => $code,
                'color' => $color,

                'name' => $admin->name ?? 'Producto sin nombre',
                'price' => $admin->price ?? 0,

                'quantity' => $item->quantity ?? 1,
                'page_number' => $item->page_number ?? 1,
                'position' => $item->position ?? null,

                'avg_rating' => $rating
                    ? round((float) $rating->avg_rating, 1)
                    : 0,

                'total_reviews' => $rating
                    ? (int) $rating->total_reviews
                    : 0,
            ];
        });

        $productosPorPagina = $productos->chunk(9);

        return view('catalogo.public', [
            'catalog' => $catalog,
            'publicView' => true,
            'productosPorPagina' => $productosPorPagina,
        ]);
    }
}