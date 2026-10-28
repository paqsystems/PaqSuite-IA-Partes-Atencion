-- Host override: soporta esquema template (log_timestamp) y migración host (level/created_at).
CREATE OR ALTER PROCEDURE dbo.pq_sp_task_execution_append_log
    @execution_id INT,
    @level NVARCHAR(16),
    @message NVARCHAR(MAX),
    @context_json NVARCHAR(MAX) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    IF COL_LENGTH('dbo.pq_task_execution_log', 'log_timestamp') IS NOT NULL
    BEGIN
        EXEC sp_executesql N'
            INSERT INTO pq_task_execution_log (
                execution_id,
                log_timestamp,
                log_level,
                message_text,
                context_json,
                created_at
            )
            VALUES (
                @execution_id,
                SYSUTCDATETIME(),
                @level,
                @message,
                @context_json,
                SYSUTCDATETIME()
            );',
            N'@execution_id INT, @level NVARCHAR(16), @message NVARCHAR(MAX), @context_json NVARCHAR(MAX)',
            @execution_id = @execution_id,
            @level = @level,
            @message = @message,
            @context_json = @context_json;

        RETURN;
    END;

    EXEC sp_executesql N'
        INSERT INTO pq_task_execution_log (execution_id, level, message, context_json, created_at)
        VALUES (@execution_id, @level, @message, @context_json, SYSUTCDATETIME());',
        N'@execution_id INT, @level NVARCHAR(16), @message NVARCHAR(MAX), @context_json NVARCHAR(MAX)',
        @execution_id = @execution_id,
        @level = @level,
        @message = @message,
        @context_json = @context_json;
END;
GO
