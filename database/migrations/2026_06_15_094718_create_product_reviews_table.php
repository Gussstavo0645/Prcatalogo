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
    Schema::create('product_reviews', function (Blueprint $table) {
        $table->id();

        // Producto de admin_ml
        $table->string('code', 50);
        $table->string('color', 50)->nullable();

        // Calificación
        $table->unsignedTinyInteger('rating'); // 1 a 5
        $table->text('comment')->nullable();

        // Cliente / pedido de tu web
        $table->string('customer_code', 50)->nullable();
        $table->unsignedBigInteger('pedido_id')->nullable();

        // Control
        $table->boolean('approved')->default(true);

        $table->timestamps();

        $table->index(['code', 'color']);
        $table->index('customer_code');
        $table->index('pedido_id');
    });
}
};
