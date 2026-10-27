-- Contrato TR-013: baja de paquete con optimistic lock.
CREATE OR ALTER PROCEDURE dbo.pq_sp_partes_paquete_delete
    @p_actor_tipo_funcional NVARCHAR(20),
    @p_actor_asistente_id INT,
    @p_actor_cliente_id INT = NULL,
    @p_actor_es_supervisor BIT,
    @p_id INT,
    @p_row_version NVARCHAR(32)
AS
BEGIN
    SET NOCOUNT ON;
    IF @p_actor_es_supervisor = 0 OR @p_actor_asistente_id IS NULL
    BEGIN
        RAISERROR('partes.paquete.soloSupervisor', 16, 1);
        RETURN;
    END
    IF NOT EXISTS (SELECT 1 FROM dbo.PQ_PARTES_REGISTRO_TAREA WHERE id = @p_id)
    BEGIN
        RAISERROR('partes.paquete.notFound', 16, 1);
        RETURN;
    END
    IF EXISTS (SELECT 1 FROM dbo.PQ_PARTES_REGISTRO_TAREA WHERE id = @p_id AND es_tarea = 1)
    BEGIN
        RAISERROR('partes.paquete.notPaquete', 16, 1);
        RETURN;
    END

    DELETE FROM dbo.PQ_PARTES_REGISTRO_TAREA WHERE id = @p_id;
    SELECT 1 AS deleted;
END
GO
