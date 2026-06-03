<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Pedido;
use App\Models\PremioEntregado;

class ClientePublicController extends Controller
{
    public function detectar($codcliente)
    {
        $codcliente = trim($codcliente);

        if ($codcliente === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Debe ingresar un código de cliente.'
            ], 422);
        }

        // 1. Buscar primero en bodega como cliente no inscrito
        $clienteNoInscrito = DB::connection('admin_ml')
            ->table('bodega')
            ->whereRaw('UPPER(TRIM(CodigoClieNoInscr)) = ?', [strtoupper($codcliente)])
            ->select('CodigoClieNoInscr')
            ->first();

        if ($clienteNoInscrito) {
            return response()->json([
                'ok' => true,
                'message' => 'Código de cliente no inscrito válido. No tiene mínimo de compra, no acumula y no aplica a premios.',
                'tipo' => 'cliente_no_inscrito',
                'cliente' => [
                    'CodCliente' => $clienteNoInscrito->CodigoClieNoInscr,
                ]
            ]);
        }

        // 2. Si no está en bodega, buscar en clientes inscritos
       $cliente = DB::connection('admin_ml')
    ->table('clientes')
    ->whereRaw('UPPER(TRIM(CodCliente)) = ?', [strtoupper($codcliente)])
    ->select('Codcliente', 'Nom1cliente', 'Ape1cliente')
    ->first();

        if ($cliente) {
            return response()->json([
                'ok' => true,
                'message' => 'Código de cliente inscrito válido. Aplica mínimo de Q225 y puede acumular premios.',
                'tipo' => 'cliente',
                'cliente' => [
    'CodCliente' => $cliente->Codcliente,
    'Nombre' => trim(($cliente->Nom1cliente ?? '') . ' ' . ($cliente->Ape1cliente ?? '')),
]
            ]);
        }

        return response()->json([
            'ok' => false,
            'message' => 'Código de cliente no encontrado.'
        ], 404);
    }

    public function acumulado(Request $request, $codcliente)
{
    $codcliente = trim($codcliente);
    

    if ($codcliente === '') {
        return response()->json([
            'ok' => false,
            'message' => 'Debe ingresar un código de cliente.'
        ], 422);
    }

    // 1. Revisar primero si es cliente no inscrito
    $clienteNoInscrito = DB::connection('admin_ml')
        ->table('bodega')
        ->whereRaw('UPPER(TRIM(CodigoClieNoInscr)) = ?', [strtoupper($codcliente)])
        ->select('CodigoClieNoInscr')
        ->first();

    if ($clienteNoInscrito) {
        return response()->json([
            'ok' => true,
            'tipo' => 'cliente_no_inscrito',
            'message' => 'Cliente no inscrito: no acumula compras y no aplica a premios.',
            'acumulado' => 0,
            'puntos' => 0,
            'faltante_c1' => 0,
            'faltante_c2' => 0,
            'opciones_premio' => [],
            'mejor_opcion' => null,
        ]);
    }
                                                                                                                                                   
    // 2. Buscar cliente inscrito
    $cliente = DB::connection('admin_ml')
    ->table('clientes')
    ->whereRaw('UPPER(TRIM(CodCliente)) = ?', [strtoupper($codcliente)])
    ->select('Codcliente', 'Nom1cliente', 'Ape1cliente')
    ->first();

    if (!$cliente) {
        return response()->json([
            'ok' => false,
            'message' => 'Código de cliente no encontrado.'
        ], 404);
    }

$mesope = $request->query('mesope', now()->format('m/Y'));

if (!preg_match('/^\d{1,2}\/\d{4}$/', $mesope)) {
    $mesope = now()->format('m/Y');
}

[$mes, $anio] = explode('/', $mesope);

$inicioMes = Carbon::create((int) $anio, (int) $mes, 1)->startOfMonth();
$finMes = Carbon::create((int) $anio, (int) $mes, 1)->endOfMonth();

    // 3. Buscar último canje del mes
 $ultimoCanje = PremioEntregado::query()
    ->whereRaw('UPPER(TRIM(CodCliente)) = ?', [strtoupper($codcliente)])
    ->where('mesope', $mesope)
    ->whereIn('estado', ['entregado', 'canjeado'])
    ->whereNotNull('fecha_canje')
    ->where('monto_usado', '>', 0)
    ->latest('fecha_canje')
    ->latest('created_at')
    ->first();

    // 4. Sumar compras confirmadas/entregadas
  // 4. Sumar compras confirmadas y pagadas del mes
$query = Pedido::query()
    ->whereRaw('UPPER(TRIM(CodCliente)) = ?', [strtoupper($codcliente)])
    ->whereBetween('created_at', [$inicioMes, $finMes])

    // Estado del pedido
    ->whereIn('status', ['confirmado', 'enviado', 'entregado'])

    // Estado del pago
    ->whereRaw("UPPER(TRIM(COALESCE(pago_estado, ''))) = ?", ['PAGADO'])

    // Métodos de pago que cuentan como compra pagada / contado
    ->whereIn(DB::raw("UPPER(TRIM(COALESCE(pago_metodo, '')))"), [
        'EFECTIVO',
        'TRANSFERENCIA',
        'NEOPAY',
        'TARJETA',
        'CONTADO',
        'POS'
    ]);

    // Si ya canjeó, solo acumula compras después del último canje
    if ($ultimoCanje) {
        $fechaCorte = $ultimoCanje->fecha_canje ?? $ultimoCanje->created_at;
        $query->where('created_at', '>', $fechaCorte);
    }

    $acumulado = (float) $query->sum('total');

    $opciones = $this->calcularOpcionesPremios($acumulado);

    return response()->json([
        'ok' => true,
        'tipo' => 'cliente',
        'message' => 'Acumulado encontrado.',
        'acumulado' => $acumulado,
        'puntos' => $acumulado,
        'valor_c1' => 425,
        'valor_c2' => 725,
        'faltante_c1' => max(425 - $acumulado, 0),
        'faltante_c2' => max(725 - $acumulado, 0),
        'tiene_corte' => (bool) $ultimoCanje,
        'ultimo_canje' => $ultimoCanje ? [
            'fecha' => $ultimoCanje->fecha_canje ?? $ultimoCanje->created_at,
            'cantidad_c1' => (int) $ultimoCanje->cantidad_c1,
            'cantidad_c2' => (int) $ultimoCanje->cantidad_c2,
            'monto_usado' => (float) $ultimoCanje->monto_usado,
        ] : null,
        'opciones_premio' => $opciones,
        'mejor_opcion' => $opciones[0] ?? null,
    ]);
}

