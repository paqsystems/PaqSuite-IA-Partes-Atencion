<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('PQ_PARTES_USUARIOS')) {
            Schema::create('PQ_PARTES_USUARIOS', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('code', 50);
                $table->string('nombre', 255);
                $table->string('email', 255)->nullable();
                $table->boolean('supervisor')->default(false);
                $table->boolean('activo')->default(true);
                $table->boolean('inhabilitado')->default(false);
                $table->dateTime('created_at', 3)->nullable();
                $table->dateTime('updated_at', 3)->nullable();

                $table->unique('user_id', 'pq_partes_usuarios_user_id_uq');
                $table->unique('code', 'pq_partes_usuarios_code_uq');
                $table->foreign('user_id', 'pq_partes_usuarios_user_id_fk')
                    ->references('id')
                    ->on('users');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('PQ_PARTES_USUARIOS');
    }
};
