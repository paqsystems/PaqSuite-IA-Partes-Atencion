<?php

namespace App\Tasks;

use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskExecutionRepository;
use PaqSuite\LaravelCore\Tasks\StoredProcedureTaskExecutionRepository;
use PaqSuite\LaravelCore\Tasks\TaskExecutionStatuses;
use PDO;

/**
 * Host PARTES: listado GEN-13 con drenado rowsets sqlsrv + SP de escritura compatibles sqlsrv (CC PQ #10).
 */
final class PartesTaskExecutionRepository implements TaskExecutionRepository
{
    public function __construct(
        private readonly StoredProcedureTaskExecutionRepository $inner,
    ) {
    }

    public function createBatch(array $batch): int
    {
        return $this->inner->createBatch($batch);
    }

    public function createExecution(array $execution): int
    {
        return $this->inner->createExecution($execution);
    }

    public function saveParameterSnapshot(int $executionId, array $parameters): void
    {
        if ($this->isSqlsrv()) {
            $this->execVoidProcedure('pq_sp_task_execution_save_snapshot', [
                'execution_id' => $executionId,
                'parameters_json' => json_encode($parameters, JSON_UNESCAPED_UNICODE),
            ]);

            return;
        }

        $this->inner->saveParameterSnapshot($executionId, $parameters);
    }

    public function appendLog(int $executionId, string $level, string $message, array $context = []): void
    {
        if ($this->isSqlsrv()) {
            $this->execVoidProcedure('pq_sp_task_execution_append_log', [
                'execution_id' => $executionId,
                'level' => $level,
                'message' => $message,
                'context_json' => $context === [] ? null : json_encode($context, JSON_UNESCAPED_UNICODE),
            ]);

            return;
        }

        $this->inner->appendLog($executionId, $level, $message, $context);
    }

    public function updateExecutionStatus(
        int $executionId,
        string $statusCode,
        ?\DateTimeInterface $finishedAt = null,
        ?string $message = null,
        bool $isOverlappedSkip = false,
    ): bool {
        if ($this->isSqlsrv()) {
            return $this->updateExecutionStatusSqlsrv(
                $executionId,
                $statusCode,
                $message,
                $isOverlappedSkip,
            );
        }

        return $this->inner->updateExecutionStatus(
            $executionId,
            $statusCode,
            $finishedAt,
            $message,
            $isOverlappedSkip,
        );
    }

    public function hasRunningOverlap(int $definitionId, int $companyId): bool
    {
        $this->closeStaleExecutionsBlockingOverlap($definitionId, $companyId);

        return $this->inner->hasRunningOverlap($definitionId, $companyId);
    }

    public function listExecutions(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        $result = $this->inner->listExecutions($filters, $page, $pageSize);
        if ($this->isSqlsrv()) {
            $result['items'] = $this->hydrateMissingMessages($result['items'] ?? []);
            $result = $this->backfillListWhenTotalWithoutItems($result, $filters, $page, $pageSize);
        }

        return $result;
    }

    public function findExecutionById(int $executionId): ?array
    {
        $row = $this->inner->findExecutionById($executionId);
        if ($row === null) {
            return null;
        }

        $hydrated = $this->hydrateMissingMessages([$row]);

        return $hydrated[0] ?? $row;
    }

    public function listLogs(int $executionId): array
    {
        return $this->inner->listLogs($executionId);
    }

    public function purgeOlderThan(int $olderThanDays, int $chunkSize = 1000): int
    {
        return $this->inner->purgeOlderThan($olderThanDays, $chunkSize);
    }

    /**
     * Recuperación GEN-13: SP primero; si no hay filas pero existen EN_EJECUCION, lectura directa.
     *
     * @return list<array<string, mixed>>
     */
    public function listStaleExecutionsForRecovery(int $page, int $pageSize): array
    {
        $filters = ['statusCode' => TaskExecutionStatuses::EN_EJECUCION];
        $result = $this->listExecutions($filters, $page, $pageSize);
        $items = $result['items'] ?? [];
        if ($items !== []) {
            return $items;
        }

        $direct = $this->listExecutionsDirectSql($filters, $page, $pageSize);

        return $direct['items'] ?? [];
    }

