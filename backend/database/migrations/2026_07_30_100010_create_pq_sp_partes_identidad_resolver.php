<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        DB::unprepared("
IF OBJECT_ID(N'dbo.pq_sp_partes_identidad_resolver', N'P') IS NOT NULL
    DROP PROCEDURE dbo.pq_sp_partes_identidad_resolver;
");

        DB::unprepared("
CREATE PROCEDURE dbo.pq_sp_partes_identidad_resolver
    @p_user_id bigint
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @asistente_id bigint = NULL;
    DECLARE @asistente_code nvarchar(50) = NULL;
    DECLARE @asistente_nombre nvarchar(255) = NULL;
    DECLARE @asistente_email nvarchar(255) = NULL;
    DECLARE @es_supervisor bit = 0;

    DECLARE @cliente_id bigint = NULL;
    DECLARE @cliente_code nvarchar(50) = NULL;
    DECLARE @cliente_nombre nvarchar(255) = NULL;
    DECLARE @cliente_email nvarchar(255) = NULL;

    SELECT TOP 1
        @asistente_id = u.id,
        @asistente_code = u.code,
        @asistente_nombre = u.nombre,
        @asistente_email = u.email,
        @es_supervisor = u.supervisor
    FROM dbo.PQ_PARTES_USUARIOS u WITH (NOLOCK)
    WHERE u.user_id = @p_user_id
      AND u.activo = 1
      AND u.inhabilitado = 0;

    SELECT TOP 1
        @cliente_id = c.id,
        @cliente_code = c.code,
        @cliente_nombre = c.nombre,
        @cliente_email = c.email
    FROM dbo.PQ_PARTES_CLIENTES c WITH (NOLOCK)
    WHERE c.user_id = @p_user_id
      AND c.activo = 1
      AND c.inhabilitado = 0;

    IF @asistente_id IS NOT NULL AND @cliente_id IS NOT NULL
    BEGIN
        SELECT
            CAST(2 AS int) AS codigo_resultado,
            CAST(NULL AS nvarchar(20)) AS tipo_funcional,
            CAST(NULL AS bigint) AS asistente_id,
            CAST(NULL AS bigint) AS cliente_id,
            CAST(0 AS bit) AS es_supervisor,
            CAST(NULL AS nvarchar(50)) AS code,
            CAST(NULL AS nvarchar(255)) AS nombre,
            CAST(NULL AS nvarchar(255)) AS email;
        RETURN;
    END

    IF @asistente_id IS NOT NULL
    BEGIN
        SELECT
            CAST(0 AS int) AS codigo_resultado,
            CAST(N'asistente' AS nvarchar(20)) AS tipo_funcional,
            @asistente_id AS asistente_id,
            CAST(NULL AS bigint) AS cliente_id,
            @es_supervisor AS es_supervisor,
            @asistente_code AS code,
            @asistente_nombre AS nombre,
            @asistente_email AS email;
        RETURN;
    END

    IF @cliente_id IS NOT NULL
    BEGIN
        SELECT
            CAST(0 AS int) AS codigo_resultado,
            CAST(N'cliente' AS nvarchar(20)) AS tipo_funcional,
            CAST(NULL AS bigint) AS asistente_id,
            @cliente_id AS cliente_id,
            CAST(0 AS bit) AS es_supervisor,
            @cliente_code AS code,
            @cliente_nombre AS nombre,
            @cliente_email AS email;
        RETURN;
    END

    SELECT
        CAST(1 AS int) AS codigo_resultado,
        CAST(NULL AS nvarchar(20)) AS tipo_funcional,
        CAST(NULL AS bigint) AS asistente_id,
        CAST(NULL AS bigint) AS cliente_id,
        CAST(0 AS bit) AS es_supervisor,
        CAST(NULL AS nvarchar(50)) AS code,
        CAST(NULL AS nvarchar(255)) AS nombre,
        CAST(NULL AS nvarchar(255)) AS email;
END
");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        DB::unprepared("
IF OBJECT_ID(N'dbo.pq_sp_partes_identidad_resolver', N'P') IS NOT NULL
    DROP PROCEDURE dbo.pq_sp_partes_identidad_resolver;
");
    }
};
