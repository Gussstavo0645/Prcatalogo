<?php

namespace App\Http\Controllers;

use App\Models\CatalogPdf;
use Illuminate\Support\Facades\Storage;

class CatalogPdfController extends Controller
{
    /**
     * Mostrar todos los catálogos PDF publicados.
     */
    public function index()
    {
        $catalogos = CatalogPdf::query()
            ->where('activo', true)
            ->orderByDesc('destacado')
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->orderBy('orden')
            ->get();

        $destacado = $catalogos->first();
        $anteriores = $catalogos->skip(1)->values();

        return view('catalogos-pdf.index', compact(
            'destacado',
            'anteriores'
        ));
    }

    /**
     * Abrir el PDF dentro del navegador.
     */
    public function ver(CatalogPdf $catalogPdf)
    {
        abort_unless($catalogPdf->activo, 404);

        $disk = Storage::disk('public');

        abort_unless($disk->exists($catalogPdf->archivo_pdf), 404);

        $nombre = $catalogPdf->nombre_archivo_original
            ?: $catalogPdf->slug . '.pdf';

        return $disk->response(
            $catalogPdf->archivo_pdf,
            $nombre,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline',
            ]
        );
    }

    /**
     * Descargar el archivo PDF.
     */
    public function descargar(CatalogPdf $catalogPdf)
    {
        abort_unless($catalogPdf->activo, 404);

        $disk = Storage::disk('public');

        abort_unless($disk->exists($catalogPdf->archivo_pdf), 404);

        $nombre = $catalogPdf->nombre_archivo_original
            ?: $catalogPdf->slug . '.pdf';

        return $disk->download(
            $catalogPdf->archivo_pdf,
            $nombre
        );
    }
}