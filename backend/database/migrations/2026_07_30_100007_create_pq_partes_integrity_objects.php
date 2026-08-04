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
IF OBJECT_ID(N'dbo.pq_sp_partes_assert_user_id_exclusividad', N'P') IS NOT NULL
    DROP PROCEDURE dbo.pq_sp_partes_assert_user_id_exclusividad;
");

        DB::unprepared("
CREATE PROCEDURE dbo.pq_sp_partes_assert_user_id_exclusividad
    @p_user_id bigint,
    @p_lado nvarchar(20)
AS
BEGIN
    SET NOCOUNT ON;

    IF @p_user_id IS NULL
    BEGIN
        IF LOWER(@p_lado) = N'cliente'
        BEGIN
            RETURN;
        END
        RAISERROR(N'PARTES_EXCLUSIVIDAD_USER_ID: user_id requerido para lado usuario', 16, 1);
        RETURN;
    END

    IF LOWER(@p_lado) = N'usuario'
    BEGIN
        IF EXISTS (
            SELECT 1 FROM dbo.PQ_PARTES_CLIENTES WITH (UPDLOCK, HOLDLOCK)
            WHERE user_id = @p_user_id
        )
        BEGIN
            RAISERROR(N'PARTES_EXCLUSIVIDAD_USER_ID: user_id ya vinculado como cliente', 16, 1);
            RETURN;
        END
        RETURN;
    END

    IF LOWER(@p_lado) = N'cliente'
    BEGIN
        IF EXISTS (
            SELECT 1 FROM dbo.PQ_PARTES_USUARIOS WITH (UPDLOCK, HOLDLOCK)
            WHERE user_id = @p_user_id
        )
        BEGIN
            RAISERROR(N'PARTES_EXCLUSIVIDAD_USER_ID: user_id ya vinculado como asistente', 16, 1);
            RETURN;
        END
        RETURN;
    END

    RAISERROR(N'PARTES_EXCLUSIVIDAD_USER_ID: lado invalido (usuario|cliente)', 16, 1);
END
");

        DB::unprepared("
IF OBJECT_ID(N'dbo.pq_sp_partes_tipos_tarea_marcar_default', N'P') IS NOT NULL
    DROP PROCEDURE dbo.pq_sp_partes_tipos_tarea_marcar_default;
");

        DB::unprepared("
CREATE PROCEDURE dbo.pq_sp_partes_tipos_tarea_marcar_default
    @p_tipo_tarea_id bigint
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    BEGIN TRAN;

    IF NOT EXISTS (
        SELECT 1 FROM dbo.PQ_PARTES_TIPOS_TAREA WITH (UPDLOCK, HOLDLOCK)
        WHERE id = @p_tipo_tarea_id
    )
    BEGIN
        ROLLBACK TRAN;
        RAISERROR(N'PARTES_TIPO_TAREA_NOT_FOUND', 16, 1);
        RETURN;
    END

    UPDATE dbo.PQ_PARTES_TIPOS_TAREA
    SET is_default = 0,
        updated_at = SYSUTCDATETIME()
    WHERE is_default = 1
      AND id <> @p_tipo_tarea_id;

    UPDATE dbo.PQ_PARTES_TIPOS_TAREA
    SET is_default = 1,
        is_generico = 1,
        updated_at = SYSUTCDATETIME()
    WHERE id = @p_tipo_tarea_id;

    COMMIT TRAN;
END
");

        DB::unprepared("
IF OBJECT_ID(N'dbo.tr_pq_partes_usuarios_exclusividad_user_id', N'TR') IS NOT NULL
    DROP TRIGGER dbo.tr_pq_partes_usuarios_exclusividad_user_id;
");

        DB::unprepared("
CREATE TRIGGER dbo.tr_pq_partes_usuarios_exclusividad_user_id
ON dbo.PQ_PARTES_USUARIOS
AFTER INSERT, UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (
        SELECT 1
        FROM inserted i
        INNER JOIN dbo.PQ_PARTES_CLIENTES c ON c.user_id = i.user_id
    )
    BEGIN
        RAISERROR(N'PARTES_EXCLUSIVIDAD_USER_ID: user_id ya vinculado como cliente', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END
END
");

        DB::unprepared("
IF OBJECT_ID(N'dbo.tr_pq_partes_clientes_exclusividad_user_id', N'TR') IS NOT NULL
    DROP TRIGGER dbo.tr_pq_partes_clientes_exclusividad_user_id;
");

        DB::unprepared("
CREATE TRIGGER dbo.tr_pq_partes_clientes_exclusividad_user_id
ON dbo.PQ_PARTES_CLIENTES
AFTER INSERT, UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (
        SELECT 1
        FROM inserted i
        INNER JOIN dbo.PQ_PARTES_USUARIOS u ON u.user_id = i.user_id
        WHERE i.user_id IS NOT NULL
    )
    BEGIN
        RAISERROR(N'PARTES_EXCLUSIVIDAD_USER_ID: user_id ya vinculado como asistente', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END
END
");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        DB::unprepared("
IF OBJECT_ID(N'dbo.tr_pq_partes_clientes_exclusividad_user_id', N'TR') IS NOT NULL
    DROP TRIGGER dbo.tr_pq_partes_clientes_exclusividad_user_id;
IF OBJECT_ID(N'dbo.tr_pq_partes_usuarios_exclusividad_user_id', N'TR') IS NOT NULL
    DROP TRIGGER dbo.tr_pq_partes_usuarios_exclusividad_user_id;
IF OBJECT_ID(N'dbo.pq_sp_partes_tipos_tarea_marcar_default', N'P') IS NOT NULL
    DROP PROCEDURE dbo.pq_sp_partes_tipos_tarea_marcar_default;
IF OBJECT_ID(N'dbo.pq_sp_partes_assert_user_id_exclusividad', N'P') IS NOT NULL
    DROP PROCEDURE dbo.pq_sp_partes_assert_user_id_exclusividad;
");
    }
};
