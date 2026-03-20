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
        Schema::table('products', function (Blueprint $table) {
            // Schema::table('products', function (Blueprint $table) {
        $table->string('barcode_type')->nullable()->after('code');
        $table->string('barcode_value')->nullable()->after('barcode_type');
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
        $table->dropColumn(['barcode_type','barcode_value']);
    });
    }
};
