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
        return $this->inner->listDueDefinitions($asOf);
    }

    public function updateNextRunAt(int $definitionId, \DateTimeInterface $nextRunAt): bool
    {
        return $this->inner->updateNextRunAt($definitionId, $nextRunAt);
    }

    public function updateLastRun(int $definitionId, \DateTimeInterface $lastRunAt, string $lastStatusCode): bool
    {
        return $this->inner->updateLastRun($definitionId, $lastRunAt, $lastStatusCode);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: list<array<string, mixed>>, total: int}
     */
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
}
