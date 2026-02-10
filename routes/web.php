<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\Admin\AdminCatalogo;

Route::get('/', fn() => redirect()->route('catalogs.index'));

Route::get('/catalogos', [CatalogoController::class, 'index'])->name('catalogs.index');
Route::get('/catalogos/{slug}', [CatalogoController::class, 'show'])->name('catalogs.show');

Route::get('/catalog-pages/{pagina}/image', [CatalogoController::class, 'pageImage'])
    ->name('catalog_pages.image');


Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/catalogos/create', [AdminCatalogo::class, 'create'])->name('catalogs.create');
    Route::post('/catalogos', [AdminCatalogo::class, 'store'])->name('catalogs.store');

    //nueva ruta para ver formularios y crear
    Route::get('/catalogos/{catalog}/pages', [AdminCatalogo::class, 'addPages'])->name('catalogs.pages.add');
    Route::post('/catalogos/{catalog}/pages', [AdminCatalogo::class, 'storePages'])->name('catalogs.pages.store');
// va sirviendo la imagen desde un blob
    Route::get('/catalog-pages/{pagina}/image', [AdminCatalogo::class, 'image'])
    ->name('catalog_pages.image');
});
