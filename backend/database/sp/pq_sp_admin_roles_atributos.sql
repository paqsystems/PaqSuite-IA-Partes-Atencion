-- GEN-06-roles-atributos: roles y atributos por opción de menú — contrato SP (SQL Server).
-- Host: PaqSuite-IA-Partes-Atencion. Tabla `pq_rol_atributos`:
--   id, rol_id FK pq_roles, menu_id FK pq_menus,
--   permiso_alta/baja/modi/repo (bit), timestamps, UNIQUE(rol_id, menu_id).
-- PUT `/admin/roles/{id}/atributos` = reemplazo del set completo (D1-06-10):
-- el host llama `_delete_all` y luego `_insert` por cada fila, en una transacción.
-- Ver docs/06-modelo-datos/sp/README.md (Framework) y regla BASE 74/75 (SP + NOLOCK).

-- =====================================================================
-- ROLES — detalle (usado por el gate AccesoTotal / 404 de esta pantalla)
-- =====================================================================

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_roles_atributos_get
    @rol_id INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        a.menu_id AS menuId,
        a.permiso_alta AS permisoAlta,
        a.permiso_baja AS permisoBaja,
        a.permiso_modi AS permisoModi,
        a.permiso_repo AS permisoRepo
    FROM dbo.pq_rol_atributos a WITH (NOLOCK)
    WHERE a.rol_id = @rol_id
    ORDER BY a.menu_id;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_roles_atributos_delete_all
    @rol_id INT
AS
BEGIN
    SET NOCOUNT ON;

    DELETE FROM dbo.pq_rol_atributos WHERE rol_id = @rol_id;

    SELECT @@ROWCOUNT AS updated_rows;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_roles_atributos_insert
    @rol_id INT,
    @menu_id BIGINT,
    @permiso_alta BIT = 0,
    @permiso_baja BIT = 0,
    @permiso_modi BIT = 0,
    @permiso_repo BIT = 0
AS
BEGIN
    SET NOCOUNT ON;

    INSERT INTO dbo.pq_rol_atributos
        (rol_id, menu_id, permiso_alta, permiso_baja, permiso_modi, permiso_repo, created_at, updated_at)
    VALUES
        (@rol_id, @menu_id, @permiso_alta, @permiso_baja, @permiso_modi, @permiso_repo, SYSUTCDATETIME(), SYSUTCDATETIME());
END;
GO

-- =====================================================================
-- MENÚS — árbol habilitado (embebido en la misma respuesta GET, D1-06-10)
-- =====================================================================

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_menus_arbol_enabled
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        m.id AS menuId,
        m.parent_id AS padreId,
        m.titulo,
        CASE WHEN m.process_type = 'A' THEN CAST(1 AS BIT) ELSE CAST(0 AS BIT) END AS esProceso
    FROM dbo.pq_menus m WITH (NOLOCK)
    WHERE m.enabled = 1 AND m.activo = 1
    ORDER BY m.orden, m.id;
END;
GO
