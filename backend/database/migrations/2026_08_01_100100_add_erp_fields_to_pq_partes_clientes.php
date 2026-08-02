<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TR-001-update-01 / SPEC-001-update-01: referencias ERP en clientes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('PQ_PARTES_CLIENTES')) {
            return;
        }

        Schema::table('PQ_PARTES_CLIENTES', function (Blueprint $table) {
            if (! Schema::hasColumn('PQ_PARTES_CLIENTES', 'erp_cliente')) {
                $table->string('erp_cliente', 15)->nullable();
            }
            if (! Schema::hasColumn('PQ_PARTES_CLIENTES', 'erp_articulo')) {
                $table->string('erp_articulo', 15)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('PQ_PARTES_CLIENTES')) {
            return;
        }

        Schema::table('PQ_PARTES_CLIENTES', function (Blueprint $table) {
            if (Schema::hasColumn('PQ_PARTES_CLIENTES', 'erp_cliente')) {
                $table->dropColumn('erp_cliente');
            }
            if (Schema::hasColumn('PQ_PARTES_CLIENTES', 'erp_articulo')) {
                $table->dropColumn('erp_articulo');
            }
        });
    }
};
