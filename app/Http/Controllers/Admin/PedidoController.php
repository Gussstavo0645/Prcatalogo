<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Pedido::with('store')
            ->withCount('items')
            ->orderBy('created_at', 'desc');

        if ($user->role !== 'admin_general') {
            $query->where('store_id', $user->store_id);
        }

        $pedidos = $query->paginate(10);

        return view('admin.pedidos.index', compact('pedidos'));
    }

    // MOSTRAR UN PEDIDO ESPECIFICO
    public function show(Request $request, Pedido $pedido)
    {
        $user = $request->user();

        if ($user->role !== 'admin_general' && $pedido->store_id != $user->store_id) {
            abort(403, 'No tienes permiso para ver este pedido.');
        }

        $pedido->load(['items', 'store']);

        return view('admin.pedidos.show', compact('pedido'));
    }

    // CAMBIAR ESTADO
    public function updateEstado(Request $request, Pedido $pedido)
    {
        $user = $request->user();

        if ($user->role !== 'admin_general' && $pedido->store_id != $user->store_id) {
            abort(403, 'No tienes permiso para cambiar este pedido.');
        }

        $request->validate([
            'status' => 'required|in:pendiente,confirmado,enviado,entregado,cancelado'
        ]);

        $pedido->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Estado actualizado');
    }
}