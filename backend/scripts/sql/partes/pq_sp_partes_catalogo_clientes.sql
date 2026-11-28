/*
 * Catálogo operativo de clientes Partes.
 */
CREATE OR ALTER PROCEDURE dbo.pq_sp_partes_catalogo_clientes
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        c.id,
        c.code,
        c.nombre
    FROM dbo.PQ_PARTES_CLIENTES AS c WITH (NOLOCK)
    WHERE c.activo = 1
      AND c.inhabilitado = 0
    ORDER BY c.code;
END;
GO
