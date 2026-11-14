<?php

namespace Tests\Unit;

use DateTimeImmutable;
use PaqSuite\LaravelCore\Tasks\Backup\MonoRdsBackupGateway;
use PaqSuite\LaravelCore\Tasks\Backup\RdsBackupLock;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskExecutionRepository;
use PaqSuite\LaravelCore\Tasks\TaskExecutionStatuses;

final class FakeMonoRdsBackupGatewayForRecovery implements MonoRdsBackupGateway
{
    public function __construct(
        private readonly string $status = 'running',
    ) {
    }

    public function startExport(
        string $databaseName,
        string $s3Arn,
        string $filePrefix,
        bool $overwrite = true,
    ): array {
        return ['taskId' => '1'];
    }

    public function getTaskStatus(string $taskId): array
    {
        return ['status' => $this->status];
    }
}

final class FakeTaskExecutionRepositoryForRecovery implements TaskExecutionRepository
{
    public ?string $lastStatus = null;

    public ?string $lastMessage = null;

    public ?int $lastExecutionId = null;

    /** @var array<int, list<array<string, mixed>>> */
    public array $logsByExecution = [];

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
        $this->lastMessage = $message;
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
        return $this->logsByExecution[$executionId] ?? [];
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
