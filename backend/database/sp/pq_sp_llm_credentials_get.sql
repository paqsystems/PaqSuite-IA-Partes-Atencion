-- GEN-16: lookup aislado por dueño; evita distinguir id ajeno de inexistente.
CREATE OR ALTER PROCEDURE dbo.pq_sp_llm_credentials_get
    @credential_id INT,
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
    WHERE id = @credential_id
      AND user_id = @user_id;
END;
GO
