-- GEN-16: lista completa de credenciales del usuario (habilitadas y deshabilitadas).
CREATE OR ALTER PROCEDURE dbo.pq_sp_llm_credentials_list
    @user_id INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT
        id,
        user_id,
        nombre,
        proveedor,
        modelo,
        secreto_cifrado,
        base_url,
        supports_vision,
        enabled,
        created_at,
        updated_at
    FROM pq_llm_credentials WITH (NOLOCK)
    WHERE user_id = @user_id
    ORDER BY nombre ASC, id ASC;
END;
GO
