<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use App\Models\InvExistencias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStoreController extends Controller
{

public function index(Request $request)
{
    $bodegaSeleccionada = $request->input('bodega');
    $buscar = trim($request->input('q', ''));
    $soloStock = $request->boolean('solo_stock');
    $codigoBuscado = null;
$colorBuscado = null;

if (str_contains($buscar, '-')) {
    [$codigoBuscado, $colorBuscado] = array_map('trim', explode('-', $buscar, 2));
}

    // Solo carga la lista de bodegas
    $bodegas = DB::connection('admin_ml')
        ->table('bodega')
        ->select('Codbodega', 'Nombodega')
        ->orderBy('Codbodega')
        ->get();

    $productos = null;
    $bodegaActual = null;

    // Solo carga productos si el usuario seleccionó una bodega
    if (!empty($bodegaSeleccionada)) {

        $bodegaActual = DB::connection('admin_ml')
            ->table('bodega')
            ->select('Codbodega', 'Nombodega')
            ->where('Codbodega', $bodegaSeleccionada)
            ->first();

        $productos = DB::connection('admin_ml')
            ->table('inv_existencias as a')
            ->leftJoin('inventariom as b', function ($join) {
                $join->on('a.Codigo', '=', 'b.Codigo')
                     ->on('a.Color', '=', 'b.Color');
            })
            ->select(
                'a.Bodega',
                'a.Ubicacion',
                'a.Codigo',
                'a.Color',
                'b.Descripcion',
                'a.Saldo as stock_total'
            )
            ->where('a.Bodega', $bodegaSeleccionada)

            // Buscar por código, color o descripción
       // Buscar por código, color o descripción
// También permite buscar formato: 1011-1 = Codigo 1011 y Color 1
->when($buscar !== '', function ($query) use ($buscar, $codigoBuscado, $colorBuscado) {
    $query->where(function ($q) use ($buscar, $codigoBuscado, $colorBuscado) {

        if ($codigoBuscado !== null && $colorBuscado !== null) {
            $q->whereRaw('TRIM(a.Codigo) = ?', [$codigoBuscado])
              ->whereRaw('TRIM(a.Color) = ?', [$colorBuscado]);
        } else {
            $q->where('a.Codigo', 'like', "%{$buscar}%")
              ->orWhere('a.Color', 'like', "%{$buscar}%")
              ->orWhere('b.Descripcion', 'like', "%{$buscar}%");
        }

    });
})

            // Mostrar solo productos con stock
            ->when($soloStock, function ($query) {
                $query->where('a.Saldo', '>', 0);
            })

            ->orderBy('a.Ubicacion')
            ->orderBy('a.Codigo')
            ->paginate(100)
            ->withQueryString();
    }

    return view('admin.stores.index', compact(
        'bodegas',
        'productos',
        'bodegaActual',
        'bodegaSeleccionada',
        'buscar',
        'soloStock'
    ));
}

    public function create()
    {
        return view('admin.stores.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'Nombodega' => 'required|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
        ]);

        Bodega::create($data);

        return redirect()->route('admin.stores.index')
            ->with('ok', 'Bodega creada correctamente');
    }

   public function edit($store)
{
    $store = Bodega::findOrFail($store);

    return view('admin.stores.edit', compact('store'));
}

public function update(Request $r, $store)
{
    $store = Bodega::findOrFail($store);

    $r->validate([
        'Nombodega' => 'required|string|max:255',
        'ubicacion' => 'nullable|string|max:255',
    ]);

    $store->update([
        'Nombodega' => $r->Nombodega,
        'ubicacion' => $r->ubicacion,
    ]);

    return redirect()
        ->route('admin.stores.index')
        ->with('ok', 'Bodega actualizada correctamente.');
}

    public function destroy($id)
    {
        $store = Bodega::findOrFail($id);
        $store->delete();

        return back()->with('ok', 'Bodega eliminada');
    }
}