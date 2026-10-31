<?php

namespace Tests\Unit;

use App\Tasks\SchedulingTaskDefinitionRepository;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskDefinitionRepository;
use Tests\TestCase;

class SchedulingTaskDefinitionRepositoryTest extends TestCase
{
    public function test_create_completa_next_run_at_cuando_falta(): void
    {
        $inner = new InMemoryTaskDefinitionRepository();
        $repo = new SchedulingTaskDefinitionRepository($inner);

        $id = $repo->createDefinition([
            'frequencyType' => 'HOURLY_INTERVAL',
            'frequencyInterval' => 4,
            'baseDate' => '2026-10-25',
            'baseTime' => '03:00:00',
        ]);

        $this->assertSame(1, $id);
        $this->assertArrayHasKey('nextRunAt', $inner->created[0]);
        $this->assertInstanceOf(DateTimeImmutable::class, $inner->created[0]['nextRunAt']);
        $this->assertGreaterThan(new DateTimeImmutable('now', new DateTimeZone('UTC')), $inner->created[0]['nextRunAt']);
    }

    public function test_update_completa_next_run_at_desde_la_definicion_existente(): void
    {
        $inner = new InMemoryTaskDefinitionRepository();
        $inner->definitions[7] = [
            'frequencyType' => 'HOURLY_INTERVAL',
            'frequencyInterval' => 4,
            'baseDate' => '2026-10-25',
            'baseTime' => '03:00:00',
        ];
        $repo = new SchedulingTaskDefinitionRepository($inner);

        $ok = $repo->updateDefinition(7, ['taskTitle' => 'backup diario']);

        $this->assertTrue($ok);
        $this->assertSame('backup diario', $inner->updated[7]['taskTitle']);
        $this->assertInstanceOf(DateTimeImmutable::class, $inner->updated[7]['nextRunAt']);
    }

    public function test_respeta_next_run_at_ya_informado(): void
    {
        $inner = new InMemoryTaskDefinitionRepository();
        $repo = new SchedulingTaskDefinitionRepository($inner);
        $fixed = new DateTimeImmutable('2026-10-28 03:00:00', new DateTimeZone('UTC'));

        $repo->createDefinition([
            'frequencyType' => 'HOURLY_INTERVAL',
            'frequencyInterval' => 4,
            'baseDate' => '2026-10-25',
            'baseTime' => '03:00:00',
            'nextRunAt' => $fixed,
        ]);

        $this->assertSame($fixed, $inner->created[0]['nextRunAt']);
    }
}

final class InMemoryTaskDefinitionRepository implements TaskDefinitionRepository
{
    /** @var list<array<string, mixed>> */
    public array $created = [];

    /** @var array<int, array<string, mixed>> */
    public array $updated = [];

    /** @var array<int, array<string, mixed>> */
    public array $definitions = [];

    public function listDefinitions(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        return ['items' => [], 'total' => 0];
    }

    public function findDefinitionById(int $definitionId): ?array
    {
        return $this->definitions[$definitionId] ?? null;
    }

    public function createDefinition(array $payload): int
    {
        $this->created[] = $payload;

        return 1;
    }

    public function updateDefinition(int $definitionId, array $payload): bool
    {
        $this->updated[$definitionId] = $payload;

        return true;
    }

    public function softDeactivate(int $definitionId): bool
    {
        return true;
    }

    public function listCompanyIds(int $definitionId): array
    {
        return [];
    }

    public function listDefinitionParameterValues(int $definitionId): array
    {
        return [];
    }

    public function listDueDefinitions(DateTimeInterface $asOf): array
    {
        return [];
    }

    public function updateNextRunAt(int $definitionId, DateTimeInterface $nextRunAt): bool
    {
        return true;
    }

    public function claimNextRunAt(
        int $definitionId,
        DateTimeInterface $dueSlot,
        DateTimeInterface $nextRunAt,
    ): bool {
        return true;
    }

    public function clearNextRunAt(int $definitionId): bool
    {
        return true;
    }

    public function updateLastRun(int $definitionId, DateTimeInterface $lastRunAt, string $lastStatusCode): bool
    {
        return true;
    }
}
