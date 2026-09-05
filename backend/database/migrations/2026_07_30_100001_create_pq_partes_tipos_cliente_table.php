<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('PQ_PARTES_TIPOS_CLIENTE')) {
            Schema::create('PQ_PARTES_TIPOS_CLIENTE', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50);
                $table->string('descripcion', 255);
                $table->boolean('activo')->default(true);
                $table->boolean('inhabilitado')->default(false);
                $table->dateTime('created_at', 3)->nullable();
                $table->dateTime('updated_at', 3)->nullable();

                $table->unique('code', 'pq_partes_tipos_cliente_code_uq');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('PQ_PARTES_TIPOS_CLIENTE');
    }
};
