<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Catalogo;
use App\Models\PaginaCatalogo;

class AdminCatalogo extends Controller
{
    /**
     * Formulario para crear nuevo catálogo
     */
    public function create()
    {
        $catalog = Catalogo::latest()->first(); // puede ser null
        return view('admin.catalogo.create', compact('catalog'));
    }

    /**
     * Guardar un nuevo catálogo
     */
    public function store(Request $r)
    {
        $data = $r->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public'   => 'nullable',
        ]);

        $catalog = Catalogo::create([
            'title'       => $data['title'],
            'slug'        => Str::slug($data['title']).'-'.Str::random(5),
            'description' => $data['description'] ?? null,
            'is_public'   => $r->boolean('is_public'),
        ]);

        return redirect()
            ->route('admin.catalogs.create')
            ->with('ok', "Catálogo creado: {$catalog->title}");
    }

    /**
     * Mostrar formulario para agregar páginas a un catalogo existente
     */
    public function addPages(Catalogo $catalog)
    {
        $pages = $catalog->paginas()->orderBy('page_number')->get();
        $next  = (int) ($catalog->paginas()->max('page_number') ?? 0) + 1;

        return view('admin.catalogs.add-pages', compact('catalog', 'pages', 'next'));
    }

    /**
     * Guardar las paginas subidas a un catalogo
     */
    public function storePages(Request $r, Catalogo $catalog)
    {
        $r->validate([
            'pages.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        DB::transaction(function () use ($r, $catalog) {
            // numero base: último page_number + 1 (fiable)
            $next = (int) ($catalog->paginas()->max('page_number') ?? 0) + 1;

            foreach ($r->file('pages', []) as $file) {
                // 1) intentar leer numero desde el nombre del archivo
                $fromName = $this->extractNumber($file->getClientOriginalName());

                // 2) si trae numero, usalo; si no, usa $next
                $n = $fromName ?? $next;

                // 3) evitar colisiones
                while ($catalog->paginas()->where('page_number', $n)->exists()) {
                    $n++;
                }

$binary = file_get_contents($file->getRealPath());


                PaginaCatalogo::create([
                    'catalog_id'  => $catalog->id,
                    'page_number' => $n,
                    'archivo_binario'  => $binary,   // 'catalogs/{id}/page-XYZ.jpg'
                     'mime'            => $file->getMimeType(),
                    'thumb_path'  => null,
                    'meta'        => null,
                ]);

                // 5) próximo candidato base
                $next = $n + 1;
            }
        });

        return back()->with('ok', 'Páginas subidas correctamente');
    }

    /**
     * Extrae un número del nombre del archivo (p. ej. 'page-012.jpg' -> 12, '003.png' -> 3).
     */
    private function extractNumber(string $filename): ?int
    {
        if (preg_match('/(\d{1,4})/', $filename, $m)) {
            $num = (int) ltrim($m[1], '0'); // quita ceros a la izquierda
            return $num > 0 ? $num : 0;
        }
        return null;
    }
}
