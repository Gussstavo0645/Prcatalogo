<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('catalog_products', function (Blueprint $table) {

            // ✅ 1) Crear índices individuales para que la FK no dependa del UNIQUE compuesto
            $table->index('catalog_id', 'cp_catalog_id_idx');
            $table->index('product_id', 'cp_product_id_idx');

            // ✅ 2) Ahora sí se puede dropear el UNIQUE viejo
            $table->dropUnique('catalog_products_catalog_id_product_id_unique');

            // ✅ 3) Agregar page_number
            $table->unsignedInteger('page_number')->default(1)->after('product_id');

            // ✅ 4) Crear nuevo UNIQUE correcto (producto puede repetirse en distintas páginas)
            $table->unique(['catalog_id','product_id','page_number'], 'cat_prod_page_unique');

            // ✅ útil para consultas por página
            $table->index(['catalog_id','page_number'], 'cp_catalog_page_idx');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->dropUnique('cat_prod_page_unique');
            $table->dropIndex('cp_catalog_page_idx');
            $table->dropColumn('page_number');

            // devolver UNIQUE original
            $table->unique(['catalog_id','product_id'], 'catalog_products_catalog_id_product_id_unique');

            // opcional: quitar índices extra
            $table->dropIndex('cp_catalog_id_idx');
            $table->dropIndex('cp_product_id_idx');
        });
    }
};