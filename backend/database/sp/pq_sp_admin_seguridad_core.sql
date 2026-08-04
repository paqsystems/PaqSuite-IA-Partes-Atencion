-- GEN-06: ABM Seguridad (usuarios / empresas / roles / permisos) — contrato SP (SQL Server).
-- Host: PaqSuite-IA-Partes-Atencion. MONO: empresas sin alta/baja (solo consulta y edición).
-- Ver docs/06-modelo-datos/sp/README.md (Framework) y regla BASE 74/75 (SP + NOLOCK).

-- =====================================================================
-- USUARIOS
-- =====================================================================

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_usuarios_list
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        u.id,
        u.usuario,
        u.name AS nombre,
        u.email,
        u.activo,
        u.inhabilitado
    FROM dbo.users u WITH (NOLOCK)
    ORDER BY u.usuario;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_usuarios_get
    @id INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        u.id,
        u.usuario,
        u.name AS nombre,
        u.email,
        u.activo,
        u.inhabilitado
    FROM dbo.users u WITH (NOLOCK)
    WHERE u.id = @id;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_usuarios_create
    @usuario NVARCHAR(64),
    @nombre NVARCHAR(255),
    @email NVARCHAR(255),
    @password_hash NVARCHAR(255),
    @activo BIT = 1
AS
BEGIN
    SET NOCOUNT ON;

    INSERT INTO dbo.users (name, usuario, email, password, first_login, supervisor, activo, inhabilitado, created_at, updated_at)
    VALUES (@nombre, @usuario, @email, @password_hash, 1, 0, @activo, 0, SYSUTCDATETIME(), SYSUTCDATETIME());

    DECLARE @newId INT = CAST(SCOPE_IDENTITY() AS INT);

    SELECT
        u.id,
        u.usuario,
        u.name AS nombre,
        u.email,
        u.activo,
        u.inhabilitado
    FROM dbo.users u WITH (NOLOCK)
    WHERE u.id = @newId;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_usuarios_update
    @id INT,
    @usuario NVARCHAR(64) = NULL,
    @nombre NVARCHAR(255) = NULL,
    @email NVARCHAR(255) = NULL,
    @activo BIT = NULL,
    @inhabilitado BIT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE dbo.users
    SET
        usuario = COALESCE(@usuario, usuario),
        name = COALESCE(@nombre, name),
        email = COALESCE(@email, email),
        activo = COALESCE(@activo, activo),
        inhabilitado = COALESCE(@inhabilitado, inhabilitado),
        updated_at = SYSUTCDATETIME()
    WHERE id = @id;

    SELECT
        u.id,
        u.usuario,
        u.name AS nombre,
        u.email,
        u.activo,
        u.inhabilitado
    FROM dbo.users u WITH (NOLOCK)
    WHERE u.id = @id;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_usuarios_soft_delete
    @id INT
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE dbo.users
    SET inhabilitado = 1,
        activo = 0,
        updated_at = SYSUTCDATETIME()
    WHERE id = @id;

    SELECT @@ROWCOUNT AS updated_rows;
END;
GO

-- =====================================================================
-- EMPRESAS (MONO: solo consulta y edición — NO alta/baja)
-- `theme`: whitelist efectiva DevExtreme; default/fallback 'generic.light' (D1-06-26).
-- =====================================================================

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_empresas_list
AS
BEGIN
    SET NOCOUNT ON;

    SELECT e.id, e.nombre, e.activo, e.theme
    FROM dbo.pq_empresa e WITH (NOLOCK)
    ORDER BY e.nombre;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_empresas_get
    @id INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT e.id, e.nombre, e.activo, e.theme
    FROM dbo.pq_empresa e WITH (NOLOCK)
    WHERE e.id = @id;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_empresas_update
    @id INT,
    @nombre NVARCHAR(255) = NULL,
    @activo BIT = NULL,
    @theme NVARCHAR(64) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE dbo.pq_empresa
    SET
        nombre = COALESCE(@nombre, nombre),
        activo = COALESCE(@activo, activo),
        theme = COALESCE(@theme, theme),
        updated_at = SYSUTCDATETIME()
    WHERE id = @id;

    SELECT e.id, e.nombre, e.activo, e.theme
    FROM dbo.pq_empresa e WITH (NOLOCK)
    WHERE e.id = @id;
END;
GO

-- =====================================================================
-- ROLES
-- =====================================================================

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_roles_list
AS
BEGIN
    SET NOCOUNT ON;

    SELECT r.id, r.codigo, r.nombre, r.acceso_total AS accesoTotal, r.activo
    FROM dbo.pq_roles r WITH (NOLOCK)
    ORDER BY r.codigo;
END;
GO

-- Detalle rol: usado por GEN-06-roles-atributos para 404/gate AccesoTotal.
CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_roles_get
    @id INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT r.id, r.codigo, r.nombre, r.acceso_total AS accesoTotal, r.activo
    FROM dbo.pq_roles r WITH (NOLOCK)
    WHERE r.id = @id;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_roles_create
    @codigo NVARCHAR(64),
    @nombre NVARCHAR(255),
    @acceso_total BIT = 0,
    @activo BIT = 1
