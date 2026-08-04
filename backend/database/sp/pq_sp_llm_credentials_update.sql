-- GEN-16: actualización completa tras merge de PATCH en la capa de servicio.
CREATE OR ALTER PROCEDURE dbo.pq_sp_llm_credentials_update
    @credential_id INT,
    @user_id INT,
    @nombre NVARCHAR(150),
    @proveedor NVARCHAR(50),
    @modelo NVARCHAR(150),
    @secreto_cifrado NVARCHAR(MAX),
    @base_url NVARCHAR(1000) = NULL,
    @supports_vision BIT,
    @enabled BIT
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE pq_llm_credentials
    SET
        nombre = @nombre,
        proveedor = @proveedor,
        modelo = @modelo,
        secreto_cifrado = @secreto_cifrado,
        base_url = @base_url,
        supports_vision = @supports_vision,
        enabled = @enabled,
        updated_at = SYSUTCDATETIME()
    OUTPUT
        inserted.id,
        inserted.user_id,
        inserted.nombre,
        inserted.proveedor,
        inserted.modelo,
        inserted.secreto_cifrado,
        inserted.base_url,
        inserted.supports_vision,
        inserted.enabled,
        inserted.created_at,
        inserted.updated_at
    WHERE id = @credential_id
      AND user_id = @user_id;
END;
GO
