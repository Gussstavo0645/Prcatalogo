<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\PremioEntregado;
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
               'pago_metodo' => 'required|in:efectivo,transferencia,neopay',
                'requiere_factura' => 'nullable|string|max:10',
                 'store_id' => 'required|exists:stores,id',
'catalog_id' => 'required|exists:catalogs,id',
'items'              => 'required|array|min:1',
                'items.*.code'       => 'required|string|max:255',
                'items.*.color'      => 'nullable|string|max:255',
                'items.*.quantity'   => 'required|integer|min:1',
                'items.*.name'       => 'nullable|string|max:255',
                'items.*.price'      => 'nullable|numeric|min:0',
            ]);


 

          $codigoCliente = trim($data['CodCliente']);

$clienteNoInscrito = DB::connection('admin_ml')
    ->table('bodega')
    ->whereRaw('TRIM(CodigoClieNoInscr) = ?', [$codigoCliente])
    ->select('CodigoClieNoInscr')
    ->first();

$clienteRemoto = null;

if (!$clienteNoInscrito) {
    $clienteRemoto = DB::connection('admin_ml')
        ->table('clientes')
        ->whereRaw('TRIM(Codcliente) = ?', [$codigoCliente])
        ->select('Codcliente')
        ->first();
}

 if (!$clienteRemoto && !$clienteNoInscrito) {
    return response()->json([
        'message' => 'El código de cliente no existe o no está autorizado.'
    ], 422);
 }

 $esClienteNoInscrito = (bool) $clienteNoInscrito;

$totalInicial = collect($data['items'])->sum(function ($item) {
    return (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1);
});

