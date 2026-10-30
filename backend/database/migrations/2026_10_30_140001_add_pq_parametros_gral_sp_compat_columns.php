<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Columnas de compatibilidad para pq_sp_emission_parameter_get / pq_sp_excel_parameter_get (core).
 * Partes persiste valor_bool / valor_int; el core lee valor_logico / valor_entero.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        if (! Schema::hasTable('PQ_PARAMETROS_GRAL')) {
            return;
        }

        DB::statement(
            "IF COL_LENGTH(N'dbo.PQ_PARAMETROS_GRAL', N'valor_logico') IS NULL
             BEGIN
                 ALTER TABLE dbo.PQ_PARAMETROS_GRAL
                 ADD valor_logico AS valor_bool PERSISTED;
             END"
        );

        DB::statement(
            "IF COL_LENGTH(N'dbo.PQ_PARAMETROS_GRAL', N'valor_entero') IS NULL
             BEGIN
                 ALTER TABLE dbo.PQ_PARAMETROS_GRAL
                 ADD valor_entero AS valor_int PERSISTED;
             END"
        );
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        if (! Schema::hasTable('PQ_PARAMETROS_GRAL')) {
            return;
        }

        DB::statement(
            "IF COL_LENGTH(N'dbo.PQ_PARAMETROS_GRAL', N'valor_logico') IS NOT NULL
             BEGIN
                 ALTER TABLE dbo.PQ_PARAMETROS_GRAL DROP COLUMN valor_logico;
             END"
        );

        DB::statement(
            "IF COL_LENGTH(N'dbo.PQ_PARAMETROS_GRAL', N'valor_entero') IS NOT NULL
             BEGIN
                 ALTER TABLE dbo.PQ_PARAMETROS_GRAL DROP COLUMN valor_entero;
             END"
        );
    }
};
