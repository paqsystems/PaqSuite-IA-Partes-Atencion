-- Contrato TR-013: detalle de paquete. 422 lógico si es_tarea = 1.
CREATE OR ALTER PROCEDURE dbo.pq_sp_partes_paquete_get
    @p_actor_tipo_funcional NVARCHAR(20),
    @p_actor_asistente_id INT,
    @p_actor_cliente_id INT = NULL,
    @p_actor_es_supervisor BIT,
    @p_id INT
AS
BEGIN
    SET NOCOUNT ON;
    IF @p_actor_es_supervisor = 0 OR @p_actor_asistente_id IS NULL
    BEGIN
        RAISERROR('partes.paquete.soloSupervisor', 16, 1);
        RETURN;
    END

    IF NOT EXISTS (SELECT 1 FROM dbo.PQ_PARTES_REGISTRO_TAREA WITH (NOLOCK) WHERE id = @p_id)
    BEGIN
        RAISERROR('partes.paquete.notFound', 16, 1);
        RETURN;
    END
    IF EXISTS (SELECT 1 FROM dbo.PQ_PARTES_REGISTRO_TAREA WITH (NOLOCK) WHERE id = @p_id AND es_tarea = 1)
    BEGIN
        RAISERROR('partes.paquete.notPaquete', 16, 1);
        RETURN;
    END

    SELECT r.id, r.usuario_id, r.cliente_id, r.tipo_tarea_id, r.fecha, r.duracion_minutos,
           r.sin_cargo, r.presencial, r.observacion, r.cerrado, r.es_tarea, r.row_version,
           r.created_at, r.updated_at,
           u.code AS usuario_code, u.nombre AS usuario_nombre,
           c.code AS cliente_code, c.nombre AS cliente_nombre,
           t.code AS tipo_tarea_code, t.descripcion AS tipo_tarea_descripcion
    FROM dbo.PQ_PARTES_REGISTRO_TAREA r WITH (NOLOCK)
    INNER JOIN dbo.PQ_PARTES_USUARIOS u WITH (NOLOCK) ON u.id = r.usuario_id
    INNER JOIN dbo.PQ_PARTES_CLIENTES c WITH (NOLOCK) ON c.id = r.cliente_id
    INNER JOIN dbo.PQ_PARTES_TIPOS_TAREA t WITH (NOLOCK) ON t.id = r.tipo_tarea_id
    WHERE r.id = @p_id;
END
GO