    /**
     * Si el SP devolvió total>0 pero items vacío (drift rowsets sqlsrv), completar lectura directa.
     *
     * @param  array{items: list<array<string, mixed>>, total: int}  $result
     * @param  array<string, mixed>  $filters
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    private function backfillListWhenTotalWithoutItems(
        array $result,
        array $filters,
        int $page,
        int $pageSize,
    ): array {
        $items = $result['items'] ?? [];
        $total = (int) ($result['total'] ?? 0);
        if ($items !== [] || $total === 0) {
            return $result;
        }

        $direct = $this->listExecutionsDirectSql($filters, $page, $pageSize);
        if ($direct['items'] === []) {
            return $result;
        }

        return $direct;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    private function listExecutionsDirectSql(array $filters, int $page, int $pageSize): array
    {
        $offset = max(0, ($page - 1) * $pageSize);
        $bindings = [];
        $where = ['1=1'];

        if (isset($filters['definitionId'])) {
            $where[] = 'e.task_definition_id = ?';
            $bindings[] = (int) $filters['definitionId'];
        }
        if (isset($filters['companyId'])) {
            $where[] = 'e.company_id = ?';
            $bindings[] = (int) $filters['companyId'];
        }
        if (isset($filters['statusCode']) && trim((string) $filters['statusCode']) !== '') {
            $where[] = 'e.status_code = ?';
            $bindings[] = trim((string) $filters['statusCode']);
        }
        if (isset($filters['fromAt']) && $filters['fromAt'] instanceof \DateTimeInterface) {
            $where[] = 'e.started_at >= ?';
            $bindings[] = $filters['fromAt']->format('Y-m-d H:i:s');
        }
        if (isset($filters['toAt']) && $filters['toAt'] instanceof \DateTimeInterface) {
            $where[] = 'e.started_at <= ?';
            $bindings[] = $filters['toAt']->format('Y-m-d H:i:s');
        }

        $whereSql = implode(' AND ', $where);
        $countRow = DB::selectOne(
            "SELECT COUNT(1) AS total FROM pq_task_execution e WITH (NOLOCK) WHERE {$whereSql}",
            $bindings,
        );
        $total = (int) ($countRow->total ?? 0);
        if ($total === 0) {
            return ['items' => [], 'total' => 0];
        }

        $listBindings = array_merge($bindings, [$offset, $pageSize]);
        $rows = DB::select(
            "SELECT e.execution_id AS id, e.task_definition_id AS definitionId, e.company_id AS companyId,
                    e.mode, e.is_simulation AS isSimulation, e.status_code AS statusCode, e.message,
                    e.is_overlapped_skip AS isOverlappedSkip, e.started_at AS startedAt, e.finished_at AS finishedAt
             FROM pq_task_execution e WITH (NOLOCK)
             WHERE {$whereSql}
             ORDER BY e.started_at DESC
             OFFSET ? ROWS FETCH NEXT ? ROWS ONLY",
            $listBindings,
        );

        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->camelRow((array) $row);
        }

        return ['items' => $items, 'total' => $total];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function hydrateMissingMessages(array $items): array
    {
        if ($items === [] || ! $this->isSqlsrv()) {
            return $items;
        }

        $needsHydration = [];
        foreach ($items as $index => $row) {
            if ($this->rowMessage($row) !== '') {
                continue;
            }
            $executionId = (int) ($row['id'] ?? $row['executionId'] ?? 0);
            if ($executionId > 0) {
                $needsHydration[$index] = $executionId;
            }
        }

        if ($needsHydration === []) {
            return $items;
        }

        foreach ($needsHydration as $index => $executionId) {
            $message = $this->resolveExecutionMessage($executionId);
            if ($message !== '') {
                $items[$index]['message'] = $message;
            }
        }

        return $items;
    }

    private function resolveExecutionMessage(int $executionId): string
    {
        $detail = $this->inner->findExecutionById($executionId);
        $fromDetail = trim((string) ($detail['message'] ?? ''));
        if ($fromDetail !== '') {
            return $fromDetail;
        }

        foreach ($this->inner->listLogs($executionId) as $log) {
            $logMessage = trim((string) ($log['message'] ?? ''));
            if ($logMessage !== '') {
                return $logMessage;
            }
        }

        return '';
    }

    private function updateExecutionStatusSqlsrv(
        int $executionId,
        string $statusCode,
        ?string $message,
        bool $isOverlappedSkip,
    ): bool {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare(
            'EXEC dbo.pq_sp_task_execution_update_status @execution_id = ?, @status_code = ?, @message = ?, @is_overlapped_skip = ?'
        );
        $stmt->execute([
            $executionId,
            $statusCode,
            $message,
            $isOverlappedSkip ? 1 : 0,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows !== []) {
            return (int) ($rows[0]['affected'] ?? 0) > 0;
        }

        while ($stmt->nextRowset()) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($rows !== [] && array_key_exists('affected', $rows[0])) {
                return (int) $rows[0]['affected'] > 0;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $bindings
     */
    private function execVoidProcedure(string $procedure, array $bindings): void
    {
        $parts = [];
        foreach (array_keys($bindings) as $name) {
            $parts[] = '@'.$name.' = ?';
        }
        $sql = 'EXEC dbo.'.$procedure.' '.implode(', ', $parts);
        DB::connection()->statement($sql, array_values($bindings));
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function rowMessage(array $row): string
    {
        return trim((string) ($row['message'] ?? ''));
    }

    private function isSqlsrv(): bool
    {
        return DB::connection()->getDriverName() === 'sqlsrv';
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function camelRow(array $row): array
    {
        $out = [];
        foreach ($row as $key => $value) {
            $camel = is_string($key)
                ? lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))))
                : $key;
            $out[$camel] = $value;
        }

        return $out;
    }

    /**
     * Ejecuciones EN_EJECUCION zombie bloquean manual y automático (OMITIDA en cadena).
     */
    private function closeStaleExecutionsBlockingOverlap(int $definitionId, int $companyId): void
    {
        $maxAgeSeconds = $this->overlapRecoveryMaxAgeSeconds();
        $cutoff = new DateTimeImmutable('-'.$maxAgeSeconds.' seconds');
        $pageResult = $this->listExecutions(
            [
                'definitionId' => $definitionId,
                'companyId' => $companyId,
                'statusCode' => TaskExecutionStatuses::EN_EJECUCION,
            ],
            1,
            20,
        );

        foreach ($pageResult['items'] ?? [] as $row) {
            $executionId = (int) ($row['id'] ?? $row['executionId'] ?? 0);
            if ($executionId <= 0) {
                continue;
            }

            $startedAt = $this->asDateTime($row['startedAt'] ?? null);
            if ($startedAt === null || $startedAt > $cutoff) {
                continue;
            }

            $staleRecovery = app(StaleTaskExecutionRecovery::class);
            $staleRecovery->closeStaleExecutionIfNeeded($row, $cutoff);
        }
    }

    private function overlapRecoveryMaxAgeSeconds(): int
    {
        $configured = (int) config('paqsuite.tasks.execution.overlapRecoveryMaxAgeSeconds', 0);
        if ($configured > 0) {
            return max(60, $configured);
        }

        $lockLease = max(60, (int) config('paqsuite.tasks.backup.lockLeaseSeconds', 900));

        return max(300, (int) floor($lockLease / 2));
    }

    private function asDateTime(mixed $value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $value)
            ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);

        return $parsed !== false ? $parsed : null;
    }
}