// Solo clientes inscritos tienen mínimo de Q225
if (!$esClienteNoInscrito && $totalInicial < 225) {
    return response()->json([
        'message' => 'El pedido mínimo para clientes inscritos es de Q225.00.'
    ], 422);
}

          return DB::transaction(function () use ($data, $esClienteNoInscrito) {

    $catalogoPedido = DB::table('catalogs')
        ->where('id', $data['catalog_id'])
        ->first();

    if (!$catalogoPedido) {
        return response()->json([
            'message' => 'No se encontró el catálogo del pedido.'
        ], 422);
    }

    $mesCatalogo = trim((string) $catalogoPedido->mesyope);
    $tipoCatalogo = trim((string) $catalogoPedido->tipocatalogo);

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
    ->where('catalog_id', $data['catalog_id'])
    ->whereRaw('TRIM(code) = ?', [$code])
    ->whereRaw('TRIM(color) = ?', [$color])
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
       $comboPrice = (float) ($combo->price ?? 0);

if ($comboPrice <= 0) {
    $comboPrice = (float) ($it['price'] ?? 0);
}

if ($comboPrice <= 0) {
    return response()->json([
        'message' => "El combo {$code}-{$color} no tiene precio configurado."
    ], 422);
}

$subtotalCombo = $comboPrice * $qty;
$total += $subtotalCombo;
        $comboGroup = 'combo_' . $code . '_' . $color . '_' . uniqid();
        $comboName  = 'Combo ' . $code . '-' . $color;



       foreach ($componentes as $comp) {
    $compCode  = trim((string) $comp->product_code);
    $compColor = trim((string) ($comp->product_color ?? ''));
   $mes  = $mesCatalogo;
$tipo = $tipoCatalogo;

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

if ($price <= 0) {
    return response()->json([
        'message' => "El producto {$code}-{$color} viene sin precio. No se puede crear el pedido."
    ], 422);
}

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

 $metodoPago = $data['pago_metodo'];

$pagoEstado = match ($metodoPago) {
    'neopay' => 'pendiente_pago',
    'transferencia' => 'pendiente_confirmacion',
    default => 'pendiente',
};

$pagoGateway = match ($metodoPago) {
    'neopay' => 'neopay',
    'transferencia' => 'transferencia',
    default => 'efectivo',
};

$pedido->update([
    'total' => $total,
    'pago_metodo' => $metodoPago,
    'pago_estado' => $pagoEstado,
    'pago_gateway' => $pagoGateway,
    'pago_monto' => $total,
    'pago_moneda' => 'GTQ',
]);

$metodosContadoActual = ['efectivo', 'transferencia', 'neopay'];

if ($esClienteNoInscrito) {
    $premioInfo = [
        'cliente_no_inscrito' => true,
        'aplica' => false,
        'ya_entregado' => false,
        'total_acumulado' => 0,
        'total_pedido_actual' => 0,
        'total_proyectado' => 0,
        'faltante_c1' => 0,
        'faltante_c2' => 0,
        'mensaje' => 'Cliente no inscrito: no acumula compras y no aplica a premio.',
        'premio' => null,
    ];
}
 else {
    $totalParaPremio = in_array($data['pago_metodo'], $metodosContadoActual, true)
        ? $total
        : 0;

    $premioInfo = $this->evaluarPremioDisponible($pedido->CodCliente, $totalParaPremio);
}
if ($data['pago_metodo'] === 'neopay') {
    return response()->json([
        'ok' => true,
        'pedido_id' => $pedido->id,
        'total' => $total,
        'premio' => $premioInfo,
        'pago_metodo' => 'neopay',
        'pago_estado' => 'pendiente_pago',
        'requiere_pago_online' => true,
        'message' => 'Pedido creado. Continuando a pago en línea con NeoPay.'
    ]);
}

if ($data['pago_metodo'] === 'transferencia') {
    return response()->json([
        'ok' => true,
        'pedido_id' => $pedido->id,
        'total' => $total,
        'premio' => $premioInfo,
        'pago_metodo' => 'transferencia',
        'pago_estado' => 'pendiente_confirmacion',
        'requiere_pago_online' => false,
        'message' => 'Pedido creado. Te compartiremos los datos bancarios para la transferencia.'
    ]);
}

return response()->json([
    'ok' => true,
    'pedido_id' => $pedido->id,
    'total' => $total,
    'premio' => $premioInfo,
    'pago_metodo' => 'efectivo',
    'pago_estado' => 'pendiente',
    'requiere_pago_online' => false,
    'message' => 'Pedido creado correctamente.'
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

   private function evaluarPremioDisponible(string $codCliente, float $totalPedidoActual = 0): array
 {
    $mesope = now()->format('m/Y');

    $inicioMes = now()->copy()->startOfMonth();
    $finMes = now()->copy()->endOfMonth();

    $metodosContado = [
        'efectivo',
        'tarjeta',
        'transferencia',
    ];

    // 1. Total confirmado actual
    $totalConfirmado = Pedido::query()
        ->where('CodCliente', $codCliente)
        ->whereBetween('created_at', [$inicioMes, $finMes])
        ->whereIn('pago_metodo', $metodosContado)
        ->whereIn('status', ['confirmado', 'entregado'])
        ->sum('total');
    // 2. Total proyectado si este pedido se confirma
    $totalProyectado = (float) $totalConfirmado + (float) $totalPedidoActual;

    // 3. Verificar si ya recibió premio este mes
    $premioEntregado = PremioEntregado::query()
        ->where('CodCliente', $codCliente)
        ->where('mesope', $mesope)
        ->where('estado', 'entregado')
        ->first();

    if ($premioEntregado) {
        return [
            'aplica' => false,
            'ya_entregado' => true,
            'total_acumulado' => $totalConfirmado,
            'total_pedido_actual' => $totalPedidoActual,
            'total_proyectado' => $totalProyectado,
            'faltante_c1' => 0,
            'faltante_c2' => 0,
            'mensaje' => 'Este cliente ya recibió premio este mes.',
            'premio' => null,
        ];
    }

    // 4. Buscar premio usando el total proyectado
    $premio = DB::connection('admin_ml')
        ->table('masterpremios')
        ->whereRaw('TRIM(MESOPE) = ?', [$mesope])
        ->whereRaw('TRIM(MESENTREGA) = ?', [$mesope])
        ->whereIn('CODTPRODUCTO', ['C1', 'C2'])
        ->where('VALORMIN', '<=', $totalProyectado)
        ->where('VALORMAX', '>=', $totalProyectado)
        ->whereRaw("TRIM(fuera_caja) = 'N'")
        ->orderByDesc('VALORMIN')
        ->first();

    if (!$premio) {
        return [
            'aplica' => false,
            'ya_entregado' => false,
            'total_acumulado' => $totalConfirmado,
            'total_pedido_actual' => $totalPedidoActual,
            'total_proyectado' => $totalProyectado,
            'faltante_c1' => max(425 - $totalProyectado, 0),
            'faltante_c2' => max(725 - $totalProyectado, 0),
            'mensaje' => 'El cliente todavía no alcanza premio.',
            'premio' => null,
        ];
    }

    return [
        'aplica' => true,
        'ya_entregado' => false,
        'total_acumulado' => $totalConfirmado,
        'total_pedido_actual' => $totalPedidoActual,
        'total_proyectado' => $totalProyectado,
        'faltante_c1' => max(425 - $totalProyectado, 0),
        'faltante_c2' => max(725 - $totalProyectado, 0),
        'mensaje' => 'Con este pedido, al ser confirmado, el cliente aplica a premio.',
        'premio' => [
            'codtproducto' => trim($premio->CODTPRODUCTO),
            'descripcion' => trim($premio->DESCRIP_PREMIO ?? $premio->DESCRIPCION ?? ''),
            'producto' => trim($premio->PRODUCTO),
            'color' => trim($premio->COLOR),
            'cantidad' => (int) $premio->CANTIDADASIG,
            'valormin' => $premio->VALORMIN,
            'valormax' => $premio->VALORMAX,
            'mesope' => trim($premio->MESOPE),
            'mesentrega' => trim($premio->MESENTREGA),
        ],
    ];
 }

 }
