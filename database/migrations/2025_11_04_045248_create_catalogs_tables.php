<?php
// database/migrations/2025_11_03_000000_create_catalogs_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('catalogs', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->boolean('is_public')->default(true);
            $t->timestamps();
        });

        Schema::create('catalog_pages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('catalog_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('page_number');
            $t->dropColumn('image_path'); // storage path: public/catalogs/{id}/page-001.jpg
            $t->string('thumb_path')->nullable();
            $t->json('meta')->nullable(); // hotspots, títulos, etc.
            $t->timestamps();
            $t->unique(['catalog_id','page_number']);
           $t->string('mime'); 
        });
        DB::statement("ALTER TABLE catalog_pages ADD archivo_binario LONGBLOB");
    }

    public function down(): void {
        Schema::dropIfExists('catalog_pages');
        Schema::dropIfExists('catalogs');
       Schema::table('catalog_pages', function (Blueprint $table) {
        $table->string('image_path')->nullable();
    });
    }
};
