-- Destinatarios únicos (users vinculados a clientes) del universo filtrado del proceso masivo.
-- Parámetros alineados a pq_sp_partes_tarea_list (actor + filtros fechas/cliente/usuario/estado/columnas).
CREATE OR ALTER PROCEDURE dbo.pq_sp_partes_masivo_destinatarios_novedad
    @p_actor_tipo_funcional NVARCHAR(32),
    @p_actor_asistente_id INT = NULL,
    @p_actor_cliente_id INT = NULL,
    @p_actor_es_supervisor BIT = 0,
    @p_fecha_desde DATE,
    @p_fecha_hasta DATE,
    @p_cliente_id INT = NULL,
    @p_usuario_id INT = NULL,
    @p_estado_cerrado NVARCHAR(16) = N'todas',
    @p_cliente_code NVARCHAR(64) = NULL,
    @p_cliente_nombre NVARCHAR(256) = NULL,
    @p_tipo_tarea_code NVARCHAR(64) = NULL,
    @p_tipo_tarea_descripcion NVARCHAR(256) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    IF @p_fecha_desde IS NULL OR @p_fecha_hasta IS NULL
    BEGIN
        RAISERROR(N'partes.tarea.fechasRequeridas', 16, 1);
        RETURN;
    END;

    IF ISNULL(@p_actor_es_supervisor, 0) = 0
    BEGIN
        RAISERROR(N'partes.masivo.forbidden', 16, 1);
        RETURN;
    END;

    ;WITH filtro AS (
        SELECT DISTINCT r.cliente_id
        FROM dbo.PQ_PARTES_REGISTRO_TAREA r WITH (NOLOCK)
        INNER JOIN dbo.PQ_PARTES_USUARIOS u WITH (NOLOCK) ON u.id = r.usuario_id
        INNER JOIN dbo.PQ_PARTES_CLIENTES c WITH (NOLOCK) ON c.id = r.cliente_id
        INNER JOIN dbo.PQ_PARTES_TIPOS_TAREA t WITH (NOLOCK) ON t.id = r.tipo_tarea_id
        WHERE r.es_tarea = 1
          AND CAST(r.fecha AS DATE) >= @p_fecha_desde
          AND CAST(r.fecha AS DATE) <= @p_fecha_hasta
          AND (@p_cliente_id IS NULL OR r.cliente_id = @p_cliente_id)
          AND (@p_usuario_id IS NULL OR r.usuario_id = @p_usuario_id)
          AND (
                @p_estado_cerrado = N'todas'
                OR (@p_estado_cerrado = N'abiertas' AND r.cerrado = 0)
                OR (@p_estado_cerrado = N'cerradas' AND r.cerrado = 1)
          )
          AND (@p_cliente_code IS NULL OR c.code LIKE N'%' + @p_cliente_code + N'%')
          AND (@p_cliente_nombre IS NULL OR c.nombre LIKE N'%' + @p_cliente_nombre + N'%')
          AND (@p_tipo_tarea_code IS NULL OR t.code LIKE N'%' + @p_tipo_tarea_code + N'%')
          AND (@p_tipo_tarea_descripcion IS NULL OR t.descripcion LIKE N'%' + @p_tipo_tarea_descripcion + N'%')
    )
    SELECT DISTINCT
        fu.id AS user_id,
        fu.email,
        fu.usuario AS codigo,
        fu.locale,
        fu.name
    FROM filtro f
    INNER JOIN dbo.PQ_PARTES_CLIENTES c WITH (NOLOCK) ON c.id = f.cliente_id
    INNER JOIN dbo.users fu WITH (NOLOCK) ON fu.id = c.user_id
    WHERE c.user_id IS NOT NULL
      AND c.activo = 1 AND c.inhabilitado = 0
      AND fu.activo = 1 AND fu.inhabilitado = 0
      AND fu.email IS NOT NULL AND LTRIM(RTRIM(fu.email)) <> N''
    ORDER BY fu.id;
END
GO
