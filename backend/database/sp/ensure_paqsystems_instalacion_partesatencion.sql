-- PAQSYSTEMS: SP + filas DEMO/PAQ para Partes (resolver=sql, Opción B).
-- Ejecutar en BD PAQSYSTEMS (mismo RDS que las BD operativas).
-- Completar password de las filas (mismo usuario SQL que DB_* en Forge).

IF COL_LENGTH('dbo.EMPRESAS_CONEXION', 'fecha_vencimiento') IS NULL
BEGIN
    ALTER TABLE dbo.EMPRESAS_CONEXION ADD fecha_vencimiento date NULL;
END
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_empresas_conexion_get
    @proyecto NVARCHAR(100),
    @cliente NVARCHAR(100)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        ec.proyecto,
        ec.cliente,
        ec.nombre,
        ec.host,
        ec.port,
        ec.database_name,
        ec.username,
        ec.password,
        ec.activo,
        ec.fecha_vencimiento,
        ec.dictionary_database,
        ec.agent_id,
        ec.client_id
    FROM dbo.EMPRESAS_CONEXION AS ec WITH (NOLOCK)
    WHERE ec.proyecto = @proyecto
      AND ec.cliente = @cliente;
END;
GO

DECLARE @host NVARCHAR(255) = N'database-1.cf2tcvmvcot6.us-east-2.rds.amazonaws.com';
DECLARE @user NVARCHAR(100) = N'admin';
DECLARE @pass NVARCHAR(MAX) = N''; -- completar: misma clave que DB_PASSWORD de Forge

MERGE dbo.EMPRESAS_CONEXION AS t
USING (VALUES
    (N'partesatencion', N'DEMO', N'Paqsystems DEMO', N'paqsystems_partesatencion_demo'),
    (N'partesatencion', N'PAQ', N'PaqSystems', N'paqsystems_partesatencion_paq')
) AS s (proyecto, cliente, nombre, database_name)
ON t.proyecto = s.proyecto AND t.cliente = s.cliente
WHEN MATCHED THEN
    UPDATE SET
        t.nombre = s.nombre,
        t.cliente = s.cliente,
        t.host = @host,
        t.port = 1433,
        t.database_name = s.database_name,
        t.dictionary_database = s.database_name,
        t.username = @user,
        t.password = CASE WHEN @pass <> N'' THEN @pass ELSE t.password END,
        t.activo = 1,
        t.updated_at = SYSUTCDATETIME()
WHEN NOT MATCHED THEN
    INSERT (
        proyecto, cliente, nombre, host, port, database_name, username, password,
        activo, dictionary_database, created_at, updated_at
    )
    VALUES (
        s.proyecto, s.cliente, s.nombre, @host, 1433, s.database_name, @user, @pass,
        1, s.database_name, SYSUTCDATETIME(), SYSUTCDATETIME()
    );
GO
