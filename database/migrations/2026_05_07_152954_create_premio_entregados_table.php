<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('premios_entregados', function (Blueprint $table) {
            $table->id();

            // Pedido donde se entregó el premio
            $table->foreignId('pedido_id')
                ->nullable()
                ->constrained('pedidos')
                ->nullOnDelete();

            // Cliente que ganó el premio
            $table->string('Codcliente', 50);

            // Tienda que entregó el premio
            $table->foreignId('store_id')
                ->nullable()
                ->constrained('stores')
                ->nullOnDelete();

            // Mes del premio: ejemplo 05/2026
            $table->string('mesope', 7);

            // Código del premio: C1 o C2
            $table->string('codtproducto', 10);

            // Producto premio que viene de admin_ml
            $table->string('producto', 50)->nullable();
            $table->string('color', 50)->nullable();

            // Cantidad del premio
            $table->integer('cantidad')->default(1);

            // Total que llevaba acumulado el cliente
            $table->decimal('total_acumulado', 10, 2)->default(0);

            // Estado del premio
            $table->enum('estado', ['pendiente', 'entregado', 'anulado'])
                ->default('entregado');

            // Usuario que entregó el premio
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Fecha real de entrega
            $table->timestamp('fecha_entrega')->nullable();

            // Nota opcional
            $table->text('observacion')->nullable();

            $table->timestamps();

            // Evita que un cliente reciba más de un premio en el mismo mes
            $table->unique(['Codcliente', 'mesope']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('premios_entregados');
    }
};