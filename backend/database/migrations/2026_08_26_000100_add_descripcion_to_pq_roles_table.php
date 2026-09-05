<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SPEC seguridad: columna `descripcion` en pq_roles.
 * pq_rol_atributos ya existe (permiso_alta/baja/modi/repo) — no recrear.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pq_roles') && ! Schema::hasColumn('pq_roles', 'descripcion')) {
            Schema::table('pq_roles', function (Blueprint $table) {
                $table->string('descripcion', 500)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pq_roles') && Schema::hasColumn('pq_roles', 'descripcion')) {
            Schema::table('pq_roles', function (Blueprint $table) {
                $table->dropColumn('descripcion');
            });
        }
    }
};
