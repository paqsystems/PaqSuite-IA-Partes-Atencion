-- GEN-13: snapshot de parámetros por código (parameter_code NOT NULL).
-- El SDK envía un objeto JSON; el SP lo expande con OPENJSON.
CREATE OR ALTER PROCEDURE dbo.pq_sp_task_execution_save_snapshot
    @execution_id INT,
    @parameters_json NVARCHAR(MAX)
AS
BEGIN
    SET NOCOUNT ON;

    DELETE FROM pq_task_execution_parameter WHERE execution_id = @execution_id;

    IF ISJSON(@parameters_json) = 1 AND LEFT(LTRIM(@parameters_json), 1) = N'{'
    BEGIN
        INSERT INTO pq_task_execution_parameter (
            execution_id,
            parameter_code,
            value_json,
            parameters_json,
            created_at,
            updated_at
        )
        SELECT
            @execution_id,
            LEFT(j.[key], 100),
            j.[value],
            @parameters_json,
            SYSUTCDATETIME(),
            SYSUTCDATETIME()
        FROM OPENJSON(@parameters_json) AS j
        WHERE j.[key] IS NOT NULL
          AND LTRIM(RTRIM(j.[key])) <> N'';
    END;

    IF NOT EXISTS (
        SELECT 1
        FROM pq_task_execution_parameter
        WHERE execution_id = @execution_id
    )
    BEGIN
        INSERT INTO pq_task_execution_parameter (
            execution_id,
            parameter_code,
            value_json,
            parameters_json,
            created_at,
            updated_at
        )
        VALUES (
            @execution_id,
            N'_snapshot',
            @parameters_json,
            @parameters_json,
            SYSUTCDATETIME(),
            SYSUTCDATETIME()
        );
    END;
END;
GO

