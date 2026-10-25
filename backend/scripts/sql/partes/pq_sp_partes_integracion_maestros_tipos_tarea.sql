CREATE OR ALTER PROCEDURE dbo.pq_sp_partes_integracion_maestros_tipos_tarea
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
        descripcion NVARCHAR(255) NULL,
        isGenerico BIT NULL,
        isDefault BIT NULL
    );
    DECLARE @resultado TABLE (
        tipoRegistro VARCHAR(10) NOT NULL,
        indice INT NULL,
        clave NVARCHAR(100) NULL,
        accion NVARCHAR(30) NULL,
        codigo NVARCHAR(120) NULL
    );

    INSERT INTO @items (indice, code, descripcion, isGenerico, isDefault)
    SELECT CONVERT(INT, j.[key]), p.code, p.descripcion, p.isGenerico, p.isDefault
    FROM OPENJSON(@itemsJson) j
    CROSS APPLY OPENJSON(j.value)
    WITH (
        code NVARCHAR(50) '$.code',
        descripcion NVARCHAR(255) '$.descripcion',
        isGenerico BIT '$.isGenerico',
        isDefault BIT '$.isDefault'
    ) p;

    INSERT INTO @resultado
    SELECT 'ITEM', i.indice, i.code, 'rechazado',
           CASE WHEN i.code = N'' OR i.descripcion IS NULL OR LTRIM(RTRIM(i.descripcion)) = N''
                THEN N'integracion.maestro.itemInvalido'
                ELSE N'integracion.maestro.duplicado' END
    FROM @items i
    WHERE i.code = N''
       OR i.descripcion IS NULL
       OR LTRIM(RTRIM(i.descripcion)) = N''
       OR EXISTS (
           SELECT 1 FROM @items duplicate
           WHERE duplicate.code = i.code AND duplicate.indice <> i.indice
       );

    DECLARE @lastDefault INT = (
        SELECT MAX(indice) FROM @items WHERE isDefault = 1
    );
    DECLARE @indice INT;
    DECLARE @code NVARCHAR(50);
    DECLARE @descripcion NVARCHAR(255);
    DECLARE @isGenerico BIT;
    DECLARE @isDefault BIT;
    DECLARE item_cursor CURSOR LOCAL FAST_FORWARD FOR
        SELECT indice, code, descripcion,
               CASE WHEN indice = @lastDefault OR isDefault = 1 THEN 1 ELSE ISNULL(isGenerico, 1) END,
               CASE WHEN indice = @lastDefault THEN 1 ELSE ISNULL(isDefault, 0) END
        FROM @items
        WHERE NOT EXISTS (
            SELECT 1 FROM @resultado r
            WHERE r.tipoRegistro = 'ITEM' AND r.indice = @items.indice
        );

    OPEN item_cursor;
    FETCH NEXT FROM item_cursor INTO @indice, @code, @descripcion, @isGenerico, @isDefault;
    WHILE @@FETCH_STATUS = 0
    BEGIN
        DECLARE @id BIGINT = NULL;
        SELECT @id = id FROM dbo.PQ_PARTES_TIPOS_TAREA WITH (NOLOCK)
        WHERE code COLLATE Latin1_General_100_BIN2 = @code;

        IF @id IS NULL
        BEGIN
            IF @dryRun = 0
                INSERT INTO dbo.PQ_PARTES_TIPOS_TAREA
                    (code, descripcion, is_generico, is_default, activo, inhabilitado, created_at, updated_at)
                VALUES (@code, LTRIM(RTRIM(@descripcion)), @isGenerico, @isDefault, 1, 0, SYSUTCDATETIME(), SYSUTCDATETIME());
            INSERT INTO @resultado VALUES ('ITEM', @indice, @code, 'alta', NULL);
        END
        ELSE IF EXISTS (
            SELECT 1 FROM dbo.PQ_PARTES_TIPOS_TAREA WITH (NOLOCK)
            WHERE id = @id
              AND descripcion = LTRIM(RTRIM(@descripcion))
              AND is_generico = @isGenerico
              AND is_default = @isDefault
              AND activo = 1 AND inhabilitado = 0
        )
        BEGIN
            INSERT INTO @resultado VALUES ('ITEM', @indice, @code, 'sinCambios', NULL);
        END
        ELSE
        BEGIN
            IF @dryRun = 0
                UPDATE dbo.PQ_PARTES_TIPOS_TAREA
                SET descripcion = LTRIM(RTRIM(@descripcion)),
                    is_generico = @isGenerico,
                    is_default = @isDefault,
                    activo = 1,
                    inhabilitado = 0,
                    updated_at = SYSUTCDATETIME()
                WHERE id = @id;
            INSERT INTO @resultado VALUES ('ITEM', @indice, @code, 'modificacion', NULL);
        END;

        IF @isDefault = 1 AND @dryRun = 0
            UPDATE dbo.PQ_PARTES_TIPOS_TAREA
            SET is_default = 0, updated_at = SYSUTCDATETIME()
            WHERE id <> ISNULL(@id, -1);

        FETCH NEXT FROM item_cursor INTO @indice, @code, @descripcion, @isGenerico, @isDefault;
    END;
    CLOSE item_cursor;
    DEALLOCATE item_cursor;

    DECLARE absent_cursor CURSOR LOCAL FAST_FORWARD FOR
        SELECT code
        FROM dbo.PQ_PARTES_TIPOS_TAREA WITH (NOLOCK)
        WHERE activo = 1 AND inhabilitado = 0
          AND is_default = 0
          AND NOT EXISTS (SELECT 1 FROM @items i WHERE i.code = code);
    OPEN absent_cursor;
    FETCH NEXT FROM absent_cursor INTO @code;
    WHILE @@FETCH_STATUS = 0
    BEGIN
        IF @dryRun = 0
            UPDATE dbo.PQ_PARTES_TIPOS_TAREA
            SET activo = 0, inhabilitado = 1, updated_at = SYSUTCDATETIME()
            WHERE code COLLATE Latin1_General_100_BIN2 = @code;
        INSERT INTO @resultado VALUES ('ITEM', NULL, @code, 'inhabilitado', NULL);
        FETCH NEXT FROM absent_cursor INTO @code;
    END;
    CLOSE absent_cursor;
    DEALLOCATE absent_cursor;

    SELECT
        'RESUMEN' AS tipoRegistro,
        CAST(NULL AS INT) AS indice,
        CAST(NULL AS NVARCHAR(100)) AS clave,
        CAST(NULL AS NVARCHAR(30)) AS accion,
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
