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
            Schema::table('pedidos_items', function (Blueprint $table) {
        $table->boolean('is_combo_component')->default(false)->after('subtotal');
        $table->string('combo_code')->nullable()->after('is_combo_component');
        $table->string('combo_color')->nullable()->after('combo_code');
        $table->string('combo_name')->nullable()->after('combo_color');
        $table->string('combo_group')->nullable()->after('combo_name');
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     Schema::table('pedidos_items', function (Blueprint $table) {
        $table->boolean('is_combo_component')->default(false)->after('subtotal');
        $table->string('combo_code')->nullable()->after('is_combo_component');
        $table->string('combo_color')->nullable()->after('combo_code');
        $table->string('combo_name')->nullable()->after('combo_color');
        $table->string('combo_group')->nullable()->after('combo_name');
    });
    }
};
