<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
          Schema::create('catalog_store', function (Blueprint $table) {
        $table->id();

        $table->foreignId('catalog_id')
            ->constrained('catalogs')
            ->cascadeOnDelete();

        $table->foreignId('store_id')
            ->constrained('stores')
            ->cascadeOnDelete();

        $table->unique(['catalog_id', 'store_id']);

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_store');
    }
};
