<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('premios_entregados', function (Blueprint $table) {
            if (!Schema::hasColumn('premios_entregados', 'cantidad_c1')) {
                $table->unsignedInteger('cantidad_c1')->default(0)->after('estado');
            }

            if (!Schema::hasColumn('premios_entregados', 'cantidad_c2')) {
                $table->unsignedInteger('cantidad_c2')->default(0)->after('cantidad_c1');
            }

            if (!Schema::hasColumn('premios_entregados', 'monto_base_acumulado')) {
                $table->decimal('monto_base_acumulado', 10, 2)->default(0)->after('cantidad_c2');
            }

            if (!Schema::hasColumn('premios_entregados', 'monto_usado')) {
                $table->decimal('monto_usado', 10, 2)->default(0)->after('monto_base_acumulado');
            }

            if (!Schema::hasColumn('premios_entregados', 'saldo_restante')) {
                $table->decimal('saldo_restante', 10, 2)->default(0)->after('monto_usado');
            }

            if (!Schema::hasColumn('premios_entregados', 'fecha_canje')) {
                $table->timestamp('fecha_canje')->nullable()->after('saldo_restante');
            }
        });
    }

    public function down(): void
    {
        Schema::table('premios_entregados', function (Blueprint $table) {
            $table->dropColumn([
                'cantidad_c1',
                'cantidad_c2',
                'monto_base_acumulado',
                'monto_usado',
                'saldo_restante',
                'fecha_canje',
            ]);
        });
    }
};