-- GEN-16: normaliza ids ajenos/inexistentes/deshabilitados a NULL.
CREATE OR ALTER PROCEDURE dbo.pq_sp_llm_active_preference_set
    @user_id INT,
    @credential_id INT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @normalized_id INT = NULL;

    IF @credential_id IS NOT NULL
    BEGIN
        SELECT @normalized_id = id
        FROM pq_llm_credentials WITH (NOLOCK)
        WHERE id = @credential_id
          AND user_id = @user_id
          AND enabled = 1;
    END;

    UPDATE users
    SET active_llm_credential_id = @normalized_id
    WHERE id = @user_id;

    SELECT @normalized_id AS active_llm_credential_id;
END;
GO
