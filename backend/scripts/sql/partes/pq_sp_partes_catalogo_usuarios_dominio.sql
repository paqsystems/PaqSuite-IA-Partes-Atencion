/*
 * Catálogo operativo de asistentes Partes.
 * DDL/despliegue de SP: la API lo invoca mediante PartesMaestrosRepository.
 */
CREATE OR ALTER PROCEDURE dbo.pq_sp_partes_catalogo_usuarios_dominio
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        u.id,
        u.code,
        u.nombre,
        u.supervisor
    FROM dbo.PQ_PARTES_USUARIOS AS u WITH (NOLOCK)
    WHERE u.activo = 1
      AND u.inhabilitado = 0
    ORDER BY u.code;
END;
GO
