-- GEN-16: alta BYOK. El secreto llega ya cifrado por Laravel Crypt.
CREATE OR ALTER PROCEDURE dbo.pq_sp_llm_credentials_insert
    @user_id INT,
    @nombre NVARCHAR(150),
    @proveedor NVARCHAR(50),
    @modelo NVARCHAR(150),
    @secreto_cifrado NVARCHAR(MAX),
    @base_url NVARCHAR(1000) = NULL,
    @supports_vision BIT = 0,
    @enabled BIT = 1
AS
BEGIN
    SET NOCOUNT ON;

    INSERT INTO pq_llm_credentials (
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
    )
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
    VALUES (
        @user_id,
        @nombre,
        @proveedor,
        @modelo,
        @secreto_cifrado,
        @base_url,
        @supports_vision,
        @enabled,
        SYSUTCDATETIME(),
        SYSUTCDATETIME()
    );
END;
GO
