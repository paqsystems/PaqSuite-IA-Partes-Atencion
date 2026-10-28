<?php

namespace App\Tasks;

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

    public function __construct(
        private readonly TaskExecutionRepository $executionRepository,
        private readonly RdsBackupLock $backupLock,
        private readonly BackupInstallationProfileResolver $profileResolver,
    ) {
    }

    public function recover(?int $maxAgeSeconds = null): int
    {
        $maxAgeSeconds = max(60, $maxAgeSeconds ?? $this->defaultMaxAgeSeconds());
        $cutoff = new DateTimeImmutable('-'.$maxAgeSeconds.' seconds');
        $recovered = 0;
        $page = 1;

        do {
            $pageResult = $this->executionRepository->listExecutions(
                ['statusCode' => TaskExecutionStatuses::EN_EJECUCION],
                $page,
                50,
            );
            $items = $pageResult['items'] ?? [];
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

        try {
            $this->executionRepository->appendLog(
                $executionId,
                'warning',
                self::RECOVERY_MESSAGE,
                ['startedAt' => $startedAt->format(DATE_ATOM)],
            );
        } catch (\Throwable) {
            // best-effort: algunos hosts tienen SP de log desalineado al template
        }

        return $this->executionRepository->updateExecutionStatus(
            $executionId,
            TaskExecutionStatuses::FALLIDA,
            new DateTimeImmutable('now'),
            self::RECOVERY_MESSAGE,
        );
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
