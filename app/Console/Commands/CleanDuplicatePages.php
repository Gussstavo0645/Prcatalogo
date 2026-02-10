<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\PaginaCatalogo;

class CleanDuplicatePages extends Command
{
    protected $signature = 'catalog:clean-duplicates {--delete-files : También borrar del storage las imágenes duplicadas}';
    protected $description = 'Elimina páginas duplicadas por checksum dentro de cada catálogo sin violar el índice único.';

    public function handle()
    {
        $disk = Storage::disk('public');
        $deleteFiles = (bool) $this->option('delete-files');
        $deleted = 0;

        // 1) Completar checksums faltantes SIN violar el índice único
        $this->info('Calculando checksums faltantes (sin violar índice único)...');

        PaginaCatalogo::whereNull('checksum')
            ->orderBy('id')
            ->chunkById(200, function ($chunk) use ($disk, $deleteFiles, &$deleted) {
                foreach ($chunk as $page) {
                    $abs = $disk->path($page->image_path ?? '');

                    if (!is_file($abs)) {
                        $this->warn("Archivo no existe, page id={$page->id} ({$page->image_path})");
                        continue;
                    }

                    $hash = md5_file($abs);

                    // Si ya existe otra página en el MISMO catálogo con ese checksum,
                    // esta es un duplicado: elimínala en vez de actualizar (evita choque con índice).
                    $exists = PaginaCatalogo::where('catalog_id', $page->catalog_id)
                        ->where('checksum', $hash)
                        ->exists();

                    if ($exists) {
                        $this->line("🗑 Duplicado detectado al calcular checksum. Eliminando page id={$page->id} (catalog={$page->catalog_id})");

                        if ($deleteFiles && $disk->exists($page->image_path)) {
                            // Borra también el archivo si quieres
                            $disk->delete($page->image_path);
                        }

                        $page->delete();
                        $deleted++;
                        continue;
                    }

                    // Seguro actualizar: no hay otro con el mismo (catalog_id, checksum)
                    $page->checksum = $hash;
                    $page->save();
                }
            });

        // 2) Limpieza final de duplicados (por si ya existían varias con checksum igual)
        $this->info('Buscando y limpiando duplicados existentes...');
        $dups = PaginaCatalogo::select('catalog_id', 'checksum')
            ->whereNotNull('checksum')
            ->groupBy('catalog_id', 'checksum')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($dups as $dup) {
            $pages = PaginaCatalogo::where('catalog_id', $dup->catalog_id)
                ->where('checksum', $dup->checksum)
                ->orderBy('id') // conserva la primera
                ->get();

            $keep = $pages->shift();

            foreach ($pages as $p) {
                $this->line("🗑 Eliminando duplicado existente: catalog={$dup->catalog_id} id={$p->id}");

                if ($deleteFiles && $disk->exists($p->image_path)) {
                    $disk->delete($p->image_path);
                }

                $p->delete();
                $deleted++;
            }
        }

        $this->info("Limpieza completada. Páginas duplicadas eliminadas: {$deleted}");

        return Command::SUCCESS;
    }
}
