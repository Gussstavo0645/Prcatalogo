<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductReviewController extends Controller
{
    public function create($pedido)
    {
        $pedidoRow = DB::table('pedidos')
            ->where('id', $pedido)
            ->first();

        abort_if(!$pedidoRow, 404);

        $items = DB::table('pedidos_items')
            ->where('pedidos_id', $pedido)
            ->where(function ($q) {
                $q->whereNull('is_combo_component')
                  ->orWhere('is_combo_component', 0);
            })
            ->select(
                'product_code as code',
                'product_color as color',
                'product_name as name',
                'quantity',
                'price'
            )
            ->get();

        $reviews = DB::table('product_reviews')
            ->where('pedido_id', $pedido)
            ->get()
            ->keyBy(function ($review) {
                return trim($review->code) . '|' . trim($review->color ?? '');
            });

        $items = $items->map(function ($item) use ($reviews) {
            $key = trim($item->code ?? '') . '|' . trim($item->color ?? '');

            $item->rating = isset($reviews[$key])
                ? (int) $reviews[$key]->rating
                : 0;

            return $item;
        });

        return view('catalogo.calificar', compact('pedidoRow', 'items'));
    }

    public function storeItem(Request $request, $pedido)
    {
        $request->validate([
            'code' => 'required|string',
            'color' => 'nullable|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $pedidoRow = DB::table('pedidos')
            ->where('id', $pedido)
            ->first();

        if (!$pedidoRow) {
            return response()->json([
                'ok' => false,
                'message' => 'Pedido no encontrado.'
            ], 404);
        }

        $code = trim((string) $request->code);
        $color = trim((string) ($request->color ?? ''));
        $rating = (int) $request->rating;

        $itemExiste = DB::table('pedidos_items')
            ->where('pedidos_id', $pedido)
            ->where('product_code', $code)
            ->where(function ($q) use ($color) {
                if ($color === '') {
                    $q->whereNull('product_color')
                      ->orWhere('product_color', '');
                } else {
                    $q->where('product_color', $color);
                }
            })
            ->where(function ($q) {
                $q->whereNull('is_combo_component')
                  ->orWhere('is_combo_component', 0);
            })
            ->exists();

        if (!$itemExiste) {
            return response()->json([
                'ok' => false,
                'message' => 'Este producto no pertenece al pedido.'
            ], 422);
        }

        $keys = [
            'pedido_id' => $pedido,
            'code' => $code,
            'color' => $color,
        ];

        $existeReview = DB::table('product_reviews')
            ->where($keys)
            ->exists();

        $data = [
            'rating' => $rating,
            'comment' => null,
            'approved' => 1,
            'updated_at' => now(),
        ];

        if (!$existeReview) {
            $data['created_at'] = now();
        }

        DB::table('product_reviews')->updateOrInsert($keys, $data);

        return response()->json([
            'ok' => true,
            'message' => 'Calificación guardada.',
            'rating' => $rating,
        ]);
    }
}