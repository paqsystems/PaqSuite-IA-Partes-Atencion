-- GEN-15 SQL Server reference pack. Runtime business I/O MUST use these SPs.
CREATE OR ALTER PROCEDURE dbo.pq_sp_emission_process_get
    @process_code VARCHAR(64)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT *
    FROM PQ_EMISSION_PROCESSES WITH (NOLOCK)
    WHERE process_code = @process_code;

    SELECT id, report_code, name, is_principal, visible_mobile, layout_mime
    FROM PQ_EMISSION_REPORTS WITH (NOLOCK)
    WHERE process_code = @process_code AND is_active = 1
    ORDER BY is_principal DESC, id;

    SELECT id, template_code, name, is_principal, visible_mobile
    FROM PQ_EMISSION_MAIL_TEMPLATES WITH (NOLOCK)
    WHERE process_code = @process_code AND is_active = 1
    ORDER BY is_principal DESC, id;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_emission_parameter_get
    @key VARCHAR(128)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT TOP (1) tipo_valor, valor_logico, valor_entero, valor_string
    FROM PQ_PARAMETROS_GRAL WITH (NOLOCK)
    WHERE programa = 'Emission' AND clave = @key;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_emission_job_create
    @id CHAR(36),
    @process_code VARCHAR(64),
    @company_id BIGINT = NULL,
    @group_id VARCHAR(64) = NULL,
    @created_by_user_id BIGINT,
    @status VARCHAR(20),
    @mode VARCHAR(20),
    @channel VARCHAR(20),
    @report_id BIGINT = NULL,
    @mail_template_id BIGINT = NULL,
    @preview_session_id CHAR(36) = NULL,
    @dataset_row_count INT,
    @estimated_size_bytes BIGINT
AS
BEGIN
    SET NOCOUNT ON;
    INSERT INTO pq_emission_jobs (
        id, process_code, company_id, group_id, created_by_user_id, status, mode,
        channel, report_id, mail_template_id, preview_session_id, dataset_row_count,
        estimated_size_bytes, created_at, updated_at
    ) VALUES (
        @id, @process_code, @company_id, @group_id, @created_by_user_id, @status, @mode,
        @channel, @report_id, @mail_template_id, @preview_session_id, @dataset_row_count,
        @estimated_size_bytes, SYSUTCDATETIME(), SYSUTCDATETIME()
    );
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_emission_job_get
    @id CHAR(36),
    @company_id BIGINT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    SELECT *
    FROM pq_emission_jobs WITH (NOLOCK)
    WHERE id = @id
      AND ((@company_id IS NULL AND company_id IS NULL) OR company_id = @company_id);
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_emission_job_transition
    @id CHAR(36),
    @expected_status VARCHAR(20),
    @status VARCHAR(20),
    @artifact_path NVARCHAR(1024) = NULL,
    @artifact_file_name NVARCHAR(255) = NULL,
    @artifact_mime VARCHAR(160) = NULL,
    @result_message_key VARCHAR(160) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE pq_emission_jobs
    SET status = @status,
        artifact_path = COALESCE(@artifact_path, artifact_path),
        artifact_file_name = COALESCE(@artifact_file_name, artifact_file_name),
        artifact_mime = COALESCE(@artifact_mime, artifact_mime),
        result_message_key = COALESCE(@result_message_key, result_message_key),
        finished_at = CASE WHEN @status IN ('done', 'failed') THEN SYSUTCDATETIME() ELSE finished_at END,
        updated_at = SYSUTCDATETIME()
    WHERE id = @id AND status = @expected_status;

    SELECT @@ROWCOUNT AS affected_rows;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_emission_artifacts_for_purge
    @older_than_days INT
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @cutoff DATETIME2 = DATEADD(DAY, -@older_than_days, SYSUTCDATETIME());
    SELECT id AS job_id, artifact_path
    FROM pq_emission_jobs WITH (NOLOCK)
    WHERE status IN ('done', 'failed')
      AND COALESCE(finished_at, created_at) < @cutoff
      AND artifact_path IS NOT NULL;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_emission_artifact_clear
    @job_id CHAR(36),
    @expected_path NVARCHAR(1024)
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE pq_emission_jobs
    SET artifact_path = NULL, updated_at = SYSUTCDATETIME()
    WHERE id = @job_id AND artifact_path = @expected_path;
    SELECT @@ROWCOUNT AS affected_rows;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_emission_report_layout_update
    @report_id BIGINT,
    @layout_definition VARBINARY(MAX),
    @layout_mime VARCHAR(160)
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE PQ_EMISSION_REPORTS
    SET layout_definition = @layout_definition,
        layout_mime = @layout_mime,
        updated_at = SYSUTCDATETIME()
    WHERE id = @report_id AND is_active = 1;
    SELECT @@ROWCOUNT AS affected_rows;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_emission_report_set_principal
    @report_id BIGINT
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;
    DECLARE @process_code VARCHAR(64);
    SELECT @process_code = process_code FROM PQ_EMISSION_REPORTS WITH (NOLOCK) WHERE id = @report_id;
    IF @process_code IS NULL RETURN;

    BEGIN TRANSACTION;
    UPDATE PQ_EMISSION_REPORTS SET is_principal = 0 WHERE process_code = @process_code;
    UPDATE PQ_EMISSION_REPORTS SET is_principal = 1 WHERE id = @report_id;
    COMMIT TRANSACTION;
END;
GO
