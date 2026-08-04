<?php

namespace App\Repositories\Sp\ExcelImport;

use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\ExcelImport\Contracts\ExcelImportRepository;
use PaqSuite\LaravelCore\ExcelImport\Dto\ExcelImportBatch;
use PaqSuite\LaravelCore\ExcelImport\Dto\ExcelImportColumn;
use PaqSuite\LaravelCore\ExcelImport\Dto\ExcelImportProcess;
use PaqSuite\LaravelCore\ExcelImport\Dto\ExcelImportRowError;
use PaqSuite\LaravelCore\ExcelImport\Dto\RowValidationResult;
use PaqSuite\LaravelCore\Parametros\Contracts\ParametroRepository;

/**
 * Adapter host MONO (TR-009): Query Builder sobre tablas pq_excel_*.
 * Contrato alineado a pq_sp_excel_* (script en database/sp para SQL Server).
 */
final class SpExcelImportRepository implements ExcelImportRepository
{
    public function __construct(private readonly ParametroRepository $parametros)
    {
    }

    public function findProcess(string $processCode): ?ExcelImportProcess
    {
        $proceso = DB::table('pq_excel_procesos')->where('codigo', $processCode)->first();
        if ($proceso === null) {
            return null;
        }

        $columns = DB::table('pq_excel_proceso_columnas')
            ->where('proceso_codigo', $processCode)
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->map(static fn ($column): ExcelImportColumn => new ExcelImportColumn(
                (string) $column->column_key,
                (string) $column->header,
                (string) $column->data_type,
                (bool) $column->is_required,
                $column->caption_key,
                isset($column->help_text) ? (string) $column->help_text : null,
                isset($column->decimal_places) ? (int) $column->decimal_places : null,
            ))
            ->all();

        return new ExcelImportProcess(
            (string) $proceso->codigo,
            (string) $proceso->menu_process_code,
            (bool) $proceso->allow_partial,
            $columns,
            $proceso->sheet_name,
            $proceso->handler_class,
            (bool) $proceso->is_active,
            (string) ($proceso->boolean_format_plantilla ?? ExcelImportProcess::BOOLEAN_FORMAT_VERDADERO_FALSO),
        );
    }

    public function parameter(string $program, string $key): string|int|null
    {
        $row = $this->parametros->get($program, $key);
        if ($row === null) {
            return null;
        }

        $valor = $row['valor'] ?? null;

        if ($program === 'ExcelImport' && $key === 'ExcelImportEnabled') {
            if ($valor === true || $valor === 1 || $valor === '1' || strtoupper((string) $valor) === 'S') {
                return 'S';
            }

            return 'N';
        }

        if (is_int($valor)) {
            return $valor;
        }
        if (is_string($valor) || is_numeric($valor)) {
            return is_numeric($valor) && ! is_string($valor) ? (int) $valor : (string) $valor;
        }

        return null;
    }

