<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalogo;
use App\Models\Pedido;
use App\Models\Store;
use App\Models\PedidoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        $pedidosQuery = Pedido::query();

        if ($fechaInicio) {
            $pedidosQuery->whereDate('created_at', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $pedidosQuery->whereDate('created_at', '<=', $fechaFin);
        }

        $totalCatalogos = Catalogo::count();
        $catalogosPublicos = Catalogo::where('is_public', true)->count();
        $catalogosOcultos = Catalogo::where('is_public', false)->count();

        $tiendasActivas = Store::where('is_active', 1)->count();

        $pedidosPendientes = (clone $pedidosQuery)
            ->where('status', 'pendiente')
            ->count();

        $pedidosCancelados = (clone $pedidosQuery)
            ->where('status', 'cancelado')
            ->count();

        $pedidosHoy = Pedido::whereDate('created_at', Carbon::today())->count();

        $totalVendido = (clone $pedidosQuery)
            ->where('status', '!=', 'cancelado')
            ->sum('total');

        $ultimosPedidos = (clone $pedidosQuery)
            ->with('store')
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // Ventas por tienda
        $ventasPorTienda = Pedido::query()
            ->select(
                'stores.name as tienda',
                DB::raw('COUNT(pedidos.id) as total_pedidos'),
                DB::raw('SUM(pedidos.total) as total_vendido')
            )
            ->leftJoin('stores', 'stores.id', '=', 'pedidos.store_id')
            ->where('pedidos.status', '!=', 'cancelado')
            ->when($fechaInicio, function ($query) use ($fechaInicio) {
                $query->whereDate('pedidos.created_at', '>=', $fechaInicio);
            })
            ->when($fechaFin, function ($query) use ($fechaFin) {
                $query->whereDate('pedidos.created_at', '<=', $fechaFin);
            })
            ->groupBy('stores.name')
            ->orderByDesc('total_vendido')
            ->get();

        // Pedidos por estado
        $pedidosPorEstado = (clone $pedidosQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        // Productos más pedidos
       $productosMasPedidos = PedidoItem::query()
    ->select(
        'pedidos_items.product_name as producto',
        'pedidos_items.product_code as codigo',
        'pedidos_items.product_color as color',
        DB::raw('SUM(pedidos_items.quantity) as cantidad_total'),
        DB::raw('SUM(pedidos_items.subtotal) as total_vendido')
    )
    ->join('pedidos', 'pedidos.id', '=', 'pedidos_items.pedidos_id')
    ->where('pedidos.status', '!=', 'cancelado')
    ->when($fechaInicio, function ($query) use ($fechaInicio) {
        $query->whereDate('pedidos.created_at', '>=', $fechaInicio);
    })
    ->when($fechaFin, function ($query) use ($fechaFin) {
        $query->whereDate('pedidos.created_at', '<=', $fechaFin);
    })
    ->groupBy(
        'pedidos_items.product_name',
        'pedidos_items.product_code',
        'pedidos_items.product_color'
    )
    ->orderByDesc('cantidad_total')
    ->take(10)
    ->get();

        // Gráfica de ventas por día
        $ventasPorDia = Pedido::query()
            ->select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('SUM(total) as total')
            )
            ->where('status', '!=', 'cancelado')
            ->when($fechaInicio, function ($query) use ($fechaInicio) {
                $query->whereDate('created_at', '>=', $fechaInicio);
            })
            ->when($fechaFin, function ($query) use ($fechaFin) {
                $query->whereDate('created_at', '<=', $fechaFin);
            })
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get();

        return view('admin.dashboard', compact(
            'fechaInicio',
            'fechaFin',
            'totalCatalogos',
            'catalogosPublicos',
            'catalogosOcultos',
            'tiendasActivas',
            'pedidosPendientes',
            'pedidosCancelados',
            'pedidosHoy',
            'totalVendido',
            'ultimosPedidos',
            'ventasPorTienda',
            'pedidosPorEstado',
            'productosMasPedidos',
            'ventasPorDia'
        ));
    }
}