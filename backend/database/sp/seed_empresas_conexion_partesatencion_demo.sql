-- Seed / upsert fila DEMO para Partes-Atención (opción A: destino = BD operativa actual).
-- Ejecutar en PAQSYSTEMS tras CREATE TABLE + SP.

IF NOT EXISTS (
    SELECT 1
    FROM dbo.EMPRESAS_CONEXION WITH (NOLOCK)
    WHERE proyecto = N'partesatencion' AND cliente = N'DEMO'
)
BEGIN
    INSERT INTO dbo.EMPRESAS_CONEXION (
        proyecto, cliente, nombre, host, port, database_name, username, password,
        activo, fecha_vencimiento, dictionary_database, agent_id, client_id,
        created_at, updated_at
    )
    VALUES (
        N'partesatencion',
        N'DEMO',
        N'Partes Demo',
        N'192.168.41.2',
        1433,
        N'PAQSYSTEMS_PARTESATENCION_DEMO',
        N'Axoft',
        N'', -- completar password ops; v1 en claro
        1,
        NULL,
        N'PAQSYSTEMS_PARTESATENCION_DEMO',
        NULL,
        NULL,
        SYSUTCDATETIME(),
        SYSUTCDATETIME()
    );
END
ELSE
BEGIN
    UPDATE dbo.EMPRESAS_CONEXION
    SET
        nombre = N'Partes Demo',
        host = N'192.168.41.2',
        port = 1433,
        database_name = N'PAQSYSTEMS_PARTESATENCION_DEMO',
        username = N'Axoft',
        dictionary_database = N'PAQSYSTEMS_PARTESATENCION_DEMO',
        activo = 1,
        updated_at = SYSUTCDATETIME()
    WHERE proyecto = N'partesatencion' AND cliente = N'DEMO';
END
GO
