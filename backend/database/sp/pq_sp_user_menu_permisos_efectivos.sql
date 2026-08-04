-- Permisos efectivos de menú por usuario+empresa (unión OR de atributos de roles asignados).
-- GEN-07 follow-up: alimenta permissions.create|update|delete|report en GET /user/menu.

CREATE OR ALTER PROCEDURE dbo.pq_sp_user_menu_permisos_efectivos
    @user_id INT,
    @empresa_id INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        a.menu_id AS menuId,
        CAST(MAX(CAST(a.permiso_alta AS INT)) AS BIT) AS permisoAlta,
        CAST(MAX(CAST(a.permiso_baja AS INT)) AS BIT) AS permisoBaja,
        CAST(MAX(CAST(a.permiso_modi AS INT)) AS BIT) AS permisoModi,
        CAST(MAX(CAST(a.permiso_repo AS INT)) AS BIT) AS permisoRepo
    FROM dbo.pq_permisos p WITH (NOLOCK)
    INNER JOIN dbo.pq_rol_atributos a WITH (NOLOCK) ON a.rol_id = p.rol_id
    WHERE p.user_id = @user_id
      AND p.empresa_id = @empresa_id
    GROUP BY a.menu_id;
END;
GO
