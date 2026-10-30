-- GEN-07 — catálogo enabled (host Partes, esquema SDK codigo + label_key).

CREATE OR ALTER PROCEDURE dbo.pq_sp_admin_menu_list_enabled
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        m.id,
        m.parent_id AS parentId,
        m.codigo AS menuKey,
        NULLIF(LTRIM(RTRIM(m.label_key)), '') AS labelKey,
        m.titulo AS text,
        m.ruta AS routeName,
        m.orden AS [order],
        m.procedimiento,
        m.process_type AS processType,
        m.icon_name AS iconName,
        CAST(m.enabled AS BIT) AS enabled
    FROM dbo.pq_menus m WITH (NOLOCK)
    WHERE m.activo = 1 AND m.enabled = 1
    ORDER BY m.orden, m.id;
END;
GO
