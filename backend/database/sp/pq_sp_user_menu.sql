-- Contrato SP GEN-07 menú (SQL Server). Host Partes: corregido a pq_empresa en SP de empresas.

IF OBJECT_ID(N'dbo.pq_sp_user_menu', N'P') IS NOT NULL
    DROP PROCEDURE dbo.pq_sp_user_menu;
GO

CREATE PROCEDURE dbo.pq_sp_user_menu
    @UserId INT,
    @EmpresaId INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        m.id,
        m.parent_id AS parentId,
        m.codigo AS menuKey,
        m.titulo AS text,
        m.ruta AS routeName,
        m.orden AS [order],
        m.procedimiento,
        m.process_type AS processType,
        m.icon_name AS iconName
    FROM dbo.pq_menus m WITH (NOLOCK)
    WHERE m.activo = 1 AND m.enabled = 1
    ORDER BY m.orden, m.id;
END;
GO
