<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('catalog_id')
                ->constrained('catalogs')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('position')->nullable();

            $table->timestamps();

            $table->unique(['catalog_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_products');
    }
};
