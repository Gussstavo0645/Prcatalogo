<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_pdfs', function (Blueprint $table) {
            $table->id();

            // Relación opcional con un catálogo digital existente
            $table->foreignId('catalog_id')
                ->nullable()
                ->constrained('catalogs')
                ->nullOnDelete();

            $table->string('titulo');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();

            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('anio');

            // Aquí se guardarán las rutas, no el archivo completo
            $table->string('portada');
            $table->string('archivo_pdf');
            $table->string('nombre_archivo_original')->nullable();

            $table->unsignedInteger('numero_paginas')->nullable();

            $table->boolean('destacado')->default(false);
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamp('publicado_at')->nullable();

            $table->timestamps();

            $table->index(['activo', 'anio', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_pdfs');
    }
};
