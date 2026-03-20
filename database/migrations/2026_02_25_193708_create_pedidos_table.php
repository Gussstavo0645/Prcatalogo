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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();

            // los datos del cliente
            $table->string('nombre_cliente');
            $table->string('telefono_cliente');
            $table->string('cliente_correo')->nullable();
            $table->string('cliente_contraseña')->nullable();

//total de pedido
            $table->decimal('total',10,2)->default(0);
            // estado del pedido
            $table->enum('status',[
                'pendiente',
                'confirmado',
                'enviado',
                'entregado',
                'cancelado',
            ])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