private function calcularOpcionesPremios(float $disponible): array
{
    $valorC1 = 425;
    $valorC2 = 725;

    $opciones = [];

    $maxC2 = (int) floor($disponible / $valorC2);

    for ($c2 = 0; $c2 <= $maxC2; $c2++) {
        $restanteDespuesC2 = $disponible - ($c2 * $valorC2);
        $maxC1 = (int) floor($restanteDespuesC2 / $valorC1);

        for ($c1 = 0; $c1 <= $maxC1; $c1++) {
            if ($c1 === 0 && $c2 === 0) {
                continue;
            }

            $montoUsado = ($c1 * $valorC1) + ($c2 * $valorC2);
            $saldoRestante = $disponible - $montoUsado;

            $opciones[] = [
                'cantidad_c1' => $c1,
                'cantidad_c2' => $c2,
                'monto_usado' => round($montoUsado, 2),
                'saldo_restante' => round($saldoRestante, 2),
                'faltante_para_otro_c1' => round(max($valorC1 - $saldoRestante, 0), 2),
                'faltante_para_otro_c2' => round(max($valorC2 - $saldoRestante, 0), 2),
            ];
        }
    }

    // Prioriza primero la opción con más premios del Rango 2.
    // Si tienen la misma cantidad de Rango 2, prioriza la que tenga más Rango 1.
    usort($opciones, function ($a, $b) {
        if ($a['cantidad_c2'] !== $b['cantidad_c2']) {
            return $b['cantidad_c2'] <=> $a['cantidad_c2'];
        }

        return $b['cantidad_c1'] <=> $a['cantidad_c1'];
    });

    return $opciones;
}

public function codigoNoInscritoPorTienda($storeId)
{
    $store = \App\Models\Store::find($storeId);

    if (!$store) {
        return response()->json([
            'ok' => false,
            'message' => 'Tienda no encontrada.'
        ], 404);
    }

    $bodega = DB::connection('admin_ml')
        ->table('bodega')
        ->whereRaw('UPPER(TRIM(Nombodega)) = ?', [strtoupper(trim($store->name))])
        ->select('Codbodega', 'Nombodega', 'CodigoClieNoInscr')
        ->first();

    if (!$bodega || trim((string) $bodega->CodigoClieNoInscr) === '') {
        return response()->json([
            'ok' => false,
            'message' => 'Esta tienda no tiene código de cliente no inscrito configurado.'
        ], 404);
    }

    return response()->json([
        'ok' => true,
        'codigo' => trim($bodega->CodigoClieNoInscr),
        'bodega' => $bodega->Nombodega,
    ]);
}
}