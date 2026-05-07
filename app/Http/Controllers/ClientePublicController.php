<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

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

        // 1. Buscar primero en clientes inscritos
        $cliente = DB::connection('admin_ml')
            ->table('clientes')
            ->whereRaw('TRIM(Codcliente) = ?', [$codcliente])
            ->select('Codcliente')
            ->first();

        if ($cliente) {
            return response()->json([
                'ok' => true,
                'message' => 'Código de cliente válido.',
                'tipo' => 'cliente',
                'cliente' => [
                    'CodCliente' => $cliente->Codcliente,
                ]
            ]);
        }

        // 2. Si no existe en clientes, buscar en bodega como cliente no inscrito
        $clienteNoInscrito = DB::connection('admin_ml')
            ->table('bodega')
            ->whereRaw('TRIM(CodigoClieNoInscr) = ?', [$codcliente])
            ->select('CodigoClieNoInscr')
            ->first();

        if ($clienteNoInscrito) {
            return response()->json([
                'ok' => true,
                'message' => 'Código de cliente no inscrito válido.',
                'tipo' => 'cliente_no_inscrito',
                'cliente' => [
                    'CodCliente' => $clienteNoInscrito->CodigoClieNoInscr,
                ]
            ]);
        }

        // 3. Si no existe en ninguna tabla
        return response()->json([
            'ok' => false,
            'message' => 'Código de cliente no encontrado.'
        ], 404);
    }
}