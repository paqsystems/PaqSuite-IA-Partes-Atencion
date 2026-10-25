-- Contrato TR-013: listar compras de horas (es_tarea = 0). Runtime MONO = PartesPaqueteOperations.
CREATE OR ALTER PROCEDURE dbo.pq_sp_partes_paquete_list
    @p_actor_tipo_funcional NVARCHAR(20),
    @p_actor_asistente_id INT,
    @p_actor_cliente_id INT = NULL,
    @p_actor_es_supervisor BIT,
    @p_fecha_desde DATE,
    @p_fecha_hasta DATE,
    @p_cliente_id INT = NULL,
    @p_page INT = 1,
    @p_page_size INT = 50
AS
BEGIN
    SET NOCOUNT ON;
    IF @p_actor_es_supervisor = 0 OR @p_actor_asistente_id IS NULL
    BEGIN
        RAISERROR('partes.paquete.soloSupervisor', 16, 1);
        RETURN;
    END
    IF @p_fecha_desde IS NULL OR @p_fecha_hasta IS NULL
    BEGIN
        RAISERROR('partes.paquete.fechasRequeridas', 16, 1);
        RETURN;
    END

    DECLARE @total INT;
    SELECT @total = COUNT(1)
    FROM dbo.PQ_PARTES_REGISTRO_TAREA r WITH (NOLOCK)
    WHERE r.es_tarea = 0
      AND r.fecha >= @p_fecha_desde
      AND r.fecha <= @p_fecha_hasta
      AND (@p_cliente_id IS NULL OR r.cliente_id = @p_cliente_id);

    DECLARE @off INT = (CASE WHEN @p_page < 1 THEN 0 ELSE (@p_page - 1) * CASE WHEN @p_page_size < 1 THEN 50 ELSE @p_page_size END END);

    SELECT r.id, r.usuario_id, r.cliente_id, r.tipo_tarea_id, r.fecha, r.duracion_minutos,
           r.sin_cargo, r.presencial, r.observacion, r.cerrado, r.es_tarea, r.row_version,
           r.created_at, r.updated_at,
           u.code AS usuario_code, u.nombre AS usuario_nombre,
           c.code AS cliente_code, c.nombre AS cliente_nombre,
           t.code AS tipo_tarea_code, t.descripcion AS tipo_tarea_descripcion,
           @total AS _total
    FROM dbo.PQ_PARTES_REGISTRO_TAREA r WITH (NOLOCK)
    INNER JOIN dbo.PQ_PARTES_USUARIOS u WITH (NOLOCK) ON u.id = r.usuario_id
    INNER JOIN dbo.PQ_PARTES_CLIENTES c WITH (NOLOCK) ON c.id = r.cliente_id
    INNER JOIN dbo.PQ_PARTES_TIPOS_TAREA t WITH (NOLOCK) ON t.id = r.tipo_tarea_id
    WHERE r.es_tarea = 0
      AND r.fecha >= @p_fecha_desde
      AND r.fecha <= @p_fecha_hasta
      AND (@p_cliente_id IS NULL OR r.cliente_id = @p_cliente_id)
    ORDER BY r.fecha DESC, r.id DESC
    OFFSET @off ROWS FETCH NEXT (CASE WHEN @p_page_size < 1 THEN 50 ELSE @p_page_size END) ROWS ONLY;
END
GO
