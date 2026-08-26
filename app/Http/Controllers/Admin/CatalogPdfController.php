<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CatalogPdfController extends Controller
{
    public function index()
    {
        $catalogos = CatalogPdf::query()
            ->orderByDesc('destacado')
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->orderBy('orden')
            ->paginate(12);

        return view('admin.catalogos-pdf.index', compact('catalogos'));
    }

    public function create()
    {
        $catalogosDigitales = DB::table('catalogs')
            ->select('id', 'title')
            ->orderByDesc('id')
            ->get();

        return view(
            'admin.catalogos-pdf.create',
            compact('catalogosDigitales')
        );
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'catalog_id' => ['nullable', 'exists:catalogs,id'],
            'titulo' => ['required', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'mes' => ['required', 'integer', 'between:1,12'],
            'anio' => [
                'required',
                'integer',
                'min:2020',
                'max:' . (now()->year + 2),
            ],
            'portada' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
            'archivo_pdf' => [
                'required',
                'file',
                'mimes:pdf',
                'max:102400',
            ],
            'numero_paginas' => [
                'nullable',
                'integer',
                'min:1',
                'max:2000',
            ],
            'orden' => ['nullable', 'integer', 'min:0'],
            'destacado' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $portadaNueva = null;
        $pdfNuevo = null;

        DB::beginTransaction();

        try {
            $portadaNueva = $request->file('portada')->store(
                'catalogos-pdf/portadas',
                'public'
            );

            $pdfNuevo = $request->file('archivo_pdf')->store(
                'catalogos-pdf/archivos',
                'public'
            );

            $slugBase = Str::slug(
                $datos['titulo'] . '-' .
                    $datos['mes'] . '-' .
                    $datos['anio']
            );

            $slug = $slugBase;
            $numero = 2;

            while (CatalogPdf::where('slug', $slug)->exists()) {
                $slug = $slugBase . '-' . $numero;
                $numero++;
            }

            $esDestacado = $request->boolean('destacado');
            $estaActivo = $request->boolean('activo');

            if ($esDestacado) {
                CatalogPdf::query()->update([
                    'destacado' => false,
                ]);
            }

            CatalogPdf::create([
                'catalog_id' => $datos['catalog_id'] ?? null,
                'titulo' => $datos['titulo'],
                'slug' => $slug,
                'descripcion' => $datos['descripcion'] ?? null,
                'mes' => $datos['mes'],
                'anio' => $datos['anio'],
                'portada' => $portadaNueva,
                'archivo_pdf' => $pdfNuevo,
                'nombre_archivo_original' => $request
                    ->file('archivo_pdf')
                    ->getClientOriginalName(),
                'numero_paginas' => $datos['numero_paginas'] ?? null,
                'destacado' => $esDestacado,
                'activo' => $estaActivo,
                'orden' => $datos['orden'] ?? 0,
                'publicado_at' => $estaActivo ? now() : null,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.catalogos-pdf.index')
                ->with(
                    'success',
                    'El catálogo PDF se publicó correctamente.'
                );
        } catch (Throwable $error) {
            DB::rollBack();

            if ($portadaNueva) {
                Storage::disk('public')->delete($portadaNueva);
            }

            if ($pdfNuevo) {
                Storage::disk('public')->delete($pdfNuevo);
            }

            throw $error;
        }
    }

    public function show(CatalogPdf $catalogPdf)
    {
        return redirect()->route(
            'admin.catalogos-pdf.edit',
            $catalogPdf
        );
    }

    public function edit(CatalogPdf $catalogPdf)
    {
        $catalogosDigitales = DB::table('catalogs')
            ->select('id', 'title')
            ->orderByDesc('id')
            ->get();

        return view(
            'admin.catalogos-pdf.edit',
            compact('catalogPdf', 'catalogosDigitales')
        );
    }

    public function update(
        Request $request,
        CatalogPdf $catalogPdf
    ) {
        $datos = $request->validate([
            'catalog_id' => ['nullable', 'exists:catalogs,id'],
            'titulo' => ['required', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'mes' => ['required', 'integer', 'between:1,12'],
            'anio' => [
                'required',
                'integer',
                'min:2020',
                'max:' . (now()->year + 2),
            ],
            'portada' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
            'archivo_pdf' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:102400',
            ],
            'numero_paginas' => [
                'nullable',
                'integer',
                'min:1',
                'max:2000',
            ],
            'orden' => ['nullable', 'integer', 'min:0'],
            'destacado' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $portadaAnterior = $catalogPdf->portada;
        $pdfAnterior = $catalogPdf->archivo_pdf;

        $portadaNueva = null;
        $pdfNuevo = null;

        DB::beginTransaction();

        try {
            if ($request->hasFile('portada')) {
                $portadaNueva = $request->file('portada')->store(
                    'catalogos-pdf/portadas',
                    'public'
                );
            }

            if ($request->hasFile('archivo_pdf')) {
                $pdfNuevo = $request->file('archivo_pdf')->store(
                    'catalogos-pdf/archivos',
                    'public'
                );
            }

            $esDestacado = $request->boolean('destacado');
            $estaActivo = $request->boolean('activo');

            if ($esDestacado) {
                CatalogPdf::where('id', '!=', $catalogPdf->id)
                    ->update(['destacado' => false]);
            }

            $catalogPdf->update([
                'catalog_id' => $datos['catalog_id'] ?? null,
                'titulo' => $datos['titulo'],
                'descripcion' => $datos['descripcion'] ?? null,
                'mes' => $datos['mes'],
                'anio' => $datos['anio'],
                'portada' => $portadaNueva
                    ?: $portadaAnterior,
                'archivo_pdf' => $pdfNuevo
                    ?: $pdfAnterior,
                'nombre_archivo_original' => $pdfNuevo
                    ? $request
                    ->file('archivo_pdf')
                    ->getClientOriginalName()
                    : $catalogPdf->nombre_archivo_original,
                'numero_paginas' => $datos['numero_paginas'] ?? null,
                'destacado' => $esDestacado,
                'activo' => $estaActivo,
                'orden' => $datos['orden'] ?? 0,
                'publicado_at' => $estaActivo
                    ? ($catalogPdf->publicado_at ?? now())
                    : null,
            ]);

            DB::commit();

            if ($portadaNueva && $portadaAnterior) {
                Storage::disk('public')->delete($portadaAnterior);
            }

            if ($pdfNuevo && $pdfAnterior) {
                Storage::disk('public')->delete($pdfAnterior);
            }

            return redirect()
                ->route('admin.catalogos-pdf.index')
                ->with(
                    'success',
                    'El catálogo PDF se actualizó correctamente.'
                );
        } catch (Throwable $error) {
            DB::rollBack();

            if ($portadaNueva) {
                Storage::disk('public')->delete($portadaNueva);
            }

            if ($pdfNuevo) {
                Storage::disk('public')->delete($pdfNuevo);
            }

            throw $error;
        }
    }

    public function destroy(CatalogPdf $catalogPdf)
    {
        $portada = $catalogPdf->portada;
        $archivoPdf = $catalogPdf->archivo_pdf;

        $catalogPdf->delete();

        Storage::disk('public')->delete([
            $portada,
            $archivoPdf,
        ]);

        return redirect()
            ->route('admin.catalogos-pdf.index')
            ->with(
                'success',
                'El catálogo PDF se eliminó correctamente.'
            );
    }
}
