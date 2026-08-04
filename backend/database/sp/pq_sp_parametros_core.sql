-- GEN-10: parámetros diccionario (SP MUST en hosts SQL Server).
-- Smoke CI usa adapter Eloquent; este script es el contrato canónico.

CREATE OR ALTER PROCEDURE dbo.pq_sp_parametros_list
    @programa NVARCHAR(100)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        programa,
        clave,
        tipo_valor,
        valor_string,
        valor_texto,
        valor_int,
        valor_decimal,
        valor_bool,
        valor_fecha,
        precision_fecha,
        caption,
        tooltip,
        meta_json
    FROM pq_parametros_gral WITH (NOLOCK)
    WHERE programa = @programa
    ORDER BY caption ASC, clave ASC;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_parametros_get
    @programa NVARCHAR(100),
    @clave NVARCHAR(150)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        programa,
        clave,
        tipo_valor,
        valor_string,
        valor_texto,
        valor_int,
        valor_decimal,
        valor_bool,
        valor_fecha,
        precision_fecha,
        caption,
        tooltip,
        meta_json
    FROM pq_parametros_gral WITH (NOLOCK)
    WHERE programa = @programa AND clave = @clave;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_parametros_update
    @programa NVARCHAR(100),
    @clave NVARCHAR(150),
    @valor_string NVARCHAR(500) = NULL,
    @valor_texto NVARCHAR(MAX) = NULL,
    @valor_int INT = NULL,
    @valor_decimal DECIMAL(18, 6) = NULL,
    @valor_bool BIT = NULL,
    @valor_fecha DATETIME2 = NULL
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE pq_parametros_gral
    SET
        valor_string = CASE WHEN tipo_valor = 'S' THEN @valor_string ELSE valor_string END,
        valor_texto = CASE WHEN tipo_valor = 'T' THEN @valor_texto ELSE valor_texto END,
        valor_int = CASE WHEN tipo_valor = 'I' THEN @valor_int ELSE valor_int END,
        valor_decimal = CASE WHEN tipo_valor = 'D' THEN @valor_decimal ELSE valor_decimal END,
        valor_bool = CASE WHEN tipo_valor = 'B' THEN @valor_bool ELSE valor_bool END,
        valor_fecha = CASE WHEN tipo_valor = 'F' THEN @valor_fecha ELSE valor_fecha END,
        updated_at = SYSUTCDATETIME()
    WHERE programa = @programa AND clave = @clave;

    SELECT @@ROWCOUNT AS updated_rows;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_parametros_insert_if_absent
    @programa NVARCHAR(100),
    @clave NVARCHAR(150),
    @tipo_valor CHAR(1),
    @valor_string NVARCHAR(500) = NULL,
    @valor_texto NVARCHAR(MAX) = NULL,
    @valor_int INT = NULL,
    @valor_decimal DECIMAL(18, 6) = NULL,
    @valor_bool BIT = NULL,
    @valor_fecha DATETIME2 = NULL,
    @precision_fecha NVARCHAR(20) = NULL,
    @caption NVARCHAR(200) = NULL,
    @tooltip NVARCHAR(500) = NULL,
    @meta_json NVARCHAR(MAX) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (SELECT 1 FROM pq_parametros_gral WITH (NOLOCK) WHERE programa = @programa AND clave = @clave)
    BEGIN
        UPDATE pq_parametros_gral
        SET
            caption = COALESCE(@caption, caption),
            tooltip = COALESCE(@tooltip, tooltip),
            meta_json = COALESCE(@meta_json, meta_json),
            updated_at = SYSUTCDATETIME()
        WHERE programa = @programa AND clave = @clave;
        RETURN;
    END

    INSERT INTO pq_parametros_gral (
        programa, clave, tipo_valor,
        valor_string, valor_texto, valor_int, valor_decimal, valor_bool, valor_fecha,
        precision_fecha, caption, tooltip, meta_json, created_at, updated_at
    )
    VALUES (
        @programa, @clave, @tipo_valor,
        @valor_string, @valor_texto, @valor_int, @valor_decimal, @valor_bool, @valor_fecha,
        @precision_fecha, @caption, @tooltip, @meta_json, SYSUTCDATETIME(), SYSUTCDATETIME()
    );
END;
GO
