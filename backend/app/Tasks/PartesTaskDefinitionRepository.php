<?php

namespace App\Tasks;

use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskDefinitionRepository;
use PDO;

/**
 * Host PARTES: listado de definiciones GEN-13 con drenado robusto de rowsets sqlsrv (CC PQ #10).
 */
final class PartesTaskDefinitionRepository implements TaskDefinitionRepository
{
    public function __construct(
        private readonly TaskDefinitionRepository $inner,
    ) {
    }

    public function listDefinitions(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        if (DB::connection()->getDriverName() !== 'sqlsrv') {
            return $this->inner->listDefinitions($filters, $page, $pageSize);
        }

        return $this->listDefinitionsSqlsrv($filters, $page, $pageSize);
    }

    public function findDefinitionById(int $definitionId): ?array
    {
        return $this->inner->findDefinitionById($definitionId);
    }

    public function createDefinition(array $payload): int
    {
        return $this->inner->createDefinition($payload);
    }

    public function updateDefinition(int $definitionId, array $payload): bool
    {
        return $this->inner->updateDefinition($definitionId, $payload);
    }

    public function softDeactivate(int $definitionId): bool
    {
        return $this->inner->softDeactivate($definitionId);
    }

    public function listCompanyIds(int $definitionId): array
    {
        return $this->inner->listCompanyIds($definitionId);
    }

    public function listDefinitionParameterValues(int $definitionId): array
    {
        return $this->inner->listDefinitionParameterValues($definitionId);
    }

    public function listDueDefinitions(\DateTimeInterface $asOf): array
    {
        if (DB::connection()->getDriverName() !== 'sqlsrv') {
            return $this->inner->listDueDefinitions($asOf);
        }

        return $this->listDueDefinitionsSqlsrv($asOf);
    }

    public function updateNextRunAt(int $definitionId, \DateTimeInterface $nextRunAt): bool
    {
        try {
            return $this->inner->updateNextRunAt($definitionId, $nextRunAt);
        } catch (\Throwable $exception) {
            if (! $this->shouldTryLegacyScheduleProcedure($exception)) {
                throw $exception;
            }

            return $this->updateScheduleLegacy(
                $definitionId,
                $nextRunAt->format('Y-m-d H:i:s'),
                null,
                null,
            );
        }
    }

    public function claimNextRunAt(
        int $definitionId,
        \DateTimeInterface $dueSlot,
        \DateTimeInterface $nextRunAt,
    ): bool {
        if (DB::connection()->getDriverName() === 'sqlsrv') {
            return $this->claimNextRunAtSqlsrv($definitionId, $dueSlot, $nextRunAt);
        }

        return $this->inner->claimNextRunAt($definitionId, $dueSlot, $nextRunAt);
    }

    public function clearNextRunAt(int $definitionId): bool
    {
        try {
            return $this->inner->clearNextRunAt($definitionId);
        } catch (\Throwable $exception) {
            if (! $this->shouldTryLegacyScheduleProcedure($exception)) {
                throw $exception;
            }

            return $this->updateScheduleLegacy($definitionId, null, null, null);
        }
    }

    public function updateLastRun(int $definitionId, \DateTimeInterface $lastRunAt, string $lastStatusCode): bool
    {
        try {
            return $this->inner->updateLastRun($definitionId, $lastRunAt, $lastStatusCode);
        } catch (\Throwable $exception) {
            if (! $this->shouldTryLegacyScheduleProcedure($exception)) {
                throw $exception;
            }

            return $this->updateScheduleLegacy(
                $definitionId,
                null,
                $lastRunAt->format('Y-m-d H:i:s'),
                $lastStatusCode,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    /**
     * @return list<array<string, mixed>>
     */
    private function listDueDefinitionsSqlsrv(\DateTimeInterface $asOf): array
    {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('EXEC dbo.pq_sp_task_definition_list_due @as_of = ?');
        $stmt->execute([$this->formatInstantAsUtc($asOf)]);
        $items = [];
        do {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($rows === []) {
                continue;
            }
            $first = $rows[0];
            if (array_key_exists('id', $first) || array_key_exists('taskDefinitionId', $first)) {
                foreach ($rows as $row) {
                    $items[] = $this->camelRow($row);
                }
            }
        } while ($stmt->nextRowset());

        return $items;
    }

    private function listDefinitionsSqlsrv(array $filters, int $page, int $pageSize): array
    {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare(
            'EXEC dbo.pq_sp_task_definition_list @is_active = ?, @process_id = ?, @page = ?, @page_size = ?'
        );
        $stmt->execute([
            $filters['isActive'] ?? null,
            $filters['processId'] ?? null,
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
            if (array_key_exists('id', $first) || array_key_exists('taskCode', $first)) {
                foreach ($rows as $row) {
                    $items[] = $this->camelRow($row);
                }
            }
        } while ($stmt->nextRowset());

        if ($items === [] && $total === 0) {
            return $this->inner->listDefinitions($filters, $page, $pageSize);
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

    private function shouldTryLegacyScheduleProcedure(\Throwable $exception): bool
    {
        if (DB::connection()->getDriverName() !== 'sqlsrv') {
            return false;
        }

        $message = strtolower($exception->getMessage());

        return str_contains($message, 'too many arguments')
            || str_contains($message, 'demasiados argumentos');
    }

    private function claimNextRunAtSqlsrv(
        int $definitionId,
        \DateTimeInterface $dueSlot,
        \DateTimeInterface $nextRunAt,
    ): bool {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare(
            'UPDATE pq_task_definition
                SET next_run_at = ?, updated_at = SYSUTCDATETIME()
             OUTPUT INSERTED.task_definition_id AS id
             WHERE task_definition_id = ?
               AND is_active = 1
               AND next_run_at IS NOT NULL
               AND next_run_at <= ?'
        );
        $stmt->execute([
            $this->formatScheduleWallClock($nextRunAt),
            $definitionId,
            $this->formatScheduleWallClock($dueSlot),
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    private function formatScheduleWallClock(\DateTimeInterface $instant): string
    {
        return $this->formatInstantAsUtc($instant);
    }

    private function formatInstantAsUtc(\DateTimeInterface $instant): string
    {
        if ($instant instanceof \DateTimeImmutable) {
            return $instant->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        }

        return \DateTimeImmutable::createFromInterface($instant)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d H:i:s');
    }

    private function updateScheduleLegacy(
        int $definitionId,
        ?string $nextRunAt,
        ?string $lastRunAt,
        ?string $lastStatusCode,
    ): bool {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare(
            'EXEC dbo.pq_sp_task_definition_update_schedule @task_definition_id = ?, @next_run_at = ?, @last_run_at = ?, @last_status_code = ?'
        );
        $stmt->execute([$definitionId, $nextRunAt, $lastRunAt, $lastStatusCode]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows === []) {
            while ($stmt->nextRowset()) {
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($rows !== []) {
                    break;
                }
            }
        }

        return $rows !== [] && (int) ($rows[0]['affected'] ?? 0) > 0;
    }
}
