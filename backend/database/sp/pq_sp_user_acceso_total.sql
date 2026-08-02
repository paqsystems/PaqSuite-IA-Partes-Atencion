-- Contrato SP GEN-06 AccesoTotal (SQL Server).

IF OBJECT_ID(N'dbo.pq_sp_user_acceso_total', N'P') IS NOT NULL
    DROP PROCEDURE dbo.pq_sp_user_acceso_total;
GO

CREATE PROCEDURE dbo.pq_sp_user_acceso_total
    @UserId INT,
    @EmpresaId INT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    SELECT TOP (1) 1 AS accesoTotal
    FROM dbo.pq_permisos p WITH (NOLOCK)
    INNER JOIN dbo.pq_roles r WITH (NOLOCK) ON r.id = p.rol_id
    WHERE p.user_id = @UserId
      AND r.acceso_total = 1
      AND r.activo = 1
      AND (@EmpresaId IS NULL OR p.empresa_id = @EmpresaId);
END;
GO
