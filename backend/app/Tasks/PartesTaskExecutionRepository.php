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
        if (! $this->isSqlsrv()) {
            return $this->inner->listExecutions($filters, $page, $pageSize);
        }

        $result = $this->listExecutionsSqlsrv($filters, $page, $pageSize);
        $result['items'] = $this->hydrateMissingMessages($result['items'] ?? []);

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
     * @param  array<string, mixed>  $filters
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    private function listExecutionsSqlsrv(array $filters, int $page, int $pageSize): array
    {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare(
            'EXEC dbo.pq_sp_task_execution_list @definition_id = ?, @company_id = ?, @status_code = ?, @from_at = ?, @to_at = ?, @page = ?, @page_size = ?'
        );
        $fromAt = $filters['fromAt'] ?? null;
        $toAt = $filters['toAt'] ?? null;
        $stmt->execute([
            $filters['definitionId'] ?? null,
            $filters['companyId'] ?? null,
            $filters['statusCode'] ?? null,
            $fromAt instanceof \DateTimeInterface ? $fromAt->format('Y-m-d H:i:s') : $fromAt,
            $toAt instanceof \DateTimeInterface ? $toAt->format('Y-m-d H:i:s') : $toAt,
            $page,
            $pageSize,
        ]);

        $total = 0;
        $items = [];

        do {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($rows === []) {
                continue;
            }
            $first = $rows[0];
            if (array_key_exists('total', $first) && count($first) === 1) {
                $total = (int) $first['total'];

                continue;
            }
            if (array_key_exists('id', $first) || array_key_exists('definitionId', $first)) {
                foreach ($rows as $row) {
                    $items[] = $this->camelRow($row);
                }
            }
        } while ($stmt->nextRowset());

        if ($items === [] && $total === 0) {
            return $this->inner->listExecutions($filters, $page, $pageSize);
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

            $message = StaleTaskExecutionRecovery::recoveryMessage();
            try {
                $this->appendLog($executionId, 'warning', $message, [
                    'startedAt' => $startedAt->format(DATE_ATOM),
                ]);
            } catch (\Throwable) {
                // best-effort
            }

            $this->updateExecutionStatus(
                $executionId,
                TaskExecutionStatuses::FALLIDA,
                new DateTimeImmutable('now'),
                $message,
            );
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
