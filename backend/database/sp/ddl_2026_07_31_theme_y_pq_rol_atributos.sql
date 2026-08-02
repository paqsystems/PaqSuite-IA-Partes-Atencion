-- DDL idempotente GEN-06 (roles-atributos + empresas.theme) — alternativa a `php artisan migrate`
-- para aplicar directamente contra SQL Server con sqlcmd (ej. DEMO).
-- Equivalente a las migraciones Laravel:
--   2026_07_31_100000_add_theme_to_pq_empresa_table.php
--   2026_07_31_100001_create_pq_rol_atributos_table.php
-- Ejecutar ANTES de (re)desplegar pq_sp_admin_seguridad_core.sql y pq_sp_admin_roles_atributos.sql.

IF NOT EXISTS (
    SELECT 1 FROM sys.columns
    WHERE object_id = OBJECT_ID(N'dbo.pq_empresa') AND name = 'theme'
)
BEGIN
    ALTER TABLE dbo.pq_empresa ADD theme NVARCHAR(64) NULL CONSTRAINT DF_pq_empresa_theme DEFAULT ('generic.light');
END;
GO

UPDATE dbo.pq_empresa SET theme = 'generic.light' WHERE theme IS NULL;
GO

IF OBJECT_ID(N'dbo.pq_rol_atributos', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.pq_rol_atributos (
        id BIGINT IDENTITY(1,1) NOT NULL,
        rol_id BIGINT NOT NULL,
        menu_id BIGINT NOT NULL,
        permiso_alta BIT NOT NULL CONSTRAINT DF_pq_rol_atributos_alta DEFAULT (0),
        permiso_baja BIT NOT NULL CONSTRAINT DF_pq_rol_atributos_baja DEFAULT (0),
        permiso_modi BIT NOT NULL CONSTRAINT DF_pq_rol_atributos_modi DEFAULT (0),
        permiso_repo BIT NOT NULL CONSTRAINT DF_pq_rol_atributos_repo DEFAULT (0),
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        CONSTRAINT PK_pq_rol_atributos PRIMARY KEY CLUSTERED (id ASC),
        CONSTRAINT FK_pq_rol_atributos_rol FOREIGN KEY (rol_id) REFERENCES dbo.pq_roles (id) ON DELETE CASCADE,
        CONSTRAINT FK_pq_rol_atributos_menu FOREIGN KEY (menu_id) REFERENCES dbo.pq_menus (id),
        CONSTRAINT UQ_pq_rol_atributos_rol_menu UNIQUE (rol_id, menu_id)
    );
END;
GO
