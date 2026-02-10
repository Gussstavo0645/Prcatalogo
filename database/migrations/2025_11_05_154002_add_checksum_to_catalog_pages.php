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
    Schema::table('catalog_pages', function (Blueprint $t) {
        $t->string('checksum', 64)->nullable()->after('image_path');
        $t->unique(['catalog_id', 'checksum']); // evita mismo archivo en el mismo catálogo
    });
}

public function down(): void
{
    Schema::table('catalog_pages', function (Blueprint $t) {
        $t->dropUnique(['catalog_id', 'checksum']);
        $t->dropColumn('checksum');
    });
}

};
