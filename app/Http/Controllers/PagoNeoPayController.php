<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PagoNeoPayController extends Controller
{
   public function iniciar(Pedido $pedido)
{
    if ($pedido->total <= 0) {
        return response()->json([
            'ok' => false,
            'message' => 'El pedido no tiene total válido.'
        ], 422);
    }

    if ($pedido->pago_estado === 'pagado') {
        return response()->json([
            'ok' => false,
            'message' => 'Este pedido ya fue pagado.'
        ], 422);
    }

    $referencia = 'PED-' . $pedido->id . '-' . now()->format('YmdHis');

    $pedido->update([
        'pago_estado' => 'pendiente_pago',
        'pago_gateway' => 'neopay',
        'pago_metodo' => 'neopay',
        'pago_referencia' => $referencia,
        'pago_monto' => $pedido->total,
        'pago_moneda' => config('services.neopay.currency', 'GTQ'),
    ]);

    return response()->json([
        'ok' => true,
        'message' => 'Pedido preparado para NeoPay.',
        'pedido_id' => $pedido->id,
        'referencia' => $referencia,
        'redirect_url' => null,
    ]);
}

    public function retorno(Request $request)
    {
        return redirect()
            ->route('catalogs.index')
            ->with('info', 'Pago recibido para validación.');
    }

    public function webhook(Request $request)
    {
        Log::info('Webhook NeoPay recibido', $request->all());

        return response()->json([
            'ok' => true,
            'message' => 'Webhook recibido'
        ]);
    }
}