CREATE OR ALTER PROCEDURE dbo.pq_sp_partes_integracion_maestros_clientes
    @itemsJson NVARCHAR(MAX),
    @fechaProceso DATE,
    @origen NVARCHAR(100) = NULL,
    @dryRun BIT = 0
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @items TABLE (
        indice INT NOT NULL,
        code NVARCHAR(50) COLLATE Latin1_General_100_BIN2 NOT NULL,
        nombre NVARCHAR(255) NULL,
        tipoClienteCode NVARCHAR(50) COLLATE Latin1_General_100_BIN2 NULL,
        email NVARCHAR(255) NULL,
        erpCliente NVARCHAR(15) NULL,
        erpArticulo NVARCHAR(15) NULL
    );
    DECLARE @resultado TABLE (
        tipoRegistro VARCHAR(10) NOT NULL,
        indice INT NULL,
        clave NVARCHAR(100) NULL,
        accion NVARCHAR(30) NULL,
        codigo NVARCHAR(120) NULL
    );

    INSERT INTO @items
    SELECT CONVERT(INT, j.[key]), p.code, p.nombre, p.tipoClienteCode, p.email, p.erpCliente, p.erpArticulo
    FROM OPENJSON(@itemsJson) j
    CROSS APPLY OPENJSON(j.value)
    WITH (
        code NVARCHAR(50) '$.code',
        nombre NVARCHAR(255) '$.nombre',
        tipoClienteCode NVARCHAR(50) '$.tipoCliente.code',
        email NVARCHAR(255) '$.email',
        erpCliente NVARCHAR(15) '$.erpCliente',
        erpArticulo NVARCHAR(15) '$.erpArticulo'
    ) p;

    INSERT INTO @resultado
    SELECT 'ITEM', i.indice, i.code, 'rechazado',
        CASE
            WHEN i.code = N'' OR i.nombre IS NULL OR LTRIM(RTRIM(i.nombre)) = N''
                OR i.tipoClienteCode IS NULL OR LTRIM(RTRIM(i.tipoClienteCode)) = N''
                THEN N'integracion.maestro.itemInvalido'
            WHEN LEN(i.erpCliente) > 15 OR LEN(i.erpArticulo) > 15
                THEN N'integracion.maestro.itemInvalido'
            ELSE N'integracion.maestro.duplicado'
        END
    FROM @items i
    WHERE i.code = N''
       OR i.nombre IS NULL
       OR LTRIM(RTRIM(i.nombre)) = N''
       OR i.tipoClienteCode IS NULL
       OR LTRIM(RTRIM(i.tipoClienteCode)) = N''
       OR LEN(i.erpCliente) > 15
       OR LEN(i.erpArticulo) > 15
       OR EXISTS (
           SELECT 1 FROM @items duplicate
           WHERE duplicate.code = i.code AND duplicate.indice <> i.indice
       );

    DECLARE @indice INT;
    DECLARE @code NVARCHAR(50);
    DECLARE @nombre NVARCHAR(255);
    DECLARE @tipoClienteCode NVARCHAR(50);
    DECLARE @email NVARCHAR(255);
    DECLARE @erpCliente NVARCHAR(15);
    DECLARE @erpArticulo NVARCHAR(15);
    DECLARE item_cursor CURSOR LOCAL FAST_FORWARD FOR
        SELECT i.indice, i.code, i.nombre, i.tipoClienteCode, NULLIF(i.email, N''), NULLIF(i.erpCliente, N''), NULLIF(i.erpArticulo, N'')
        FROM @items i
        WHERE NOT EXISTS (
            SELECT 1 FROM @resultado r WHERE r.tipoRegistro = 'ITEM' AND r.indice = i.indice
        );

    OPEN item_cursor;
    FETCH NEXT FROM item_cursor INTO @indice, @code, @nombre, @tipoClienteCode, @email, @erpCliente, @erpArticulo;
    WHILE @@FETCH_STATUS = 0
    BEGIN
        DECLARE @tipoClienteId BIGINT;
        SELECT @tipoClienteId = id
        FROM dbo.PQ_PARTES_TIPOS_CLIENTE WITH (NOLOCK)
        WHERE code COLLATE Latin1_General_100_BIN2 = LTRIM(RTRIM(@tipoClienteCode))
          AND activo = 1 AND inhabilitado = 0;

        IF @tipoClienteId IS NULL
        BEGIN
            INSERT INTO @resultado VALUES ('ITEM', @indice, @code, 'rechazado', 'integracion.maestro.referenciaInvalida');
        END
        ELSE
        BEGIN
            DECLARE @id BIGINT;
            SELECT @id = id FROM dbo.PQ_PARTES_CLIENTES WITH (NOLOCK)
            WHERE code COLLATE Latin1_General_100_BIN2 = @code;

            IF @id IS NULL
            BEGIN
                IF @dryRun = 0
                    INSERT INTO dbo.PQ_PARTES_CLIENTES
                        (code, nombre, tipo_cliente_id, email, erp_cliente, erp_articulo, activo, inhabilitado, created_at, updated_at)
                    VALUES (@code, LTRIM(RTRIM(@nombre)), @tipoClienteId, @email, @erpCliente, @erpArticulo, 1, 0, SYSUTCDATETIME(), SYSUTCDATETIME());
                INSERT INTO @resultado VALUES ('ITEM', @indice, @code, 'alta', NULL);
            END
            ELSE IF EXISTS (
                SELECT 1 FROM dbo.PQ_PARTES_CLIENTES WITH (NOLOCK)
                WHERE id = @id AND nombre = LTRIM(RTRIM(@nombre)) AND tipo_cliente_id = @tipoClienteId
                  AND ISNULL(email, N'') = ISNULL(@email, N'')
                  AND ISNULL(erp_cliente, N'') = ISNULL(@erpCliente, N'')
                  AND ISNULL(erp_articulo, N'') = ISNULL(@erpArticulo, N'')
                  AND activo = 1 AND inhabilitado = 0
            )
            BEGIN
                INSERT INTO @resultado VALUES ('ITEM', @indice, @code, 'sinCambios', NULL);
            END
            ELSE
            BEGIN
                IF @dryRun = 0
                    UPDATE dbo.PQ_PARTES_CLIENTES
                    SET nombre = LTRIM(RTRIM(@nombre)), tipo_cliente_id = @tipoClienteId,
                        email = @email, erp_cliente = @erpCliente, erp_articulo = @erpArticulo,
                        activo = 1, inhabilitado = 0, updated_at = SYSUTCDATETIME()
                    WHERE id = @id;
                INSERT INTO @resultado VALUES ('ITEM', @indice, @code, 'modificacion', NULL);
            END;
        END;

        FETCH NEXT FROM item_cursor INTO @indice, @code, @nombre, @tipoClienteCode, @email, @erpCliente, @erpArticulo;
    END;
    CLOSE item_cursor;
    DEALLOCATE item_cursor;

    DECLARE absent_cursor CURSOR LOCAL FAST_FORWARD FOR
        SELECT c.code FROM dbo.PQ_PARTES_CLIENTES c WITH (NOLOCK)
        WHERE c.activo = 1 AND c.inhabilitado = 0
          AND NOT EXISTS (
              SELECT 1 FROM @items i WHERE i.code = c.code COLLATE Latin1_General_100_BIN2
          );
    OPEN absent_cursor;
    FETCH NEXT FROM absent_cursor INTO @code;
    WHILE @@FETCH_STATUS = 0
    BEGIN
        IF @dryRun = 0
            UPDATE dbo.PQ_PARTES_CLIENTES
            SET activo = 0, inhabilitado = 1, updated_at = SYSUTCDATETIME()
            WHERE code COLLATE Latin1_General_100_BIN2 = @code;
        INSERT INTO @resultado VALUES ('ITEM', NULL, @code, 'inhabilitado', NULL);
        FETCH NEXT FROM absent_cursor INTO @code;
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
