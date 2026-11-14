<?php

namespace App\Tasks;

use PaqSuite\LaravelCore\Tasks\Backup\RdsBackupExecutionLogSupport;
use DateTimeImmutable;
use DateTimeInterface;
use PaqSuite\LaravelCore\Tasks\Backup\BackupInstallationProfileResolver;
use PaqSuite\LaravelCore\Tasks\Backup\RdsBackupLock;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskExecutionRepository;
use PaqSuite\LaravelCore\Tasks\TaskExecutionStatuses;

/**
 * Cierra ejecuciones EN_EJECUCION huérfanas (timeout PHP, cierre de sesión, etc.).
 */
final class StaleTaskExecutionRecovery
{
    private const RECOVERY_MESSAGE = 'Recuperación automática: ejecución EN_EJECUCION expirada';

    public static function recoveryMessage(): string
    {
        return self::RECOVERY_MESSAGE;
    }

    public function __construct(
        private readonly TaskExecutionRepository $executionRepository,
        private readonly RdsBackupLock $backupLock,
        private readonly BackupInstallationProfileResolver $profileResolver,
        private readonly RdsBackupExecutionLogSupport $rdsLogSupport,
    ) {
    }

    /**
     * Recuperación agresiva para overlap / scheduler (no esperar timeout RDS completo).
     */
    public function recoverOverlaps(): int
    {
        $configured = (int) config('paqsuite.tasks.execution.overlapRecoveryMaxAgeSeconds', 0);
        if ($configured > 0) {
            return $this->recover($configured);
        }

        return $this->recover($this->defaultOverlapRecoveryMaxAgeSeconds());
    }

    public function recover(?int $maxAgeSeconds = null): int
    {
        $maxAgeSeconds = max(60, $maxAgeSeconds ?? $this->defaultMaxAgeSeconds());
        $cutoff = new DateTimeImmutable('-'.$maxAgeSeconds.' seconds');
        $recovered = 0;
        $page = 1;

        do {
            $items = $this->listOpenExecutions($page, 50);
            if ($items === []) {
                break;
            }
            foreach ($items as $row) {
                if ($this->recoverRowIfStale($row, $cutoff)) {
                    $recovered++;
                }
            }
            $page++;
        } while ($page <= 20);

        if ($recovered > 0) {
            $this->releaseInstallationBackupLock();
        }

        return $recovered;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function recoverRowIfStale(array $row, DateTimeInterface $cutoff): bool
    {
        $executionId = (int) ($row['id'] ?? $row['executionId'] ?? 0);
        if ($executionId <= 0) {
            return false;
        }

        $startedAt = $this->asDateTime($row['startedAt'] ?? null);
        if ($startedAt === null || $startedAt > $cutoff) {
            return false;
        }

        $backupOkMessage = (string) config('paqsuite.tasks.backup.backend', '') === 'rds'
            ? $this->rdsLogSupport->resolveRdsBackupSuccessMessage($executionId)
            : null;
        $statusCode = $backupOkMessage !== null
            ? TaskExecutionStatuses::EXITOSA
            : TaskExecutionStatuses::FALLIDA;
        $closeMessage = $backupOkMessage ?? self::RECOVERY_MESSAGE;

        try {
            $this->executionRepository->appendLog(
                $executionId,
                $statusCode === TaskExecutionStatuses::EXITOSA ? 'info' : 'warning',
                $closeMessage,
                ['startedAt' => $startedAt->format(DATE_ATOM)],
            );
        } catch (\Throwable) {
            // best-effort: algunos hosts tienen SP de log desalineado al template
        }

        return $this->executionRepository->updateExecutionStatus(
            $executionId,
            $statusCode,
            new DateTimeImmutable('now'),
            $closeMessage,
        );
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function closeStaleExecutionIfNeeded(array $row, DateTimeInterface $cutoff): bool
    {
        return $this->recoverRowIfStale($row, $cutoff);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function listOpenExecutions(int $page, int $pageSize): array
    {
        if ($this->executionRepository instanceof PartesTaskExecutionRepository) {
            return $this->executionRepository->listStaleExecutionsForRecovery($page, $pageSize);
        }

        $pageResult = $this->executionRepository->listExecutions(
            ['statusCode' => TaskExecutionStatuses::EN_EJECUCION],
            $page,
            $pageSize,
        );

        return $pageResult['items'] ?? [];
    }

    private function releaseInstallationBackupLock(): void
    {
        try {
            $profile = $this->profileResolver->resolve();
            $cliente = trim($profile->cliente);
            if ($cliente === '') {
                return;
            }
            $this->backupLock->release('tasks:backup:rds:'.$cliente);
        } catch (\Throwable) {
            // best-effort
        }
    }

    private function defaultMaxAgeSeconds(): int
    {
        $lockLease = max(60, (int) config('paqsuite.tasks.backup.lockLeaseSeconds', 900));
        $rdsTimeoutMinutes = max(1, (int) config('paqsuite.tasks.backup.rdsTimeoutMinutes', 120));

        return max($lockLease, ($rdsTimeoutMinutes * 60) + 300);
    }

    private function defaultOverlapRecoveryMaxAgeSeconds(): int
    {
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
