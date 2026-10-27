<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('PQ_PARTES_CLIENTE_TIPO_TAREA')) {
            Schema::table('PQ_PARTES_CLIENTE_TIPO_TAREA', function (Blueprint $table): void {
                if (! Schema::hasColumn('PQ_PARTES_CLIENTE_TIPO_TAREA', 'activo')) {
                    $table->boolean('activo')->default(true);
                }
                if (! Schema::hasColumn('PQ_PARTES_CLIENTE_TIPO_TAREA', 'inhabilitado')) {
                    $table->boolean('inhabilitado')->default(false);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('PQ_PARTES_CLIENTE_TIPO_TAREA')) {
            Schema::table('PQ_PARTES_CLIENTE_TIPO_TAREA', function (Blueprint $table): void {
                if (Schema::hasColumn('PQ_PARTES_CLIENTE_TIPO_TAREA', 'inhabilitado')) {
                    $table->dropColumn('inhabilitado');
                }
                if (Schema::hasColumn('PQ_PARTES_CLIENTE_TIPO_TAREA', 'activo')) {
                    $table->dropColumn('activo');
                }
            });
        }
    }
};
