<?php

namespace Tests\Unit;

use DateTimeImmutable;
use PaqSuite\LaravelCore\Tasks\Backup\ConfigBackupInstallationProfileResolver;
use PaqSuite\LaravelCore\Tasks\Backup\RdsBackupExecutionLogSupport;
use PaqSuite\LaravelCore\Tasks\Backup\RdsDeferredBackupExecutionPoller;
use PaqSuite\LaravelCore\Tasks\TaskExecutionStatuses;
use Tests\TestCase;

require_once __DIR__.'/RdsBackupTestDoubles.php';

class BackupRdsExecutionPollerTest extends TestCase
{
    public function test_poll_cierra_exitosa_cuando_rds_termino(): void
    {
        $repo = new FakeTaskExecutionRepositoryForRecovery([
            [
                'id' => 5,
                'startedAt' => new DateTimeImmutable('-2 minutes'),
            ],
        ]);
        $repo->logsByExecution[5] = [
            [
                'message' => 'RDS export started',
                'context' => ['taskId' => 't-5'],
            ],
        ];
        $lock = new FakeRdsBackupLock();
        config([
            'paqsuite.tasks.backup.backend' => 'rds',
            'paqsuite.tasks.backup.deferManualRdsPolling' => true,
            'paqsuite.tenancy' => 'single',
        ]);
        $profileResolver = new ConfigBackupInstallationProfileResolver(static fn (): string => 'paq');
        $gateway = new FakeMonoRdsBackupGatewayForRecovery('success');
        $logSupport = new RdsBackupExecutionLogSupport($repo, $gateway);
        $poller = new RdsDeferredBackupExecutionPoller(
            $repo,
            $gateway,
            $logSupport,
            $lock,
            $profileResolver,
            deferManualRdsPolling: true,
            backupBackend: 'rds',
        );

        $closed = $poller->pollOpenRdsBackups();

        $this->assertSame(1, $closed);
        $this->assertSame(TaskExecutionStatuses::EXITOSA, $repo->lastStatus);
        $this->assertSame('rds backup ok', $repo->lastMessage);
        $this->assertSame('tasks:backup:rds:paq', $lock->releasedKey);
    }

    public function test_poll_no_cierra_si_rds_sigue_running(): void
    {
        $repo = new FakeTaskExecutionRepositoryForRecovery([
            [
                'id' => 7,
                'startedAt' => new DateTimeImmutable('-1 minute'),
            ],
        ]);
        $repo->logsByExecution[7] = [
            [
                'message' => 'RDS export started',
                'context' => ['taskId' => 't-7'],
            ],
        ];
        config([
            'paqsuite.tasks.backup.backend' => 'rds',
            'paqsuite.tasks.backup.deferManualRdsPolling' => true,
        ]);
        $gateway = new FakeMonoRdsBackupGatewayForRecovery('running');
        $logSupport = new RdsBackupExecutionLogSupport($repo, $gateway);
        $poller = new RdsDeferredBackupExecutionPoller(
            $repo,
            $gateway,
            $logSupport,
            new FakeRdsBackupLock(),
            new ConfigBackupInstallationProfileResolver(static fn (): string => 'paq'),
            deferManualRdsPolling: true,
            backupBackend: 'rds',
        );

        $this->assertSame(0, $poller->pollOpenRdsBackups());
        $this->assertNull($repo->lastStatus);
    }
}
