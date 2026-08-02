-- GEN-05: universo empresas del usuario. Host Partes: tabla canónica pq_empresa (fix upstream Framework stub pq_empresas).
CREATE OR ALTER PROCEDURE dbo.pq_sp_user_empresas_list
    @user_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT DISTINCT
        e.id,
        e.nombre AS nombreEmpresa,
        e.theme
    FROM pq_empresa e WITH (NOLOCK)
    INNER JOIN pq_permisos p WITH (NOLOCK) ON p.empresa_id = e.id
    WHERE p.user_id = @user_id
      AND e.activo = 1
    ORDER BY e.nombre;
END;
GO
