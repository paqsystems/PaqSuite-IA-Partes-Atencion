-- GEN-14 SQL Server reference pack. Runtime business I/O MUST use these SPs.
CREATE OR ALTER PROCEDURE dbo.pq_sp_excel_process_get
    @process_code VARCHAR(64)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT codigo, descripcion, menu_process_code, handler_class, allow_partial,
           sheet_name, is_active
    FROM PQ_EXCEL_PROCESOS WITH (NOLOCK)
    WHERE codigo = @process_code;

    SELECT column_key, header, caption_key, data_type, is_required, orden
    FROM PQ_EXCEL_PROCESO_COLUMNAS WITH (NOLOCK)
    WHERE proceso_codigo = @process_code
    ORDER BY orden;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_excel_parameter_get
    @key VARCHAR(128)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT TOP (1) tipo_valor, valor_logico, valor_entero, valor_string
    FROM PQ_PARAMETROS_GRAL WITH (NOLOCK)
    WHERE programa = 'ExcelImport' AND clave = @key;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_excel_batch_create
    @id CHAR(36),
    @process_code VARCHAR(64),
    @company_id BIGINT = NULL,
    @created_by_user_id BIGINT,
    @status VARCHAR(20),
    @sheet_name VARCHAR(64) = NULL,
    @original_file_name VARCHAR(255),
    @file_size_bytes BIGINT
AS
BEGIN
    SET NOCOUNT ON;
    INSERT INTO pq_excel_batches (
        id, process_code, company_id, created_by_user_id, status, sheet_name,
        original_file_name, file_size_bytes, total_rows, valid_rows, error_rows,
        created_at, updated_at
    ) VALUES (
        @id, @process_code, @company_id, @created_by_user_id, @status, @sheet_name,
        @original_file_name, @file_size_bytes, 0, 0, 0, SYSUTCDATETIME(), SYSUTCDATETIME()
    );
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_excel_batch_get
    @id CHAR(36),
    @company_id BIGINT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    SELECT b.*, p.allow_partial
    FROM pq_excel_batches b WITH (NOLOCK)
    INNER JOIN PQ_EXCEL_PROCESOS p WITH (NOLOCK) ON p.codigo = b.process_code
    WHERE b.id = @id
      AND ((@company_id IS NULL AND b.company_id IS NULL) OR b.company_id = @company_id);
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_excel_batch_stage_row
    @batch_id CHAR(36),
    @row_number INT,
    @raw_json NVARCHAR(MAX),
    @normalized_json NVARCHAR(MAX),
    @is_valid BIT,
    @errors_json NVARCHAR(MAX) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;
    BEGIN TRANSACTION;

    INSERT INTO pq_excel_batch_rows (
        batch_id, row_number, raw_json, normalized_json, is_valid, created_at
    ) VALUES (
        @batch_id, @row_number, @raw_json, @normalized_json, @is_valid, SYSUTCDATETIME()
    );

    IF @errors_json IS NOT NULL
    BEGIN
        INSERT INTO pq_excel_batch_row_errors (
            batch_id, row_number, column_key, message_key, params_json, created_at
        )
        SELECT @batch_id, @row_number, column_key, message_key, params_json, SYSUTCDATETIME()
        FROM OPENJSON(@errors_json)
        WITH (
            column_key VARCHAR(64) '$.column',
            message_key VARCHAR(160) '$.messageKey',
            params_json NVARCHAR(MAX) '$.params' AS JSON
        );
    END;

    COMMIT TRANSACTION;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_excel_batch_transition
    @id CHAR(36),
    @expected_status VARCHAR(20),
    @status VARCHAR(20),
    @mode VARCHAR(10) = NULL,
    @total_rows INT = NULL,
    @valid_rows INT = NULL,
    @error_rows INT = NULL,
    @processed_rows INT = NULL,
    @failed_rows INT = NULL,
    @result_payload_json NVARCHAR(MAX) = NULL,
    @message_key VARCHAR(160) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE pq_excel_batches
    SET status = @status,
        mode = COALESCE(@mode, mode),
        total_rows = COALESCE(@total_rows, total_rows),
        valid_rows = COALESCE(@valid_rows, valid_rows),
        error_rows = COALESCE(@error_rows, error_rows),
        processed_rows = COALESCE(@processed_rows, processed_rows),
        failed_rows = COALESCE(@failed_rows, failed_rows),
        result_payload_json = COALESCE(@result_payload_json, result_payload_json),
        message_key = COALESCE(@message_key, message_key),
        validated_at = CASE WHEN @status IN ('validated', 'invalid') THEN SYSUTCDATETIME() ELSE validated_at END,
        processed_at = CASE WHEN @status IN ('done', 'partial', 'failed') THEN SYSUTCDATETIME() ELSE processed_at END,
        updated_at = SYSUTCDATETIME()
    WHERE id = @id AND status = @expected_status;

    SELECT @@ROWCOUNT AS affected_rows;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_excel_batch_errors_list
    @batch_id CHAR(36),
    @page INT = 1,
    @page_size INT = 25
AS
BEGIN
    SET NOCOUNT ON;
    SET @page = CASE WHEN @page < 1 THEN 1 ELSE @page END;
    SET @page_size = CASE WHEN @page_size < 1 THEN 25 WHEN @page_size > 100 THEN 100 ELSE @page_size END;

    SELECT row_number, column_key, message_key, params_json
    FROM pq_excel_batch_row_errors WITH (NOLOCK)
    WHERE batch_id = @batch_id
    ORDER BY row_number, id
    OFFSET (@page - 1) * @page_size ROWS FETCH NEXT @page_size ROWS ONLY;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_excel_staging_purge
    @older_than_days INT
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;
    DECLARE @cutoff DATETIME2 = DATEADD(DAY, -@older_than_days, SYSUTCDATETIME());
    DECLARE @eligible TABLE (id CHAR(36) PRIMARY KEY, cancel_batch BIT);

    INSERT INTO @eligible (id, cancel_batch)
    SELECT id,
           CASE WHEN processed_at IS NULL AND status IN ('validated', 'invalid', 'validating')
                THEN 1 ELSE 0 END
    FROM pq_excel_batches WITH (NOLOCK)
    WHERE created_at < @cutoff
      AND status IN ('validated', 'invalid', 'validating', 'cancelled', 'done', 'partial', 'failed');

    BEGIN TRANSACTION;
    DELETE e FROM pq_excel_batch_row_errors e WITH (NOLOCK) INNER JOIN @eligible x ON x.id = e.batch_id;
    DELETE r FROM pq_excel_batch_rows r WITH (NOLOCK) INNER JOIN @eligible x ON x.id = r.batch_id;
    UPDATE b SET status = 'cancelled', updated_at = SYSUTCDATETIME()
    FROM pq_excel_batches b WITH (NOLOCK) INNER JOIN @eligible x ON x.id = b.id
    WHERE x.cancel_batch = 1;
    COMMIT TRANSACTION;

    SELECT COUNT(*) AS purged_batches FROM @eligible;
END;
GO
