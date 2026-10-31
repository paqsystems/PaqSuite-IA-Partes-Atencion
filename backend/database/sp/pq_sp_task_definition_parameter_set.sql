-- Host override (post installation:update): BD con process_parameter_id NOT NULL (DEV/RDS legacy).
-- SoT alineado: packages/php/laravel-core/database/sp/pq_sp_task_definition_upsert.sql
CREATE OR ALTER PROCEDURE dbo.pq_sp_task_definition_parameter_set
    @task_definition_id INT,
    @parameters_json NVARCHAR(MAX)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @process_id INT;

    SELECT @process_id = d.process_id
    FROM pq_task_definition d WITH (NOLOCK)
    WHERE d.task_definition_id = @task_definition_id;

    IF @process_id IS NULL
    BEGIN
        SELECT 0 AS affected;
        RETURN;
    END;

    DELETE FROM pq_task_definition_parameter WHERE task_definition_id = @task_definition_id;

    IF @parameters_json IS NULL OR LTRIM(RTRIM(@parameters_json)) = N''
    BEGIN
        SELECT 0 AS affected;
        RETURN;
    END;

    IF COL_LENGTH('dbo.pq_task_definition_parameter', 'process_parameter_id') IS NOT NULL
    BEGIN
        INSERT INTO pq_task_definition_parameter (
            task_definition_id,
            process_parameter_id,
            parameter_code,
            value_json,
            created_at,
            updated_at
        )
        SELECT
            @task_definition_id,
            pp.process_parameter_id,
            j.[key],
            j.[value],
            SYSUTCDATETIME(),
            SYSUTCDATETIME()
        FROM OPENJSON(@parameters_json) j
        INNER JOIN pq_task_process_parameter pp WITH (NOLOCK)
            ON pp.process_id = @process_id
           AND pp.parameter_code COLLATE DATABASE_DEFAULT = j.[key] COLLATE DATABASE_DEFAULT
        WHERE j.[key] IS NOT NULL
          AND LTRIM(RTRIM(j.[key])) <> N'';
    END
    ELSE
    BEGIN
        INSERT INTO pq_task_definition_parameter (
            task_definition_id, parameter_code, value_json, created_at, updated_at
        )
        SELECT @task_definition_id, j.[key], j.[value], SYSUTCDATETIME(), SYSUTCDATETIME()
        FROM OPENJSON(@parameters_json) j
        WHERE j.[key] IS NOT NULL AND LTRIM(RTRIM(j.[key])) <> N'';
    END;

    SELECT @@ROWCOUNT AS affected;
END;
