<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PQ_PARTES_REGISTRO_TAREA', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('tipo_tarea_id');
            $table->date('fecha');
            $table->integer('duracion_minutos');
            $table->boolean('sin_cargo')->default(false);
            $table->boolean('presencial')->default(false);
            $table->text('observacion');
            $table->boolean('cerrado')->default(false);
            $table->dateTime('created_at', 3)->nullable();
            $table->dateTime('updated_at', 3)->nullable();

            $table->foreign('usuario_id', 'pq_partes_rt_usuario_fk')
                ->references('id')
                ->on('PQ_PARTES_USUARIOS');
            $table->foreign('cliente_id', 'pq_partes_rt_cliente_fk')
                ->references('id')
                ->on('PQ_PARTES_CLIENTES');
            $table->foreign('tipo_tarea_id', 'pq_partes_rt_tipo_tarea_fk')
                ->references('id')
                ->on('PQ_PARTES_TIPOS_TAREA');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlsrv') {
            DB::unprepared(<<<'SQL'
IF COL_LENGTH(N'dbo.PQ_PARTES_REGISTRO_TAREA', N'row_version') IS NULL
BEGIN
    ALTER TABLE dbo.PQ_PARTES_REGISTRO_TAREA ADD row_version rowversion NOT NULL;
END
SQL);
        } else {
            Schema::table('PQ_PARTES_REGISTRO_TAREA', function (Blueprint $table) {
                $table->unsignedBigInteger('row_version')->default(1);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('PQ_PARTES_REGISTRO_TAREA');
    }
};
