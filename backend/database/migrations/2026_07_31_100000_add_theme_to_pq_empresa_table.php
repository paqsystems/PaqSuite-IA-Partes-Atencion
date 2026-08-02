<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GEN-06-empresas (TR-06-empresas §1/§5, D1-06-26): `theme` = clave `data-theme`
 * de la whitelist efectiva DevExtreme; default/fallback `generic.light`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pq_empresa', function (Blueprint $table) {
            $table->string('theme', 64)->nullable()->default('generic.light')->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('pq_empresa', function (Blueprint $table) {
            $table->dropColumn('theme');
        });
    }
};
