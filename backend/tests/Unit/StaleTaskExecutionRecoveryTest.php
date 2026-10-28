<?php

namespace Tests\Unit;

use App\Tasks\StaleTaskExecutionRecovery;
use DateTimeImmutable;
use PaqSuite\LaravelCore\Tasks\Backup\ConfigBackupInstallationProfileResolver;
use PaqSuite\LaravelCore\Tasks\Backup\RdsBackupLock;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskExecutionRepository;
use PaqSuite\LaravelCore\Tasks\TaskExecutionStatuses;
use Tests\TestCase;

class StaleTaskExecutionRecoveryTest extends TestCase
{
    public function test_recupera_ejecucion_en_ejecucion_antigua(): void
    {
        $repo = new FakeTaskExecutionRepositoryForRecovery([
            [
                'id' => 9,
                'startedAt' => new DateTimeImmutable('-2 hours'),
            ],
        ]);
        $lock = new FakeRdsBackupLock();
        config(['paqsuite.tasks.backup.backend' => 'rds', 'paqsuite.tenancy' => 'single']);
        $profileResolver = new ConfigBackupInstallationProfileResolver(static fn (): string => 'dev');

        $service = new StaleTaskExecutionRecovery($repo, $lock, $profileResolver);
        $count = $service->recover(3600);

        $this->assertSame(1, $count);
        $this->assertSame(TaskExecutionStatuses::FALLIDA, $repo->lastStatus);
        $this->assertSame(9, $repo->lastExecutionId);
        $this->assertSame('tasks:backup:rds:dev', $lock->releasedKey);
    }

    public function test_no_toca_ejecucion_reciente(): void
    {
        $repo = new FakeTaskExecutionRepositoryForRecovery([
            [
                'id' => 3,
                'startedAt' => new DateTimeImmutable('-5 minutes'),
            ],
        ]);
        $lock = new FakeRdsBackupLock();
        config(['paqsuite.tasks.backup.backend' => 'rds', 'paqsuite.tenancy' => 'single']);
        $profileResolver = new ConfigBackupInstallationProfileResolver(static fn (): string => 'dev');

        $service = new StaleTaskExecutionRecovery($repo, $lock, $profileResolver);
        $count = $service->recover(3600);

        $this->assertSame(0, $count);
        $this->assertNull($repo->lastStatus);
    }
}

final class FakeTaskExecutionRepositoryForRecovery implements TaskExecutionRepository
{
    public ?string $lastStatus = null;

    public ?int $lastExecutionId = null;

    /**
     * @param  list<array{id: int, startedAt: DateTimeImmutable}>  $running
     */
    public function __construct(
        private array $running,
    ) {
    }

    public function createBatch(array $batch): int
    {
        return 0;
    }

    public function createExecution(array $execution): int
    {
        return 0;
    }

    public function saveParameterSnapshot(int $executionId, array $parameters): void
    {
    }

    public function appendLog(int $executionId, string $level, string $message, array $context = []): void
    {
    }

    public function updateExecutionStatus(
        int $executionId,
        string $statusCode,
        ?\DateTimeInterface $finishedAt = null,
        ?string $message = null,
        bool $isOverlappedSkip = false,
    ): bool {
        $this->lastExecutionId = $executionId;
        $this->lastStatus = $statusCode;
        $this->running = array_values(array_filter(
            $this->running,
            static fn (array $row): bool => (int) ($row['id'] ?? 0) !== $executionId,
        ));

        return true;
    }

    public function hasRunningOverlap(int $definitionId, int $companyId): bool
    {
        return false;
    }

    public function listExecutions(array $filters = [], int $page = 1, int $pageSize = 25): array
    {
        if (($filters['statusCode'] ?? '') !== TaskExecutionStatuses::EN_EJECUCION) {
            return ['items' => [], 'total' => 0];
        }

        return ['items' => $this->running, 'total' => count($this->running)];
    }

    public function findExecutionById(int $executionId): ?array
    {
        return null;
    }

    public function listLogs(int $executionId): array
    {
        return [];
    }

    public function purgeOlderThan(int $olderThanDays, int $chunkSize = 1000): int
    {
        return 0;
    }
}

final class FakeRdsBackupLock implements RdsBackupLock
{
    public ?string $releasedKey = null;

    public function acquire(string $key, int $leaseSeconds): bool
    {
        return true;
    }

    public function renew(string $key, int $leaseSeconds): bool
    {
        return true;
    }

    public function release(string $key): void
    {
        $this->releasedKey = $key;
    }
}
