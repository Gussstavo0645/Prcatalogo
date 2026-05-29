<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Throwable;

class AdminMlCatalogoBridgeService
{
    public function enviarPedido(Pedido $pedido): int
    {
        // Si ya fue enviado, no lo duplicamos
        $existente = DB::connection('admin_ml')
            ->table('web_catalogo_pedidos')
            ->where('pedido_local_id', $pedido->id)
            ->first();

        if ($existente) {
            DB::table('pedidos')
                ->where('id', $pedido->id)
                ->update([
                    'admin_ml_web_pedido_id' => $existente->id,
                    'estado_admin_ml' => $existente->estado ?? 'enviado',
                    'error_admin_ml' => null,
                    'updated_at' => now(),
                ]);

            return $existente->id;
        }

        try {
            $webPedidoId = DB::connection('admin_ml')->transaction(function () use ($pedido) {

                $store = null;

                if (!empty($pedido->store_id)) {
                    $store = DB::table('stores')
                        ->where('id', $pedido->store_id)
                        ->first();
                }

                [$mesope, $tipocatalogo] = $this->obtenerMesYTipoCatalogo($pedido);

                // 1. Insertar encabezado del pedido en admin_ml
                $webPedidoId = DB::connection('admin_ml')
                    ->table('web_catalogo_pedidos')
                    ->insertGetId([
                        'pedido_local_id' => $pedido->id,
                        'origen' => 'catalogo_web',

                        'codcliente' => $this->attr($pedido, ['CodCliente', 'codcliente', 'cod_cliente']),
                        'tipo_cliente' => $this->attr($pedido, ['tipo_cliente']),
                        'nombre_cliente' => $this->attr($pedido, ['Nombre', 'nombre', 'cliente_nombre']),
                        'telefono' => $this->attr($pedido, ['Telefono', 'telefono']),
                        'whatsapp' => $this->attr($pedido, ['whatsapp']),
                        'nit' => $this->attr($pedido, ['nit', 'NIT']),
                        'dpi' => $this->attr($pedido, ['dpi', 'DPI']),
                        'correo' => $this->attr($pedido, ['correo', 'email']),
                        'direccion' => $this->attr($pedido, ['direccion', 'Direccion']),
                        'ciudad' => $this->attr($pedido, ['ciudad', 'Ciudad']),

                        'store_id' => $pedido->store_id ?? null,
                        'store_name' => $store->name ?? $store->nombre ?? null,
                        'bodega_codigo' => $store->bodega_codigo ?? null,

                        'mesope' => $mesope,
                        'tipocatalogo' => $tipocatalogo,

                        'entrega_tipo' => $this->attr($pedido, ['entrega_tipo']),
                        'pago_metodo' => $this->attr($pedido, ['pago_metodo']),
                        'requiere_factura' => (int) ($this->attr($pedido, ['requiere_factura'], 0)),
                        'notas' => $this->attr($pedido, ['notas']),

                        'total' => $pedido->total ?? 0,
                        'estado' => 'pendiente',
                        'enviado_at' => now(),

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                // 2. Obtener productos visibles del pedido local
               // 2. Obtener todos los items del pedido local
$todosItems = DB::table('pedidos_items')
    ->where('pedidos_id', $pedido->id)
    ->orderBy('id')
    ->get();

$lineOrder = 1;

// 3. Primero procesamos combos agrupados por combo_group
$combosAgrupados = $todosItems
    ->filter(function ($item) {
        return (int)($item->is_combo_component ?? 0) === 1
            && !empty($item->combo_group);
    })
    ->groupBy('combo_group');

foreach ($combosAgrupados as $comboGroup => $componentes) {
    $primero = $componentes->first();

    $comboCode = trim((string) $primero->combo_code);
    $comboColor = $this->normalizarColor($primero->combo_color ?? 0);
    $comboName = $primero->combo_name ?? ('Combo ' . $comboCode . '-' . $comboColor);

    // En tu estructura actual no existe fila padre.
    // Tomamos la cantidad de la primera línea como cantidad del combo.
    $comboCantidad = (float) ($primero->quantity ?? 1);
    if ($comboCantidad <= 0) {
        $comboCantidad = 1;
    }

    $comboPrice = (float) ($primero->price ?? 0);
    $comboSubtotal = $comboCantidad * $comboPrice;

    // Insertar línea visible del combo en admin_ml
    $webItemId = DB::connection('admin_ml')
        ->table('web_catalogo_pedido_items')
        ->insertGetId([
            'web_pedido_id' => $webPedidoId,
            'pedido_item_local_id' => null,

            'product_code' => $comboCode,
            'product_color' => $comboColor,
            'product_name' => $comboName,

            'quantity' => $comboCantidad,
            'precio' => $comboPrice,
            'subtotal' => $comboSubtotal,

            'is_combo' => 1,
            'line_order' => $lineOrder++,

            'created_at' => now(),
            'updated_at' => now(),
        ]);

    // Insertar componentes internos del combo
    foreach ($componentes as $comp) {
        $cantidadTotal = (float) ($comp->quantity ?? 1);

        $cantidadPorCombo = $comboCantidad > 0
            ? $cantidadTotal / $comboCantidad
            : $cantidadTotal;

        DB::connection('admin_ml')
            ->table('web_catalogo_pedido_componentes')
            ->insert([
                'web_pedido_id' => $webPedidoId,
                'web_item_id' => $webItemId,

                'combo_code' => $comboCode,
                'combo_color' => $comboColor,
                'combo_name' => $comboName,

                'component_code' => trim((string) $comp->product_code),
                'component_color' => $this->normalizarColor($comp->product_color ?? 0),
                'component_name' => $comp->product_name ?? null,

                'cantidad_por_combo' => $cantidadPorCombo,
                ' cantidad_total' => $cantidadTotal,

                'created_at' => now(),
            ]);
    }
}

// 4. Luego procesamos productos normales
$productosNormales = $todosItems
    ->filter(function ($item) {
        return (int)($item->is_combo_component ?? 0) === 0;
    });

foreach ($productosNormales as $item) {
    DB::connection('admin_ml')
        ->table('web_catalogo_pedido_items')
        ->insert([
            'web_pedido_id' => $webPedidoId,
            'pedido_item_local_id' => $item->id,

            'product_code' => trim((string) $item->product_code),
            'product_color' => $this->normalizarColor($item->product_color ?? 0),
            'product_name' => $item->product_name ?? null,

            'quantity' => $item->quantity ?? 1,
            'price' => $item->price ?? 0,
            'subtotal' => $item->subtotal ?? (($item->quantity ?? 1) * ($item->price ?? 0)),

            'is_combo' => 0,
            'line_order' => $lineOrder++,

            'created_at' => now(),
            'updated_at' => now(),
        ]);
}
return $webPedidoId;
            });

            // 5. Marcar pedido local como enviado
            DB::table('pedidos')
                ->where('id', $pedido->id)
                ->update([
                    'admin_ml_web_pedido_id' => $webPedidoId,
                    'enviado_admin_ml_at' => now(),
                    'estado_admin_ml' => 'enviado',
                    'error_admin_ml' => null,
                    'updated_at' => now(),
                ]);

            return $webPedidoId;

        } catch (Throwable $e) {
            DB::table('pedidos')
                ->where('id', $pedido->id)
                ->update([
                    'estado_admin_ml' => 'error',
                    'error_admin_ml' => $e->getMessage(),
                    'updated_at' => now(),
                ]);

            throw $e;
        }
    }

    private function attr($model, array $names, $default = null)
    {
        foreach ($names as $name) {
            $value = $model->getAttribute($name);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    }

    private function normalizarColor($color): string
    {
        $color = trim((string) ($color ?? '0'));

        return $color === '' ? '0' : $color;
    }

    private function obtenerMesYTipoCatalogo(Pedido $pedido): array
    {
        $mesope = $this->attr($pedido, ['mesope', 'mesyope', 'Mesope']);
        $tipocatalogo = $this->attr($pedido, ['tipocatalogo', 'tipo_catalogo']);

        $catalogoId = $this->attr($pedido, ['catalogo_id', 'catalog_id']);

        if ((!$mesope || !$tipocatalogo) && $catalogoId) {
            $catalogo = DB::table('catalogos')
                ->where('id', $catalogoId)
                ->first();

            if ($catalogo) {
                $mesope = $mesope
                    ?: ($catalogo->mesope ?? $catalogo->mesyope ?? null);

                $tipocatalogo = $tipocatalogo
                    ?: ($catalogo->tipocatalogo ?? $catalogo->tipo_catalogo ?? null);
            }
        }

        return [$mesope, $tipocatalogo];
    }
}