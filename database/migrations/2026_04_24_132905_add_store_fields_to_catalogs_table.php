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
        Schema::table('catalogs', function (Blueprint $table) {
            $table->string('store_name')->nullable()->after('description');
             $table->string('store_address')->nullable()->after('store_name');
            $table->string('store_hours')->nullable()->after('store_address');
$table->string('store_manager')->nullable()->after('store_hours');
$table->string('watsapp_number')->nullable()->after('store_manager');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogs', function (Blueprint $table) {
            $table->dropColumn([
                'store_name',
                'store_address',
                'store_hours',
                'store_manager',
                'watsapp_number',
            ]);
        });
    }
};
