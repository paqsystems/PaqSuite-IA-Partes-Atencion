<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GEN-12 pivot layouts personales (Partes MVP).
 * Sin FK a catálogo pq_pivots_consultas: consulta_id es clave libre del host.
 * Producción: preferir SP pq_sp_pivot_layout_*.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_pivots_config')) {
            Schema::create('pq_pivots_config', function (Blueprint $table) {
                $table->id();
                $table->string('consulta_id');
                $table->string('layout_name');
                $table->longText('state_json');
                $table->unsignedBigInteger('created_by_user_id');
                $table->timestamps();

                $table->unique(['consulta_id', 'layout_name'], 'pq_pivots_config_consulta_name_uq');
                $table->foreign('created_by_user_id')->references('id')->on('users');
            });
        }

        if (! Schema::hasTable('pq_pivots_config_last_used')) {
            Schema::create('pq_pivots_config_last_used', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('consulta_id');
                $table->unsignedBigInteger('layout_id')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'consulta_id'], 'pq_pivots_config_last_used_uq');
                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('layout_id')->references('id')->on('pq_pivots_config');
            });
        }
    }

    public function down(): void
    {
        // No drop: puede haber datos de layouts de usuario.
    }
};
