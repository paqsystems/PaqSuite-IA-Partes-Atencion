<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pq_parametros_gral', function (Blueprint $table) {
            $table->id();
            $table->string('programa', 100);
            $table->string('clave', 150);
            $table->char('tipo_valor', 1);
            $table->string('valor_string', 500)->nullable();
            $table->text('valor_texto')->nullable();
            $table->integer('valor_int')->nullable();
            $table->decimal('valor_decimal', 18, 6)->nullable();
            $table->boolean('valor_bool')->nullable();
            $table->dateTime('valor_fecha')->nullable();
            $table->string('precision_fecha', 20)->nullable();
            $table->string('caption', 200)->nullable();
            $table->string('tooltip', 500)->nullable();
            $table->text('meta_json')->nullable();
            $table->timestamps();

            $table->unique(['programa', 'clave'], 'pq_parametros_gral_programa_clave_uq');
            $table->index(['programa'], 'pq_parametros_gral_programa_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_parametros_gral');
    }
};
