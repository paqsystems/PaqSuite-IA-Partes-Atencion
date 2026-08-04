-- GEN-06: alta manual del ítem de menú "Empresas" bajo Seguridad (id 50000) en DEMO,
-- para instalaciones donde el seeder `PqMenuSeeder` no se vuelve a correr.
-- Idempotente: no inserta si ya existe.

IF NOT EXISTS (SELECT 1 FROM dbo.pq_menus WHERE id = 50400)
BEGIN
    INSERT INTO dbo.pq_menus (
        id, parent_id, codigo, titulo, ruta, orden, procedimiento, process_type,
        activo, enabled, icon_name, created_at, updated_at
    )
    VALUES (
        50400, 50000, 'admin_empresas', 'Empresas', '/admin/empresas', 50400, 'admin_empresas', 'A',
        1, 1, NULL, SYSUTCDATETIME(), SYSUTCDATETIME()
    );
END;
GO
