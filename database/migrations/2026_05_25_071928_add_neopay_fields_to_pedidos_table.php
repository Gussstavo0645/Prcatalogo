<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pedidos', 'pago_estado')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->string('pago_estado')->default('pendiente')->after('total');
            });
        }

        if (!Schema::hasColumn('pedidos', 'pago_gateway')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->string('pago_gateway')->nullable()->after('pago_estado');
            });
        }

        // Esta ya existe en tu tabla, por eso la protegemos
        if (!Schema::hasColumn('pedidos', 'pago_metodo')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->string('pago_metodo')->nullable()->after('pago_gateway');
            });
        }

        if (!Schema::hasColumn('pedidos', 'pago_referencia')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->string('pago_referencia')->nullable()->after('pago_metodo');
            });
        }

        if (!Schema::hasColumn('pedidos', 'pago_transaccion_id')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->string('pago_transaccion_id')->nullable()->after('pago_referencia');
            });
        }

        if (!Schema::hasColumn('pedidos', 'pago_token')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->string('pago_token')->nullable()->after('pago_transaccion_id');
            });
        }

        if (!Schema::hasColumn('pedidos', 'pago_monto')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->decimal('pago_monto', 10, 2)->nullable()->after('pago_token');
            });
        }

        if (!Schema::hasColumn('pedidos', 'pago_moneda')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->string('pago_moneda', 10)->default('GTQ')->after('pago_monto');
            });
        }

        if (!Schema::hasColumn('pedidos', 'pago_respuesta')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->json('pago_respuesta')->nullable()->after('pago_moneda');
            });
        }

        if (!Schema::hasColumn('pedidos', 'pagado_en')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->timestamp('pagado_en')->nullable()->after('pago_respuesta');
            });
        }
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $columns = [
                'pago_estado',
                'pago_gateway',
                // No borres pago_metodo si ya lo usabas antes
                'pago_referencia',
                'pago_transaccion_id',
                'pago_token',
                'pago_monto',
                'pago_moneda',
                'pago_respuesta',
                'pagado_en',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('pedidos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};