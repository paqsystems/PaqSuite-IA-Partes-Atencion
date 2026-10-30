-- TR-006-update CC PQ #8: cuenta corriente paquete horas (saldo running por cliente).
-- Referencia window: pq_sp_partes_informe_paquete_horas-REFERENCE-saldo-window.sql
CREATE OR ALTER PROCEDURE dbo.pq_sp_partes_informe_paquete_horas
    @p_actor_tipo_funcional NVARCHAR(20),
    @p_actor_asistente_id INT,
    @p_actor_cliente_id INT = NULL,
    @p_actor_es_supervisor BIT,
    @p_fecha_desde DATE,
    @p_fecha_hasta DATE,
    @p_cliente_id INT = NULL,
    @p_usuario_id INT = NULL,
    @p_tipo_cliente_id INT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    IF @p_fecha_desde IS NULL OR @p_fecha_hasta IS NULL
    BEGIN
        RAISERROR('partes.tarea.fechasRequeridas', 16, 1);
        RETURN;
    END

    IF @p_actor_tipo_funcional = N'cliente'
    BEGIN
        SET @p_cliente_id = @p_actor_cliente_id;
    END

    IF @p_cliente_id IS NULL OR @p_cliente_id <= 0
    BEGIN
        RAISERROR('partes.informe.clienteRequerido', 16, 1);
        RETURN;
    END

    IF @p_actor_tipo_funcional = N'asistente' AND (@p_actor_asistente_id IS NULL OR @p_actor_asistente_id <= 0)
    BEGIN
        RAISERROR('partes.tarea.forbidden', 16, 1);
        RETURN;
    END

    CREATE TABLE #mov (
        id INT NOT NULL,
        usuario_id INT NOT NULL,
        cliente_id INT NOT NULL,
        tipo_tarea_id INT NOT NULL,
        fecha DATE NOT NULL,
        duracion_minutos INT NOT NULL,
        sin_cargo BIT NOT NULL,
        presencial BIT NOT NULL,
        observacion NVARCHAR(MAX) NOT NULL,
        cerrado BIT NOT NULL,
        es_tarea BIT NOT NULL,
        usuario_code NVARCHAR(64) NOT NULL,
        usuario_nombre NVARCHAR(256) NOT NULL,
        cliente_code NVARCHAR(64) NOT NULL,
        cliente_nombre NVARCHAR(256) NOT NULL,
        erp_cliente NVARCHAR(128) NOT NULL,
        erp_articulo NVARCHAR(128) NOT NULL,
        tipo_tarea_code NVARCHAR(64) NOT NULL,
        tipo_tarea_descripcion NVARCHAR(256) NOT NULL,
        tipo_cliente_code NVARCHAR(64) NOT NULL,
        tipo_cliente_descripcion NVARCHAR(256) NOT NULL,
        delta_minutos INT NOT NULL
    );

    INSERT INTO #mov (
        id, usuario_id, cliente_id, tipo_tarea_id, fecha, duracion_minutos,
        sin_cargo, presencial, observacion, cerrado, es_tarea,
        usuario_code, usuario_nombre, cliente_code, cliente_nombre,
        erp_cliente, erp_articulo, tipo_tarea_code, tipo_tarea_descripcion,
        tipo_cliente_code, tipo_cliente_descripcion, delta_minutos
    )
    SELECT
        r.id,
        r.usuario_id,
        r.cliente_id,
        r.tipo_tarea_id,
        r.fecha,
        r.duracion_minutos,
        r.sin_cargo,
        r.presencial,
        r.observacion,
        r.cerrado,
        r.es_tarea,
        u.code,
        u.nombre,
        c.code,
        c.nombre,
        COALESCE(c.erp_cliente, N''),
        COALESCE(c.erp_articulo, N''),
        t.code,
        t.descripcion,
        COALESCE(tc.code, N''),
        COALESCE(tc.descripcion, N''),
        CASE WHEN r.es_tarea = 1 THEN r.duracion_minutos ELSE -r.duracion_minutos END
    FROM dbo.PQ_PARTES_REGISTRO_TAREA r WITH (NOLOCK)
    INNER JOIN dbo.PQ_PARTES_USUARIOS u WITH (NOLOCK) ON u.id = r.usuario_id
    INNER JOIN dbo.PQ_PARTES_CLIENTES c WITH (NOLOCK) ON c.id = r.cliente_id
    INNER JOIN dbo.PQ_PARTES_TIPOS_TAREA t WITH (NOLOCK) ON t.id = r.tipo_tarea_id
    LEFT JOIN dbo.PQ_PARTES_TIPOS_CLIENTE tc WITH (NOLOCK) ON tc.id = c.tipo_cliente_id
    WHERE r.cliente_id = @p_cliente_id
      AND (
          @p_actor_tipo_funcional = N'cliente'
          OR (@p_actor_es_supervisor = 1 AND (@p_usuario_id IS NULL OR r.usuario_id = @p_usuario_id))
          OR (@p_actor_es_supervisor = 0 AND r.usuario_id = @p_actor_asistente_id)
      )
      AND (@p_tipo_cliente_id IS NULL OR c.tipo_cliente_id = @p_tipo_cliente_id);

    DECLARE @saldo_inicial INT;
    SELECT @saldo_inicial = COALESCE(SUM(delta_minutos), 0)
    FROM #mov
    WHERE fecha < @p_fecha_desde;

    CREATE TABLE #out (
        sort_key INT NOT NULL,
        id INT NOT NULL,
        es_saldo_inicial BIT NOT NULL,
        fecha DATE NULL,
        usuario_id INT NULL,
        cliente_id INT NULL,
        tipo_tarea_id INT NULL,
        duracion_minutos INT NOT NULL,
        sin_cargo BIT NOT NULL,
        presencial BIT NOT NULL,
        observacion NVARCHAR(MAX) NOT NULL,
        cerrado BIT NOT NULL,
        es_tarea BIT NOT NULL,
        usuario_code NVARCHAR(64) NOT NULL,
        usuario_nombre NVARCHAR(256) NOT NULL,
        cliente_code NVARCHAR(64) NOT NULL,
        cliente_nombre NVARCHAR(256) NOT NULL,
        erp_cliente NVARCHAR(128) NOT NULL,
        erp_articulo NVARCHAR(128) NOT NULL,
        tipo_tarea_code NVARCHAR(64) NOT NULL,
        tipo_tarea_descripcion NVARCHAR(256) NOT NULL,
        tipo_cliente_code NVARCHAR(64) NOT NULL,
        tipo_cliente_descripcion NVARCHAR(256) NOT NULL,
        saldo INT NOT NULL
    );

    INSERT INTO #out (
        sort_key, id, es_saldo_inicial, fecha, usuario_id, cliente_id, tipo_tarea_id,
        duracion_minutos, sin_cargo, presencial, observacion, cerrado, es_tarea,
        usuario_code, usuario_nombre, cliente_code, cliente_nombre,
        erp_cliente, erp_articulo, tipo_tarea_code, tipo_tarea_descripcion,
        tipo_cliente_code, tipo_cliente_descripcion, saldo
    )
    VALUES (
        0, -1, 1, @p_fecha_desde, NULL, @p_cliente_id, NULL,
        @saldo_inicial, 0, 0, N'Saldo inicial', 0, 1,
        N'', N'', N'', N'',
        N'', N'', N'', N'',
        N'', N'',
        @saldo_inicial
    );

    ;WITH running AS (
        SELECT
            m.*,
            SUM(m.delta_minutos) OVER (
                PARTITION BY m.cliente_id
                ORDER BY m.fecha, m.id
                ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
            ) AS saldo_running
        FROM #mov m
    )
    INSERT INTO #out (
        sort_key, id, es_saldo_inicial, fecha, usuario_id, cliente_id, tipo_tarea_id,
        duracion_minutos, sin_cargo, presencial, observacion, cerrado, es_tarea,
        usuario_code, usuario_nombre, cliente_code, cliente_nombre,
        erp_cliente, erp_articulo, tipo_tarea_code, tipo_tarea_descripcion,
        tipo_cliente_code, tipo_cliente_descripcion, saldo
    )
    SELECT
        ROW_NUMBER() OVER (ORDER BY r.fecha, r.id),
        r.id, 0, r.fecha, r.usuario_id, r.cliente_id, r.tipo_tarea_id,
        r.duracion_minutos, r.sin_cargo, r.presencial, r.observacion, r.cerrado, r.es_tarea,
        r.usuario_code, r.usuario_nombre, r.cliente_code, r.cliente_nombre,
        r.erp_cliente, r.erp_articulo, r.tipo_tarea_code, r.tipo_tarea_descripcion,
        r.tipo_cliente_code, r.tipo_cliente_descripcion, r.saldo_running
    FROM running r
    WHERE r.fecha >= @p_fecha_desde AND r.fecha <= @p_fecha_hasta;

    DECLARE @items_json NVARCHAR(MAX);
    DECLARE @total INT;

    SELECT @items_json = (
        SELECT
            o.id,
            o.es_saldo_inicial,
            CONVERT(NVARCHAR(10), o.fecha, 23) AS fecha,
            o.usuario_id,
            o.cliente_id,
            o.tipo_tarea_id,
            o.duracion_minutos,
            o.sin_cargo,
            o.presencial,
            o.observacion,
            o.cerrado,
            o.es_tarea,
            o.usuario_code,
            o.usuario_nombre,
            o.cliente_code,
            o.cliente_nombre,
            o.erp_cliente,
            o.erp_articulo,
            o.tipo_tarea_code,
            o.tipo_tarea_descripcion,
            o.tipo_cliente_code,
            o.tipo_cliente_descripcion,
            o.saldo
        FROM #out o
        ORDER BY o.sort_key
        FOR JSON PATH
    );

    SELECT @total = COUNT(1) FROM #out;

    SELECT
        @saldo_inicial AS saldo_inicial,
        COALESCE(@items_json, N'[]') AS items_json,
        @total AS total;

    DROP TABLE #out;
    DROP TABLE #mov;
END
GO
