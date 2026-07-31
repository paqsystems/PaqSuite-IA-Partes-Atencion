-- GEN-05: validación pertenencia empresa. Host Partes: pq_empresa (fix upstream).
CREATE OR ALTER PROCEDURE dbo.pq_sp_user_empresa_allowed
    @user_id INT,
    @empresa_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT CASE WHEN EXISTS (
        SELECT 1
        FROM pq_permisos p WITH (NOLOCK)
        INNER JOIN pq_empresa e WITH (NOLOCK) ON e.id = p.empresa_id
        WHERE p.user_id = @user_id
          AND p.empresa_id = @empresa_id
          AND e.activo = 1
    ) THEN 1 ELSE 0 END AS allowed;
END;
GO
