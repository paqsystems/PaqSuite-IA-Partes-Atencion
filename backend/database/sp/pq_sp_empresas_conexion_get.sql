-- Lookup instalación en PAQSYSTEMS.EMPRESAS_CONEXION (MUST SP; GEN-18).
-- Parámetros: @proyecto + @cliente (cliente ya normalizado UPPERCASE en app).
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
