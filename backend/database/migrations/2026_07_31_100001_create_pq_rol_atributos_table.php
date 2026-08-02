<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GEN-06-roles-atributos: `PQ_RolAtributo` — 4 flags por opción de menú tipo proceso.
 * Sync completo por rol (PUT reemplaza el set; ausentes = sin atributo, D1-06-10).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pq_rol_atributos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('pq_roles')->cascadeOnDelete();
            $table->unsignedBigInteger('menu_id');
            $table->boolean('permiso_alta')->default(false);
            $table->boolean('permiso_baja')->default(false);
            $table->boolean('permiso_modi')->default(false);
            $table->boolean('permiso_repo')->default(false);
            $table->timestamps();

            $table->foreign('menu_id')->references('id')->on('pq_menus');
            $table->unique(['rol_id', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_rol_atributos');
    }
};
