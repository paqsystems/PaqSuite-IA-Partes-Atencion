/*
 * Universo de tipos de tarea para carga Partes:
 * tipos genéricos usables + tipos asignados al cliente.
 */
CREATE OR ALTER PROCEDURE dbo.pq_sp_partes_catalogo_tipos_tarea_universo
    @p_cliente_id INT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    SELECT DISTINCT
        t.id,
        t.code,
        t.descripcion,
        t.is_generico,
        t.is_default
    FROM dbo.PQ_PARTES_TIPOS_TAREA AS t WITH (NOLOCK)
    LEFT JOIN dbo.PQ_PARTES_CLIENTE_TIPO_TAREA AS a WITH (NOLOCK)
        ON a.tipo_tarea_id = t.id
       AND (@p_cliente_id IS NOT NULL AND a.cliente_id = @p_cliente_id)
    WHERE t.activo = 1
      AND t.inhabilitado = 0
      AND (
          t.is_generico = 1
          OR a.tipo_tarea_id IS NOT NULL
      )
    ORDER BY t.code;
END;
GO
