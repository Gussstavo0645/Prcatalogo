<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoItem;
use Illuminate\Support\Facades\DB;

class PedidoPublicController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_cliente'   => 'required|string|max:255',
            'telefono_cliente' => 'required|string|max:50',
            'correo'           => 'nullable|email|max:255',
            'direccion'        => 'nullable|string|max:500',
            'ciudad'           => 'nullable|string|max:255',
            'entrega_tipo'     => 'nullable|string|max:100',
            'notas'            => 'nullable|string|max:1000',
            'pago_metodo'      => 'nullable|string|max:100',
            'requiere_factura' => 'nullable|string|max:10',

            'items'              => 'required|array|min:1',
            'items.*.code'       => 'required|string|max:255',
            'items.*.color'      => 'nullable|string|max:255',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.name'       => 'nullable|string|max:255',
            'items.*.price'      => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data) {

            $pedido = Pedido::create([
                'nombre_cliente'      => $data['nombre_cliente'],
                'telefono_cliente'    => $data['telefono_cliente'],
                'cliente_correo'      => $data['correo'] ?? null,
                'cliente_contraseña'  => null,
                'status'              => 'pendiente',
                'total'               => 0,

                // guardalos solo si estas columnas existen en pedidos
                'direccion'           => $data['direccion'] ?? null,
                'ciudad'              => $data['ciudad'] ?? null,
                'entrega_tipo'        => $data['entrega_tipo'] ?? null,
                'notas'               => $data['notas'] ?? null,
                'pago_metodo'         => $data['pago_metodo'] ?? null,
                'requiere_factura'    => $data['requiere_factura'] ?? null,
            ]);

            $total = 0;

            foreach ($data['items'] as $it) {
                $code  = trim((string) $it['code']);
                $color = trim((string) ($it['color'] ?? ''));
                $qty   = (int) $it['quantity'];

                $query = DB::connection('admin_ml')
                    ->table('inventario as i')
                    ->where('i.Codprod', $code)
                    ->where('i.mesyope', '03/2026')
                    ->where('i.tipocatalogo', 'N');

                if ($color !== '') {
                    $query->where('i.color', $color);
                }

                $producto = $query->select([
                        'i.Codprod as code',
                        'i.color as color',
                        'i.Descripcion as name',
                        'i.Precventa as price',
                    ])
                    ->first();

                if (!$producto) {
                    abort(422, "No se encontró el producto {$code} con color {$color}.");
                }

                $price = (float) $producto->price;
                $subtotal = $price * $qty;

                PedidoItem::create([
                    'pedidos_id'    => $pedido->id,
                    'product_code'  => $producto->code,
                    'product_color' => $producto->color,
                    'product_name'  => $producto->name,
                    'quantity'      => $qty,
                    'price'         => $price,
                    'subtotal'      => $subtotal,
                ]);

                $total += $subtotal;
            }

            $pedido->update(['total' => $total]);

            return response()->json([
                'ok' => true,
                'pedido_id' => $pedido->id,
                'total' => $total,
            ]);
        });
    }
}