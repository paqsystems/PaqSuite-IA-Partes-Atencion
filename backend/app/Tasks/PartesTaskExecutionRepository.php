<?php

namespace App\Tasks;

use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskExecutionRepository;
use PaqSuite\LaravelCore\Tasks\StoredProcedureTaskExecutionRepository;
use PDO;

/**
 * Host PARTES: listado de ejecuciones GEN-13 con drenado robusto de rowsets sqlsrv (CC PQ #10).
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
        $this->inner->saveParameterSnapshot($executionId, $parameters);
    }

    public function appendLog(int $executionId, string $level, string $message, array $context = []): void
    {
        $this->inner->appendLog($executionId, $level, $message, $context);
    }

    public function updateExecutionStatus(
        int $executionId,
        string $statusCode,
        ?\DateTimeInterface $finishedAt = null,
        ?string $message = null,
        bool $isOverlappedSkip = false,
    ): bool {
        return $this->inner->updateExecutionStatus($executionId, $statusCode, $finishedAt, $message, $isOverlappedSkip);
    }

    public function hasRunningOverlap(int $definitionId, int $companyId): bool
    {
        return $this->inner->hasRunningOverlap($definitionId, $companyId);
    }

    public function listExecutions(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        if (DB::connection()->getDriverName() !== 'sqlsrv') {
            return $this->inner->listExecutions($filters, $page, $pageSize);
        }

        return $this->listExecutionsSqlsrv($filters, $page, $pageSize);
    }

    public function findExecutionById(int $executionId): ?array
    {
        return $this->inner->findExecutionById($executionId);
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
}
