-- Contrato TR-013: insert/update paquete. Fuerza es_tarea=0; update no cambia usuario_id.
-- Runtime MONO = PartesPaqueteOperations (validaciones completas ahí).
CREATE OR ALTER PROCEDURE dbo.pq_sp_partes_paquete_upsert
    @p_actor_tipo_funcional NVARCHAR(20),
    @p_actor_asistente_id INT,
    @p_actor_cliente_id INT = NULL,
    @p_actor_es_supervisor BIT,
    @p_id INT = NULL,
    @p_cliente_id INT,
    @p_fecha DATE,
    @p_duracion_minutos INT,
    @p_observacion NVARCHAR(MAX),
    @p_row_version NVARCHAR(32) = NULL,
    @p_confirmar_fecha_futura BIT = 0
AS
BEGIN
    SET NOCOUNT ON;
    IF @p_actor_es_supervisor = 0 OR @p_actor_asistente_id IS NULL
    BEGIN
        RAISERROR('partes.paquete.soloSupervisor', 16, 1);
        RETURN;
    END
    IF @p_cliente_id IS NULL OR @p_fecha IS NULL OR NULLIF(LTRIM(RTRIM(@p_observacion)), '') IS NULL
    BEGIN
        RAISERROR('partes.paquete.camposObligatorios', 16, 1);
        RETURN;
    END
    IF @p_duracion_minutos IS NULL
        OR @p_duracion_minutos < 60
        OR @p_duracion_minutos > 14400
        OR @p_duracion_minutos % 60 <> 0
    BEGIN
        RAISERROR('partes.paquete.duracionInvalida', 16, 1);
        RETURN;
    END

    DECLARE @tipoDefault INT;
    SELECT TOP 1 @tipoDefault = id
    FROM dbo.PQ_PARTES_TIPOS_TAREA WITH (NOLOCK)
    WHERE is_default = 1 AND activo = 1 AND inhabilitado = 0;
    IF @tipoDefault IS NULL
    BEGIN
        RAISERROR('partes.paquete.tipoDefaultFaltante', 16, 1);
        RETURN;
    END

    IF @p_id IS NULL
    BEGIN
        INSERT INTO dbo.PQ_PARTES_REGISTRO_TAREA (
            usuario_id, cliente_id, tipo_tarea_id, fecha, duracion_minutos,
            sin_cargo, presencial, observacion, cerrado, es_tarea, created_at, updated_at
        ) VALUES (
            @p_actor_asistente_id, @p_cliente_id, @tipoDefault, @p_fecha, @p_duracion_minutos,
            0, 0, @p_observacion, 0, 0, SYSUTCDATETIME(), SYSUTCDATETIME()
        );
        SET @p_id = SCOPE_IDENTITY();
    END
    ELSE
    BEGIN
        IF NOT EXISTS (SELECT 1 FROM dbo.PQ_PARTES_REGISTRO_TAREA WHERE id = @p_id)
        BEGIN
            RAISERROR('partes.paquete.notFound', 16, 1);
            RETURN;
        END
        IF EXISTS (SELECT 1 FROM dbo.PQ_PARTES_REGISTRO_TAREA WHERE id = @p_id AND es_tarea = 1)
        BEGIN
            RAISERROR('partes.paquete.notPaquete', 16, 1);
            RETURN;
        END
        UPDATE dbo.PQ_PARTES_REGISTRO_TAREA
        SET cliente_id = @p_cliente_id,
            tipo_tarea_id = @tipoDefault,
            fecha = @p_fecha,
            duracion_minutos = @p_duracion_minutos,
            sin_cargo = 0,
            presencial = 0,
            observacion = @p_observacion,
            es_tarea = 0,
            cerrado = 0,
            updated_at = SYSUTCDATETIME()
        WHERE id = @p_id;
    END

    EXEC dbo.pq_sp_partes_paquete_get
        @p_actor_tipo_funcional = @p_actor_tipo_funcional,
        @p_actor_asistente_id = @p_actor_asistente_id,
        @p_actor_cliente_id = @p_actor_cliente_id,
        @p_actor_es_supervisor = @p_actor_es_supervisor,
        @p_id = @p_id;
END
GO
