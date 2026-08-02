-- GEN-16: lectura de selección activa desde el store único de preferencias.
CREATE OR ALTER PROCEDURE dbo.pq_sp_llm_active_preference_get
    @user_id INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT active_llm_credential_id
    FROM users WITH (NOLOCK)
    WHERE id = @user_id;
END;
GO
