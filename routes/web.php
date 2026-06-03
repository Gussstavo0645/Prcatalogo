<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\PedidoPublicController;
use App\Http\Controllers\Admin\AdminCatalogo;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\CatalogComboController;
use App\Http\Controllers\AdminStoreController;
use App\Http\Controllers\ClientePublicController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\PagoNeoPayController;

/*
|--------------------------------------------------------------------------
| PÚBLICO
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('catalogs.index');
});

Route::get('/catalogos', [CatalogoController::class, 'index'])
    ->name('catalogs.index');

Route::get('/c/{slug}', [CatalogoController::class, 'showPublic'])
    ->name('catalog.public');

Route::get('/c/{slug}/bloque', [CatalogoController::class, 'pagesBlock'])
    ->name('catalog.public.block');

Route::get('/clientes/detectar/{codcliente}', [ClientePublicController::class, 'detectar'])
    ->name('clientes.detectar');

Route::post('/pedido/finalizar', [PedidoPublicController::class, 'store'])
    ->name('pedido.finalizar');

Route::get('/catalog-pages/{page}/image', [CatalogoController::class, 'pageImage'])
    ->name('catalog_pages.image');

Route::get('/catalogo/producto-imagen/{code}/{color?}', [CatalogoController::class, 'productoImagen'])
    ->name('catalog.product.image');

Route::get('/catalogo/producto-imagen-large/{code}/{color?}', [CatalogoController::class, 'productoImagenLarge'])
    ->name('catalog.product.image.large');

Route::get('/catalogo/producto-thumb/{code}/{color?}', [CatalogoController::class, 'productoThumb'])
    ->name('catalog.product.thumb');

Route::get('/product-image/{product}', [CatalogoController::class, 'productImage'])
    ->name('admin.products.image');

Route::get('/clientes/acumulado/{codcliente}', [ClientePublicController::class, 'acumulado'])
    ->name('clientes.acumulado');

Route::get('/clientes/no-inscrito/tienda/{storeId}', [ClientePublicController::class, 'codigoNoInscritoPorTienda']);

Route::view('/quienes-somos', 'catalogo.quisomos')
    ->name('catalogo.quisomos');

Route::post('/pedidos/{pedido}/neopay/iniciar', [PagoNeoPayController::class, 'iniciar'])
    ->name('neopay.iniciar');

Route::get('/pagos/neopay/retorno', [PagoNeoPayController::class, 'retorno'])
    ->name('neopay.retorno');

Route::post('/pagos/neopay/webhook', [PagoNeoPayController::class, 'webhook'])
    ->name('neopay.webhook');


/*
|--------------------------------------------------------------------------
| DASHBOARD BREEZE
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return redirect()->route('admin.catalogs.index');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| PERFIL DE USUARIO BREEZE
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    })->name('index');

    Route::get('/catalogos', [AdminCatalogo::class, 'index'])
        ->name('catalogs.index');

    Route::get('/catalogos/create', [AdminCatalogo::class, 'create'])
        ->name('catalogs.create');


    Route::post('/catalogos/{catalog}/tiendas/sync', [AdminCatalogo::class, 'syncTiendas'])
        ->name('catalogos.tiendas.sync');

    Route::post('/catalogos', [AdminCatalogo::class, 'store'])
        ->name('catalogs.store');

    Route::get('/catalogos/productos/search', [AdminCatalogo::class, 'searchProducts'])
        ->name('catalogs.products.search');

    Route::get('/catalogos/{catalog}/edit', [AdminCatalogo::class, 'edit'])
        ->name('catalogs.edit');

    Route::get('/catalogos/{slug}', [AdminCatalogo::class, 'show'])
        ->name('catalog.show');

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

    Route::delete('/catalogos/{catalogo}/paginas/{pagina}', [AdminCatalogo::class, 'destroyPage'])
        ->name('catalogs.paginas.destroy');

    Route::post('/catalogos/{catalog}/bulk-add-products', [AdminCatalogo::class, 'bulkAddProducts'])
        ->name('catalogs.bulkAddProducts');

    Route::patch('/catalogos/{id}/toggle-public', [AdminCatalogo::class, 'togglePublic'])
        ->name('catalogos.togglePublic');

    Route::get('/catalogos/{catalog}/combos/create', [CatalogComboController::class, 'create'])
        ->name('catalogos.combos.create');

    Route::post('/catalogos/{catalog}/combos', [CatalogComboController::class, 'store'])
        ->name('catalogos.combos.store');

    Route::delete('/catalogos/combos/{id}', [CatalogComboController::class, 'destroy'])
        ->name('catalogos.combos.destroy');

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

    Route::resource('stores', AdminStoreController::class);

    Route::get('/pedidos', [PedidoController::class, 'index'])
        ->name('pedidos.index');

    Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])
        ->name('pedidos.show');

    Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'updateEstado'])
        ->name('pedidos.estado');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::post('/pedidos/{pedido}/enviar-admin-ml', [PedidoController::class, 'enviarAdminMl'])
        ->name('pedidos.enviarAdminMl');

        Route::patch('/pedidos/{pedido}/validar-pago', [PedidoController::class, 'validarPago'])
    ->name('pedidos.validarPago');
});


/*
|--------------------------------------------------------------------------
| AUTH BREEZE
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';