AS
BEGIN
    SET NOCOUNT ON;

    INSERT INTO dbo.pq_roles (codigo, nombre, acceso_total, activo, created_at, updated_at)
    VALUES (@codigo, @nombre, @acceso_total, @activo, SYSUTCDATETIME(), SYSUTCDATETIME());

    DECLARE @newId INT = CAST(SCOPE_IDENTITY() AS INT);

    SELECT r.id, r.codigo, r.nombre, r.acceso_total AS accesoTotal, r.activo
    FROM dbo.pq_roles r WITH (NOLOCK)
    WHERE r.id = @newId;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_roles_update
    @id INT,
    @codigo NVARCHAR(64) = NULL,
    @nombre NVARCHAR(255) = NULL,
    @acceso_total BIT = NULL,
    @activo BIT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE dbo.pq_roles
    SET
        codigo = COALESCE(@codigo, codigo),
        nombre = COALESCE(@nombre, nombre),
        acceso_total = COALESCE(@acceso_total, acceso_total),
        activo = COALESCE(@activo, activo),
        updated_at = SYSUTCDATETIME()
    WHERE id = @id;

    SELECT r.id, r.codigo, r.nombre, r.acceso_total AS accesoTotal, r.activo
    FROM dbo.pq_roles r WITH (NOLOCK)
    WHERE r.id = @id;
END;
GO

-- DELETE: bloqueado si el rol tiene filas en pq_permisos (TR-GEN-06-roles-atributos).
CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_roles_delete
    @id INT
AS
BEGIN
    SET NOCOUNT ON;

    IF NOT EXISTS (SELECT 1 FROM dbo.pq_roles WITH (NOLOCK) WHERE id = @id)
    BEGIN
        SELECT CAST(0 AS BIT) AS deleted, N'not_found' AS outcome;
        RETURN;
    END;

    IF EXISTS (SELECT 1 FROM dbo.pq_permisos WITH (NOLOCK) WHERE rol_id = @id)
    BEGIN
        SELECT CAST(0 AS BIT) AS deleted, N'has_permisos' AS outcome;
        RETURN;
    END;

    DELETE FROM dbo.pq_rol_atributos WHERE rol_id = @id;
    DELETE FROM dbo.pq_roles WHERE id = @id;

    SELECT CAST(1 AS BIT) AS deleted, N'ok' AS outcome;
END;
GO

-- =====================================================================
-- PERMISOS (usuario x empresa x rol)
-- =====================================================================

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_permisos_list
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        p.id,
        p.user_id AS userId,
        u.usuario AS usuario,
        u.name AS usuarioNombre,
        p.empresa_id AS empresaId,
        e.nombre AS empresaNombre,
        p.rol_id AS rolId,
        r.codigo AS rolCodigo,
        r.nombre AS rolNombre
    FROM dbo.pq_permisos p WITH (NOLOCK)
    INNER JOIN dbo.users u WITH (NOLOCK) ON u.id = p.user_id
    INNER JOIN dbo.pq_empresa e WITH (NOLOCK) ON e.id = p.empresa_id
    INNER JOIN dbo.pq_roles r WITH (NOLOCK) ON r.id = p.rol_id
    ORDER BY u.usuario, e.nombre, r.codigo, p.id;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_permisos_list_by_user
    @user_id INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        p.id,
        p.user_id AS userId,
        u.usuario AS usuario,
        u.name AS usuarioNombre,
        p.empresa_id AS empresaId,
        e.nombre AS empresaNombre,
        p.rol_id AS rolId,
        r.codigo AS rolCodigo,
        r.nombre AS rolNombre
    FROM dbo.pq_permisos p WITH (NOLOCK)
    INNER JOIN dbo.users u WITH (NOLOCK) ON u.id = p.user_id
    INNER JOIN dbo.pq_empresa e WITH (NOLOCK) ON e.id = p.empresa_id
    INNER JOIN dbo.pq_roles r WITH (NOLOCK) ON r.id = p.rol_id
    WHERE p.user_id = @user_id
    ORDER BY p.id;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_permisos_create
    @user_id INT,
    @empresa_id INT,
    @rol_id INT
AS
BEGIN
    SET NOCOUNT ON;

    INSERT INTO dbo.pq_permisos (user_id, empresa_id, rol_id, created_at, updated_at)
    VALUES (@user_id, @empresa_id, @rol_id, SYSUTCDATETIME(), SYSUTCDATETIME());

    DECLARE @newId INT = CAST(SCOPE_IDENTITY() AS INT);

    SELECT
        p.id,
        p.user_id AS userId,
        u.usuario AS usuario,
        u.name AS usuarioNombre,
        p.empresa_id AS empresaId,
        e.nombre AS empresaNombre,
        p.rol_id AS rolId,
        r.codigo AS rolCodigo,
        r.nombre AS rolNombre
    FROM dbo.pq_permisos p WITH (NOLOCK)
    INNER JOIN dbo.users u WITH (NOLOCK) ON u.id = p.user_id
    INNER JOIN dbo.pq_empresa e WITH (NOLOCK) ON e.id = p.empresa_id
    INNER JOIN dbo.pq_roles r WITH (NOLOCK) ON r.id = p.rol_id
    WHERE p.id = @newId;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_permisos_create_if_absent
    @user_id INT,
    @empresa_id INT,
    @rol_id INT
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (
        SELECT 1
        FROM dbo.pq_permisos WITH (NOLOCK)
        WHERE user_id = @user_id
          AND empresa_id = @empresa_id
          AND rol_id = @rol_id
    )
    BEGIN
        SELECT CAST(0 AS INT) AS created;
        RETURN;
    END;

    INSERT INTO dbo.pq_permisos (user_id, empresa_id, rol_id, created_at, updated_at)
    VALUES (@user_id, @empresa_id, @rol_id, SYSUTCDATETIME(), SYSUTCDATETIME());

    SELECT CAST(1 AS INT) AS created;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_permisos_delete
    @id INT
AS
BEGIN
    SET NOCOUNT ON;

    DELETE FROM dbo.pq_permisos WHERE id = @id;

    SELECT @@ROWCOUNT AS updated_rows;
END;
GO