    public function createBatch(ExcelImportBatch $batch): void
    {
        DB::table('pq_excel_batches')->insert([
            'id' => $batch->batchId,
            'process_code' => $batch->processCode,
            'company_id' => $batch->companyId === null ? null : (int) $batch->companyId,
            'created_by_user_id' => (int) $batch->createdByUserId,
            'status' => $batch->status,
            'mode' => $batch->mode,
            'sheet_name' => $batch->sheetName,
            'original_file_name' => $batch->fileName,
            'file_size_bytes' => $batch->fileSizeBytes,
            'total_rows' => $batch->totalRows,
            'valid_rows' => $batch->validRows,
            'error_rows' => $batch->errorRows,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function findBatch(string $batchId, int|string|null $companyId): ?ExcelImportBatch
    {
        $row = $this->findBatchRow($batchId, $companyId);

        return $row === null ? null : $this->toBatchDto($row);
    }

    public function stageRow(string $batchId, int $rowNumber, array $raw, RowValidationResult $validation): void
    {
        DB::table('pq_excel_batch_rows')->insert([
            'batch_id' => $batchId,
            'row_number' => $rowNumber,
            'raw_json' => json_encode($raw, JSON_UNESCAPED_UNICODE),
            'normalized_json' => json_encode($validation->normalized, JSON_UNESCAPED_UNICODE),
            'is_valid' => $validation->isValid(),
            'created_at' => now(),
        ]);

        foreach ($validation->errors as $error) {
            DB::table('pq_excel_batch_row_errors')->insert([
                'batch_id' => $batchId,
                'row_number' => $error->rowNumber,
                'column_key' => $error->column,
                'message_key' => $error->messageKey,
                'params_json' => $error->params === [] ? null : json_encode($error->params, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
            ]);
        }
    }

    public function transition(string $batchId, string $expectedStatus, string $status, array $fields = []): bool
    {
        $map = [
            'totalRows' => 'total_rows',
            'validRows' => 'valid_rows',
            'errorRows' => 'error_rows',
            'processedRows' => 'processed_rows',
            'failedRows' => 'failed_rows',
            'mode' => 'mode',
            'sheetName' => 'sheet_name',
            'messageKey' => 'message_key',
            'validatedAt' => 'validated_at',
            'processedAt' => 'processed_at',
        ];

        $values = ['status' => $status, 'updated_at' => now()];

        foreach ($map as $key => $column) {
            if (! array_key_exists($key, $fields)) {
                continue;
            }
            $value = $fields[$key];
            $values[$column] = $value instanceof \DateTimeInterface
                ? $value->format('Y-m-d H:i:s')
                : $value;
        }

        if (array_key_exists('resultPayload', $fields)) {
            $values['result_payload_json'] = json_encode($fields['resultPayload'], JSON_UNESCAPED_UNICODE);
        }

        return DB::table('pq_excel_batches')
            ->where('id', $batchId)
            ->where('status', $expectedStatus)
            ->update($values) > 0;
    }

    public function errors(string $batchId, int $page, int $pageSize): array
    {
        return DB::table('pq_excel_batch_row_errors')
            ->where('batch_id', $batchId)
            ->orderBy('row_number')
            ->orderBy('id')
            ->forPage($page, $pageSize)
            ->get()
            ->map(static function ($row): ExcelImportRowError {
                $params = json_decode((string) ($row->params_json ?? '[]'), true);

                return new ExcelImportRowError(
                    (int) $row->row_number,
                    (string) $row->column_key,
                    (string) $row->message_key,
                    is_array($params) ? $params : [],
                );
            })
            ->all();
    }

    public function errorsCount(string $batchId): int
    {
        return (int) DB::table('pq_excel_batch_row_errors')->where('batch_id', $batchId)->count();
    }

    /**
     * @return list<array{rowNumber: int, normalized: array<string, mixed>}>
     */
    public function validRows(string $batchId): array
    {
        return DB::table('pq_excel_batch_rows')
            ->where('batch_id', $batchId)
            ->where('is_valid', true)
            ->orderBy('row_number')
            ->get()
            ->map(static function ($row): array {
                $normalized = json_decode((string) ($row->normalized_json ?? '{}'), true);

                return [
                    'rowNumber' => (int) $row->row_number,
                    'normalized' => is_array($normalized) ? $normalized : [],
                ];
            })
            ->all();
    }

    public function purgeStaging(int $olderThanDays): int
    {
        $threshold = now()->subDays(max(1, $olderThanDays));
        $batches = DB::table('pq_excel_batches')->where('created_at', '<', $threshold)->get();
        if ($batches->isEmpty()) {
            return 0;
        }

        $purged = 0;
        $abandoned = ['validated', 'invalid', 'validating', 'cancelled'];

        foreach ($batches as $batch) {
            $batchId = (string) $batch->id;
            DB::table('pq_excel_batch_row_errors')->where('batch_id', $batchId)->delete();
            DB::table('pq_excel_batch_rows')->where('batch_id', $batchId)->delete();
            $purged++;

            if (
                in_array((string) $batch->status, $abandoned, true)
                && $batch->processed_at === null
            ) {
                DB::table('pq_excel_batches')->where('id', $batchId)->update([
                    'status' => 'cancelled',
                    'updated_at' => now(),
                ]);
            }
        }

        return $purged;
    }

    /**
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function listBatches(int|string|null $companyId, ?string $processCode, int $page, int $pageSize): array
    {
        $query = DB::table('pq_excel_batches');

        if ($companyId !== null) {
            $query->where('company_id', (int) $companyId);
        }

        if ($processCode !== null && $processCode !== '') {
            $query->where('process_code', $processCode);
        }

        $total = (clone $query)->count();
        $items = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->forPage($page, $pageSize)
            ->get()
            ->map(fn ($batch): array => $this->toBatchRow($batch))
            ->all();

        return ['items' => $items, 'total' => $total];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function batchRow(string $batchId, int|string|null $companyId): ?array
    {
        $row = $this->findBatchRow($batchId, $companyId);

        return $row === null ? null : $this->toBatchRow($row);
    }

    private function findBatchRow(string $batchId, int|string|null $companyId): ?object
    {
        $query = DB::table('pq_excel_batches')->where('id', $batchId);

        if ($companyId !== null) {
            $query->where(function ($q) use ($companyId): void {
                $q->where('company_id', (int) $companyId)->orWhereNull('company_id');
            });
        }

        return $query->first();
    }

    private function toBatchDto(object $row): ExcelImportBatch
    {
        return new ExcelImportBatch(
            (string) $row->id,
            (string) $row->process_code,
            $row->company_id,
            (int) $row->created_by_user_id,
            (string) $row->status,
            (int) $row->file_size_bytes,
            (int) $row->total_rows,
            (int) $row->valid_rows,
            (int) $row->error_rows,
            $row->mode,
            $row->original_file_name,
            $row->sheet_name,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function toBatchRow(object $row): array
    {
        $process = $this->findProcess((string) $row->process_code);
        $payload = null;
        if (! empty($row->result_payload_json)) {
            $decoded = json_decode((string) $row->result_payload_json, true);
            $payload = is_array($decoded) ? $decoded : null;
        }

        return [
            'batchId' => (string) $row->id,
            'processCode' => (string) $row->process_code,
            'companyId' => $row->company_id,
            'status' => (string) $row->status,
            'mode' => $row->mode,
            'allowPartial' => $process?->allowPartial ?? false,
            'fileName' => $row->original_file_name,
            'sheetName' => $row->sheet_name,
            'fileSizeBytes' => (int) $row->file_size_bytes,
            'totalRows' => (int) $row->total_rows,
            'validRows' => (int) $row->valid_rows,
            'errorRows' => (int) $row->error_rows,
            'processedRows' => $row->processed_rows === null ? null : (int) $row->processed_rows,
            'failedRows' => $row->failed_rows === null ? null : (int) $row->failed_rows,
            'messageKey' => $row->message_key,
            'data' => $payload,
            'createdAt' => isset($row->created_at) ? (string) $row->created_at : null,
            'validatedAt' => isset($row->validated_at) ? (string) $row->validated_at : null,
            'processedAt' => isset($row->processed_at) ? (string) $row->processed_at : null,
        ];
    }
}
