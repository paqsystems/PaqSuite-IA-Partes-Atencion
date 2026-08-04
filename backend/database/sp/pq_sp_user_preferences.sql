-- Host Partes: preferencias usuario (locale + open_in_new_tab). Hueco upstream: Framework solo expone pq_sp_user_locale_*.
CREATE OR ALTER PROCEDURE dbo.pq_sp_user_preferences_get
    @user_id INT
AS
BEGIN
    SET NOCOUNT ON;
    SELECT locale, open_in_new_tab
    FROM users WITH (NOLOCK)
    WHERE id = @user_id;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_user_preferences_set
    @user_id INT,
    @locale NVARCHAR(16) = NULL,
    @open_in_new_tab BIT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE users
    SET
        locale = COALESCE(@locale, locale),
        open_in_new_tab = COALESCE(@open_in_new_tab, open_in_new_tab),
        updated_at = SYSUTCDATETIME()
    WHERE id = @user_id;
END;
GO
