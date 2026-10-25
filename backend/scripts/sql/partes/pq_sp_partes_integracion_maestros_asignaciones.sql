CREATE OR ALTER PROCEDURE dbo.pq_sp_partes_integracion_maestros_asignaciones
    @itemsJson NVARCHAR(MAX),
    @fechaProceso DATE,
    @origen NVARCHAR(100) = NULL,
    @dryRun BIT = 0
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @items TABLE (
        indice INT NOT NULL,
        clienteCode NVARCHAR(50) COLLATE Latin1_General_100_BIN2 NOT NULL,
        tipoTareaCode NVARCHAR(50) COLLATE Latin1_General_100_BIN2 NOT NULL
    );
    DECLARE @resultado TABLE (
        tipoRegistro VARCHAR(10) NOT NULL,
        indice INT NULL,
        clave NVARCHAR(100) NULL,
        accion NVARCHAR(30) NULL,
        codigo NVARCHAR(120) NULL
    );

    INSERT INTO @items
    SELECT CONVERT(INT, c.[key]), p.clienteCode, t.tipoTareaCode
    FROM OPENJSON(@itemsJson) c
    CROSS APPLY OPENJSON(c.value)
    WITH (
        clienteCode NVARCHAR(50) '$.code',
        tiposTarea NVARCHAR(MAX) '$.tiposTarea' AS JSON
    ) p
    CROSS APPLY OPENJSON(p.tiposTarea)
    WITH (tipoTareaCode NVARCHAR(50) '$.code') t;

    INSERT INTO @resultado
    SELECT 'ITEM', i.indice, i.clienteCode, 'rechazado',
        CASE WHEN i.clienteCode = N'' OR i.tipoTareaCode = N''
             THEN N'integracion.maestro.itemInvalido'
             ELSE N'integracion.maestro.duplicado' END
    FROM @items i
    WHERE i.clienteCode = N''
       OR i.tipoTareaCode = N''
       OR EXISTS (
           SELECT 1 FROM @items duplicate
           WHERE duplicate.clienteCode = i.clienteCode
             AND duplicate.tipoTareaCode = i.tipoTareaCode
             AND duplicate.indice <> i.indice
       );

    DECLARE @indice INT;
    DECLARE @clienteCode NVARCHAR(50);
    DECLARE @tipoTareaCode NVARCHAR(50);
    DECLARE pair_cursor CURSOR LOCAL FAST_FORWARD FOR
        SELECT indice, clienteCode, tipoTareaCode
        FROM @items
        WHERE clienteCode <> N'' AND tipoTareaCode <> N'';
    OPEN pair_cursor;
    FETCH NEXT FROM pair_cursor INTO @indice, @clienteCode, @tipoTareaCode;
    WHILE @@FETCH_STATUS = 0
    BEGIN
        DECLARE @clienteId BIGINT;
        DECLARE @tipoTareaId BIGINT;
        SELECT @clienteId = id FROM dbo.PQ_PARTES_CLIENTES WITH (NOLOCK)
        WHERE code COLLATE Latin1_General_100_BIN2 = @clienteCode
          AND activo = 1 AND inhabilitado = 0;
        SELECT @tipoTareaId = id FROM dbo.PQ_PARTES_TIPOS_TAREA WITH (NOLOCK)
        WHERE code COLLATE Latin1_General_100_BIN2 = @tipoTareaCode
          AND activo = 1 AND inhabilitado = 0 AND is_generico = 0;

        IF @clienteId IS NULL OR @tipoTareaId IS NULL
        BEGIN
            INSERT INTO @resultado VALUES ('ITEM', @indice, @clienteCode + N'|' + @tipoTareaCode, 'rechazado', 'integracion.maestro.referenciaInvalida');
        END
        ELSE IF EXISTS (
            SELECT 1 FROM dbo.PQ_PARTES_CLIENTE_TIPO_TAREA WITH (NOLOCK)
            WHERE cliente_id = @clienteId AND tipo_tarea_id = @tipoTareaId
              AND activo = 1 AND inhabilitado = 0
        )
        BEGIN
            INSERT INTO @resultado VALUES ('ITEM', @indice, @clienteCode + N'|' + @tipoTareaCode, 'sinCambios', NULL);
        END
        ELSE
        BEGIN
            IF @dryRun = 0
            BEGIN
                IF EXISTS (SELECT 1 FROM dbo.PQ_PARTES_CLIENTE_TIPO_TAREA WHERE cliente_id = @clienteId AND tipo_tarea_id = @tipoTareaId)
                    UPDATE dbo.PQ_PARTES_CLIENTE_TIPO_TAREA
                    SET activo = 1, inhabilitado = 0, updated_at = SYSUTCDATETIME()
                    WHERE cliente_id = @clienteId AND tipo_tarea_id = @tipoTareaId;
                ELSE
                    INSERT INTO dbo.PQ_PARTES_CLIENTE_TIPO_TAREA
                        (cliente_id, tipo_tarea_id, activo, inhabilitado, created_at, updated_at)
                    VALUES (@clienteId, @tipoTareaId, 1, 0, SYSUTCDATETIME(), SYSUTCDATETIME());
            END;
            INSERT INTO @resultado VALUES ('ITEM', @indice, @clienteCode + N'|' + @tipoTareaCode, 'alta', NULL);
        END;

        FETCH NEXT FROM pair_cursor INTO @indice, @clienteCode, @tipoTareaCode;
    END;
    CLOSE pair_cursor;
    DEALLOCATE pair_cursor;

    DECLARE absent_cursor CURSOR LOCAL FAST_FORWARD FOR
        SELECT a.id, c.code, t.code
        FROM dbo.PQ_PARTES_CLIENTE_TIPO_TAREA a WITH (NOLOCK)
        INNER JOIN dbo.PQ_PARTES_CLIENTES c WITH (NOLOCK) ON c.id = a.cliente_id
        INNER JOIN dbo.PQ_PARTES_TIPOS_TAREA t WITH (NOLOCK) ON t.id = a.tipo_tarea_id
        WHERE a.activo = 1 AND a.inhabilitado = 0
          AND NOT EXISTS (
              SELECT 1 FROM @items i
              WHERE i.clienteCode = c.code AND i.tipoTareaCode = t.code
          );
    DECLARE @assignmentId BIGINT;
    OPEN absent_cursor;
    FETCH NEXT FROM absent_cursor INTO @assignmentId, @clienteCode, @tipoTareaCode;
    WHILE @@FETCH_STATUS = 0
    BEGIN
        IF @dryRun = 0
            UPDATE dbo.PQ_PARTES_CLIENTE_TIPO_TAREA
            SET activo = 0, inhabilitado = 1, updated_at = SYSUTCDATETIME()
            WHERE id = @assignmentId;
        INSERT INTO @resultado VALUES ('ITEM', NULL, @clienteCode + N'|' + @tipoTareaCode, 'inhabilitado', NULL);
        FETCH NEXT FROM absent_cursor INTO @assignmentId, @clienteCode, @tipoTareaCode;
    END;
    CLOSE absent_cursor;
    DEALLOCATE absent_cursor;

    SELECT 'RESUMEN' AS tipoRegistro, CAST(NULL AS INT) AS indice,
        CAST(NULL AS NVARCHAR(100)) AS clave, CAST(NULL AS NVARCHAR(30)) AS accion,
        CAST(NULL AS NVARCHAR(120)) AS codigo,
        (SELECT COUNT(*) FROM @items) AS recibidos,
        (SELECT COUNT(*) FROM @resultado WHERE accion IN ('alta', 'modificacion', 'inhabilitado')) AS aplicados,
        (SELECT COUNT(*) FROM @resultado WHERE accion = 'sinCambios') AS omitidos,
        (SELECT COUNT(*) FROM @resultado WHERE accion = 'rechazado') AS rechazados,
        CAST(CASE WHEN (SELECT COUNT(*) FROM @resultado WHERE codigo IS NOT NULL) > 50 THEN 1 ELSE 0 END AS BIT) AS erroresTruncados
    FROM (VALUES (1)) AS summary(dummy);

    SELECT tipoRegistro, indice, clave, accion, codigo,
        CAST(NULL AS INT) AS recibidos, CAST(NULL AS INT) AS aplicados,
        CAST(NULL AS INT) AS omitidos, CAST(NULL AS INT) AS rechazados,
        CAST(NULL AS BIT) AS erroresTruncados
    FROM @resultado
    ORDER BY CASE WHEN indice IS NULL THEN 1 ELSE 0 END, indice, clave;
END;
