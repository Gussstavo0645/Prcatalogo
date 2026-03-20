<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); //coloca el codigo del producto
            $table->text('description')->nullable();  //colocando la descripcion del producto
           $table->decimal('price',10,2);
           $table->string('mime');
            $table->timestamps();
        });
        DB::statement('ALTER TABLE products ADD image_blob
 LONGBLOB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
