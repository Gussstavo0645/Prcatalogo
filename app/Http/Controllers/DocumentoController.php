<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Documento;

class DocumentoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|max:16384'
        ]);

        $file = $request->file('archivo');

        Documento::create([
            'nombre' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'archivo_binario' => file_get_contents($file->getRealPath()),
        ]);

        return back()->with('success', 'Documento guardado');
    }

    public function show($id)
    {
        $documento = Documento::findOrFail($id);

        return response($documento->archivo_binario)
            ->header('Content-Type', $documento->mime);
    }
}
