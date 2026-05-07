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
        if (!Schema::hasColumn('pedidos', 'store_id')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->foreignId('store_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('stores')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pedidos', 'store_id')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->dropConstrainedForeignId('store_id');
            });
        }
    }
};