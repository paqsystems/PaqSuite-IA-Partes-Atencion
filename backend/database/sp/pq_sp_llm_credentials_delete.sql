-- GEN-16: baja física y limpieza atómica de selección activa.
CREATE OR ALTER PROCEDURE dbo.pq_sp_llm_credentials_delete
    @credential_id INT,
    @user_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    BEGIN TRANSACTION;

    UPDATE users
    SET active_llm_credential_id = NULL
    WHERE id = @user_id
      AND active_llm_credential_id = @credential_id;

    DELETE FROM pq_llm_credentials
    WHERE id = @credential_id
      AND user_id = @user_id;

    SELECT @@ROWCOUNT AS deleted_count;

    COMMIT TRANSACTION;
END;
GO
