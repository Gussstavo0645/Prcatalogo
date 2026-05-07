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
    Schema::table('pedidos', function (Blueprint $table) {
        $table->foreignId('store_id')
            ->nullable()
            ->after('id')
            ->constrained('stores')
            ->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('pedidos', function (Blueprint $table) {
        $table->dropConstrainedForeignId('store_id');
    });
}
};
