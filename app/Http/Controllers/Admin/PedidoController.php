<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::withCount('items')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

            return view('admin.pedidos.index', compact('pedidos'));
    }
    //MOSTRAR UN PEDIDO ESPECIFICO
     
    public function show(Pedido $pedido)
    {
        $pedido ->load('items');

        return view('admin.pedidos.show',compact('pedido'));
    }

    // CAMBIAR ESTADO

    public function updateEstado(Request $request, Pedido $pedido)
    {
      $request->validate([
            'status' => 'required|in:pendiente,confirmado,enviado,entregado,cancelado'
        ]);

        $pedido->update([
            'status' => $request->status
        ]);
        
        return back()->with('success', 'Estado actualizado');

    }
}
