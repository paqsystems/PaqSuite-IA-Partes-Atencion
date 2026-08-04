<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GEN-16 BYOK — adopción host Partes (TR-008).
 * Acceso productivo vía SP pq_sp_llm_* (BASE 74).
 *
 * Tras migrate en cada entorno SQL Server, desplegar los 7 scripts:
 *   backend/database/sp/pq_sp_llm_*.sql
 * (canónicos: PaqSuite-IA-FRAMEWORK/packages/php/laravel-core/database/sp/).
 * Guía Framework: docs/06-operacion/adopcion-gen-16-byok.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pq_llm_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nombre', 128);
            $table->string('proveedor', 64);
            $table->string('modelo', 128);
            $table->text('secreto_cifrado');
            $table->string('base_url', 255)->nullable();
            $table->boolean('supports_vision')->default(false);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->index(['user_id'], 'pq_llm_credentials_user_idx');
            $table->index(['user_id', 'enabled'], 'pq_llm_credentials_user_enabled_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('active_llm_credential_id')->nullable()->after('open_in_new_tab');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('active_llm_credential_id');
        });

        Schema::dropIfExists('pq_llm_credentials');
    }
};
