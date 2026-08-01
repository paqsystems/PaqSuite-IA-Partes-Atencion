<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * TR-001-update / SPEC-001-update: bit es_tarea en registro de tareas.
 * Default 1 = tarea de carga; 0 = compra/paquete (proceso a definir).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('PQ_PARTES_REGISTRO_TAREA')) {
            return;
        }
        if (Schema::hasColumn('PQ_PARTES_REGISTRO_TAREA', 'es_tarea')) {
            return;
        }

        Schema::table('PQ_PARTES_REGISTRO_TAREA', function (Blueprint $table) {
            $table->boolean('es_tarea')->default(true);
        });

        // Históricos al momento del alta de columna = tarea de carga (AC-U02).
        DB::table('PQ_PARTES_REGISTRO_TAREA')->update(['es_tarea' => true]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('PQ_PARTES_REGISTRO_TAREA')) {
            return;
        }
        if (! Schema::hasColumn('PQ_PARTES_REGISTRO_TAREA', 'es_tarea')) {
            return;
        }

        Schema::table('PQ_PARTES_REGISTRO_TAREA', function (Blueprint $table) {
            $table->dropColumn('es_tarea');
        });
    }
};
