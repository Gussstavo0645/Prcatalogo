<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PremioContadoController extends Controller
{
    private string $connection = 'admin_ml';
    private string $table = 'masterpremios';

    public function index(Request $request)
    {
        $mes = $request->get('mes', now()->format('m/Y'));

        $admin = DB::connection($this->connection);
        try {
            $premios = $admin
                ->table($this->table)
                ->where('MESOPE', $mes)
                ->orderBy('VALORMIN', 'asc')
                ->get();
      } finally {
    DB::disconnect($this->connection);
}

        return view('admin.premios.index', compact('premios', 'mes'));
    }

    public function create()
    {
        return view('admin.premios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'CODTPRODUCTO' => 'required|max:20',
            'DESCRIP_PREMIO' => 'required|max:150',
            'MESOPE' => 'required|max:7',
            'CODOFERTA' => 'required|max:50',
            'COLORFERTA' => 'required|numeric|min:1',
            'VALORMIN' => 'required|numeric|min:0',
            'VALORMAX' => 'required|numeric|min:0',
        ]);

        $admin = DB::connection($this->connection);
        try {
            $admin
                ->table($this->table)
                ->insert([
                    'CODTPRODUCTO' => $request->CODTPRODUCTO,
                    'DESCRIP_PREMIO' => $request->DESCRIP_PREMIO,
                    'MESOPE' => $request->MESOPE,
                    'CODOFERTA' => $request->CODOFERTA,
                    'COLORFERTA' => $request->COLORFERTA,
                    'VALORMIN' => $request->VALORMIN,
                    'VALORMAX' => $request->VALORMAX,
            ]);

        return redirect()
            ->route('admin.premios.index', ['mes' => $request->MESOPE])
            ->with('success', 'Premio creado correctamente.');
        } finally {
            DB::disconnect($this->connection);
        }
    }


   public function destroy(Request $request, $codigo)
{
    $mes = $request->query('mes');

    if (!$mes) {
        abort(404);
    }
    $admin = DB::connection($this->connection);

    try {

    $premio = $admin 
        ->table($this->table)
        ->whereRaw('TRIM(CODOFERTA) = ?', [trim($codigo)])
        ->where('MESOPE', $mes)
        ->first();

    if (!$premio) {
        abort(404);
    }

    $admin->table($this->table)
        ->whereRaw('TRIM(CODOFERTA) = ?', [trim($codigo)])
        ->where('MESOPE', $mes)
        ->delete();

    return redirect()
        ->route('admin.premios.index', ['mes' => $mes])
        ->with('success', 'Premio eliminado correctamente.');
   } finally {
    DB::disconnect($this->connection);
}
}

public function edit(Request $request, $codigo)
{
    $mes = $request->query('mes');

    if (!$mes) {
        abort(404);
    }

    $admin = DB::connection($this->connection);

    try {
        $premio = $admin
            ->table($this->table)
            ->whereRaw('TRIM(MESOPE) = ?', [trim($mes)])
            ->where(function ($query) use ($codigo) {
                $query->whereRaw('TRIM(CODOFERTA) = ?', [trim($codigo)])
                      ->orWhereRaw('TRIM(CODTPRODUCTO) = ?', [trim($codigo)]);
            })
            ->first();

        if (!$premio) {
            abort(404);
        }

    } finally {
        DB::disconnect($this->connection);
    }

    $fotoLocal = DB::table('premios_publicos')
        ->where('mesope', $premio->MESOPE)
        ->where('rango_premio', trim($premio->CODTPRODUCTO))
        ->first();

    $premio->foto_publica = $fotoLocal->foto_publica ?? null;

    return view('admin.premios.edit', compact('premio'));
}

public function update(Request $request, $codigo)
{
    $mes = $request->query('mes');

    if (!$mes) {
        abort(404);
    }

    $request->validate([
        'Descripcion' => 'required|max:150',
        'Codprod' => 'required|max:50',
        'Desde' => 'required|numeric|min:0',
        'Hasta' => 'required|numeric|min:0',
        'foto_publica' => 'nullable|image|max:4096',
    ]);

    $premio = null;

    $admin = DB::connection($this->connection);

    try {
        $premio = $admin
            ->table($this->table)
            ->whereRaw('TRIM(MESOPE) = ?', [trim($mes)])
            ->where(function ($query) use ($codigo) {
                $query->whereRaw('TRIM(CODOFERTA) = ?', [trim($codigo)])
                      ->orWhereRaw('TRIM(CODTPRODUCTO) = ?', [trim($codigo)]);
            })
            ->first();

        if (!$premio) {
            abort(404);
        }

        $admin->table($this->table)
            ->whereRaw('TRIM(MESOPE) = ?', [trim($mes)])
            ->whereRaw('TRIM(CODTPRODUCTO) = ?', [trim($premio->CODTPRODUCTO)])
            ->update([
                'DESCRIP_PREMIO' => $request->Descripcion,
                'CODOFERTA' => $request->Codprod,
                'VALORMIN' => $request->Desde,
                'VALORMAX' => $request->Hasta,
            ]);

    } finally {
        DB::disconnect($this->connection);
    }

    // Desde aquí ya NO está abierta admin_ml.
    $fotoLocal = DB::table('premios_publicos')
        ->where('mesope', $premio->MESOPE)
        ->where('rango_premio', trim($premio->CODTPRODUCTO))
        ->first();

    $dataFoto = [
        'mesope' => $premio->MESOPE,
        'rango_premio' => trim($premio->CODTPRODUCTO),
        'codigo_premio' => trim($request->Codprod),
        'activo' => 1,
        'updated_at' => now(),
    ];

    if ($request->hasFile('foto_publica')) {
        if ($fotoLocal && !empty($fotoLocal->foto_publica)) {
            Storage::disk('public')->delete($fotoLocal->foto_publica);
        }

        $dataFoto['foto_publica'] = $request->file('foto_publica')->store('premios', 'public');
    }

    DB::table('premios_publicos')->updateOrInsert(
        [
            'mesope' => $premio->MESOPE,
            'rango_premio' => trim($premio->CODTPRODUCTO),
        ],
        $dataFoto
    );

    return redirect()
        ->route('admin.premios.index', ['mes' => $mes])
        ->with('success', 'Premio actualizado correctamente.');
}
}
