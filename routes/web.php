<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CatalogoPublicController;
use App\Http\Controllers\PedidoPublicController;
use App\Http\Controllers\Admin\AdminCatalogo;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\CatalogComboController;
/*
|--------------------------------------------------------------------------
| PÚBLICO
|--------------------------------------------------------------------------
*/

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

    Route::get('/catalogo/producto-imagen-large/{code}/{color?}', [CatalogoController::class, 'productoImagenLarge'])
    ->name('catalog.product.image.large');

Route::get('/product-image/{product}', [CatalogoController::class, 'productImage'])
    ->name('admin.products.image');

Route::get('/catalogo/producto-thumb/{code}/{color?}', [CatalogoController::class, 'productoThumb'])
    ->name('catalog.product.thumb');

 Route::get('/c/{slug}/bloque', [CatalogoController::class, 'pagesBlock'])
    ->name('catalog.public.block');

Route::view('/quienes-somos', 'catalogo.quisomos')->name('catalogo.quisomos');

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

//Route::get('/', fn() => redirect()->route('catalogs.index'));

Route::get('/catalogos', [AdminCatalogo::class, 'index'])
    ->name('catalogs.index');

    Route::get('/catalogos/create', [AdminCatalogo::class, 'create'])
        ->name('catalogs.create');

        Route::get('/catalogos/{slug}', [AdminCatalogo::class, 'show'])
    ->name('catalog.show');

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

    Route::post('/catalogos/{catalog}/bulk-add-products', [AdminCatalogo::class, 'bulkAddProducts'])
    ->name('admin.catalogs.bulkAddProducts');

Route::patch('/catalogos/{id}/toggle-public', [AdminCatalogo::class, 'togglePublic'])
    ->name('catalogos.togglePublic');

    Route::get('/catalogos/productos/search', [AdminCatalogo::class, 'searchProducts'])
    ->name('catalogs.products.search');

    Route::get('/catalogos/{catalog}/combos/create', [CatalogComboController::class, 'create'])
    ->name('catalogos.combos.create');

Route::post('/catalogos/{catalog}/combos', [CatalogComboController::class, 'store'])
    ->name('catalogos.combos.store');

    Route::delete('/catalogos/combos/{id}', [App\Http\Controllers\Admin\CatalogComboController::class, 'destroy'])
    ->name('catalogos.combos.destroy');
    //ADMIN 
    //publico
    // prueba git
});
