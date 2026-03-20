<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CatalogoPublicController;
use App\Http\Controllers\PedidoPublicController;
use App\Http\Controllers\Admin\AdminCatalogo;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PedidoController;

/*
|--------------------------------------------------------------------------
| PÚBLICO
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect()->route('catalogs.index'));

Route::get('/catalogos', [CatalogoController::class, 'index'])
    ->name('catalogs.index');

Route::get('/c/{slug}', [CatalogoController::class, 'showPublic'])
    ->name('catalog.public');

Route::post('/pedido/finalizar', [PedidoPublicController::class, 'store'])
    ->name('pedido.finalizar');

Route::get('/catalog-pages/{page}/image', [CatalogoController::class, 'pageImage'])
    ->name('catalog_pages.image');

Route::get('/catalogo/producto-imagen/{code}/{color?}', [CatalogoController::class, 'productoImagen'])
    ->name('catalog.product.image');

Route::get('/product-image/{product}', [CatalogoController::class, 'productImage'])
    ->name('admin.products.image');

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/catalogos/create', [AdminCatalogo::class, 'create'])
        ->name('catalogs.create');

    Route::post('/catalogos', [AdminCatalogo::class, 'store'])
        ->name('catalogs.store');

    Route::get('/catalogos/{catalog}', [AdminCatalogo::class, 'edit'])
        ->name('catalogs.edit');

    Route::get('/catalogos/{catalog}/pages', [AdminCatalogo::class, 'addPages'])
        ->name('catalogs.pages.add');

    Route::post('/catalogos/{catalog}/pages', [AdminCatalogo::class, 'storePages'])
        ->name('catalogs.pages.store');

    Route::post('/catalogos/{catalog}/products', [AdminCatalogo::class, 'addProduct'])
        ->name('catalogs.products.add');

    Route::patch('/catalogos/{catalog}/products/{product}', [AdminCatalogo::class, 'updateProductQty'])
        ->name('catalogs.products.qty');

    Route::delete('/catalogos/{catalog}/products/remove-by-code', [AdminCatalogo::class, 'removeProduct'])
        ->name('catalogs.products.remove');

    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index');

    Route::post('/products', [ProductController::class, 'store'])
        ->name('products.store');

    Route::patch('/products/{product}', [ProductController::class, 'update'])
        ->name('products.update');

    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->name('products.destroy');

    Route::post('/products/import-admin-ml', [ProductController::class, 'importFromAdminMl'])
        ->name('products.import_admin_ml');

    Route::post('/products/import-admin-ml-images', [ProductController::class, 'importImagesFromAdminMl'])
        ->name('products.import_admin_ml_images');

    Route::get('/pedidos', [PedidoController::class, 'index'])
        ->name('pedidos.index');

    Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])
        ->name('pedidos.show');

    Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'updateEstado'])
        ->name('pedidos.estado');

     Route::delete('/catalogos/{catalogo}/paginas/{pagina}', [AdminCatalogo::class, 'destroyPage'])
    ->name('catalogs.paginas.destroy');

    //ADMIN
});
