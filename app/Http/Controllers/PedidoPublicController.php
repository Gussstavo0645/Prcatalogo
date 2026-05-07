<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class PedidoPublicController extends Controller
{
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'CodCliente'   => 'required|string|max:50',
                'Nombre'   => 'required|string|max:50',
                'Telefono' => 'required|string|max:255',
                'nit'   => 'nullable|string|max:30',
                'dpi'   => 'nullable|string|max:30',
                'correo'           => 'nullable|email|max:255',
                'direccion'        => 'nullable|string|max:500',
                'ciudad'           => 'nullable|string|max:255',
                'entrega_tipo'     => 'nullable|string|max:100',
                'notas'            => 'nullable|string|max:1000',
                'pago_metodo' => 'required|string|in:efectivo,cuotas,tarjeta,transferencia',
                'requiere_factura' => 'nullable|string|max:10',
                 'store_id' => 'required|exists:stores,id',
                'items'              => 'required|array|min:1',
                'items.*.code'       => 'required|string|max:255',
                'items.*.color'      => 'nullable|string|max:255',
                'items.*.quantity'   => 'required|integer|min:1',
                'items.*.name'       => 'nullable|string|max:255',
                'items.*.price'      => 'nullable|numeric|min:0',
            ]);

          $codigoCliente = trim($data['CodCliente']);

$clienteRemoto = DB::connection('admin_ml')
    ->table('clientes')
    ->whereRaw('TRIM(Codcliente) = ?', [$codigoCliente])
    ->select('Codcliente')
    ->first();

$clienteNoInscrito = null;

if (!$clienteRemoto) {
    $clienteNoInscrito = DB::connection('admin_ml')
        ->table('bodega')
        ->whereRaw('TRIM(CodigoClieNoInscr) = ?', [$codigoCliente])
        ->select('CodigoClieNoInscr')
        ->first();
}

if (!$clienteRemoto && !$clienteNoInscrito) {
    return response()->json([
        'message' => 'El código de cliente no existe o no está autorizado.'
    ], 422);
}

            return DB::transaction(function () use ($data) {

                $pedido = Pedido::create([
                    'store_id' => $data['store_id'],
                    'CodCliente'      => $data['CodCliente'],
                    'Nombre'      => $data['Nombre'],
                    'Telefono'    => $data['Telefono'],
                    'nit'    => $data['nit'] ?? null,
                    'dpi'    => $data['dpi'] ?? null,
                    'cliente_correo'      => $data['correo'] ?? null,
                    'pago_metodo'         => $data['pago_metodo'],
                    'cliente_contraseña'  => null,
                    'status'              => 'pendiente',
                    'total'               => 0,
                    
                ]);

                $total = 0;

             foreach ($data['items'] as $it) {
    $code  = trim((string) $it['code']);
    $color = trim((string) ($it['color'] ?? ''));
    $qty   = (int) $it['quantity'];

    // 1) Buscar si el producto comprado es un combo local
    $combo = DB::table('catalog_combos')
        ->where('code', $code)
        ->where('color', $color)
        ->first();

    // 2) Si ES combo, explotarlo en sus componentes
    if ($combo) {
        $componentes = DB::table('catalog_combo_items')
            ->where('combo_id', $combo->id)
            ->get();

        if ($componentes->isEmpty()) {
            return response()->json([
                'message' => "El combo {$code}-{$color} no tiene productos configurados."
            ], 422);
        }

        // El total del pedido se sigue calculando con el precio del combo
        $subtotalCombo = (float) $combo->price * $qty;
        $total += $subtotalCombo;
        $comboGroup = 'combo_' . $code . '_' . $color . '_' . uniqid();
        $comboName  = 'Combo ' . $code . '-' . $color;



       foreach ($componentes as $comp) {
    $compCode  = trim((string) $comp->product_code);
    $compColor = trim((string) ($comp->product_color ?? ''));
    $mes       = '04/2026';
    $tipo      = 'N';

    // 1) Buscar con tipo
    $queryComp = DB::connection('admin_ml')
        ->table('inventario as i')
        ->whereRaw('TRIM(i.Codprod) = ?', [$compCode])
        ->whereRaw('TRIM(i.mesyope) = ?', [$mes]);

    if ($compColor !== '') {
        $queryComp->whereRaw('TRIM(i.color) = ?', [$compColor]);
    }

    if ($tipo !== '') {
        $queryComp->whereRaw('TRIM(i.tipocatalogo) = ?', [$tipo]);
    }

    $productoComp = $queryComp->select([
        'i.Codprod as code',
        'i.color as color',
        'i.Descripcion as name',
        'i.Precventa as price',
    ])->first();

    // 2) Si no encuentra, buscar sin tipocatalogo
    if (!$productoComp) {
        $queryComp2 = DB::connection('admin_ml')
            ->table('inventario as i')
            ->whereRaw('TRIM(i.Codprod) = ?', [$compCode])
            ->whereRaw('TRIM(i.mesyope) = ?', [$mes]);

        if ($compColor !== '') {
            $queryComp2->whereRaw('TRIM(i.color) = ?', [$compColor]);
        }

        $productoComp = $queryComp2->select([
            'i.Codprod as code',
            'i.color as color',
            'i.Descripcion as name',
            'i.Precventa as price',
        ])->first();
    }

    if (!$productoComp) {
        return response()->json([
            'message' => "No se encontró el producto interno {$compCode} con color {$compColor} del combo {$code}-{$color}."
        ], 422);
    }
$comboPrice = (float) $combo->price;
$comboSubtotal = $comboPrice * $qty;

    PedidoItem::create([
    'pedidos_id'         => $pedido->id,
    'product_code'       => $productoComp->code,
    'product_color'      => $productoComp->color,
    'product_name'       => $productoComp->name,
    'quantity'           => ((int) $comp->quantity) * $qty,
    'price'         => $comboPrice,
    'subtotal'      => $comboSubtotal,
    'is_combo_component' => true,
    'combo_code'         => $code,
    'combo_color'        => $color,
    'combo_name'         => $comboName,
    'combo_group'        => $comboGroup,
    ]);
}
        continue;
    }

    // 3) Si NO es combo, guardar producto normal SIN consultar admin_ml
$price = (float) ($it['price'] ?? 0);
$name  = $it['name'] ?? 'Producto';
$subtotal = $price * $qty;

PedidoItem::create([
    'pedidos_id'         => $pedido->id,
    'product_code'       => $code,
    'product_color'      => $color,
    'product_name'       => $name,
    'quantity'           => $qty,
    'price'              => $price,
    'subtotal'           => $subtotal,
    'is_combo_component' => false,
    'combo_code'         => null,
    'combo_color'        => null,
    'combo_name'         => null,
    'combo_group'        => null,
]);


    $total += $subtotal;
}

             $pedido->update(['total' => $total]);

if ($data['pago_metodo'] === 'transferencia') {
    return response()->json([
        'ok' => true,
        'pedido_id' => $pedido->id,
        'total' => $total,
        'message' => 'Pedido creado. Te compartiremos los datos bancarios para la transferencia.'
    ]);
}

if (in_array($data['pago_metodo'], ['tarjeta', 'cuotas'])) {
    return response()->json([
        'ok' => true,
        'pedido_id' => $pedido->id,
        'total' => $total,
        'message' => 'Pedido creado. Método de pago en integración.'
    ]);
}

return response()->json([
    'ok' => true,
    'pedido_id' => $pedido->id,
    'total' => $total,
]);
            });

        } catch (\Throwable $e) {
             Log::error('ERROR PEDIDO', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }
}