<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('PQ_PARTES_CLIENTE_TIPO_TAREA')) {
            Schema::create('PQ_PARTES_CLIENTE_TIPO_TAREA', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cliente_id');
                $table->unsignedBigInteger('tipo_tarea_id');
                $table->dateTime('created_at', 3)->nullable();
                $table->dateTime('updated_at', 3)->nullable();

                $table->unique(['cliente_id', 'tipo_tarea_id'], 'pq_partes_cliente_tipo_tarea_uq');
                $table->foreign('cliente_id', 'pq_partes_ctt_cliente_fk')
                    ->references('id')
                    ->on('PQ_PARTES_CLIENTES');
                $table->foreign('tipo_tarea_id', 'pq_partes_ctt_tipo_tarea_fk')
                    ->references('id')
                    ->on('PQ_PARTES_TIPOS_TAREA');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('PQ_PARTES_CLIENTE_TIPO_TAREA');
    }
};
