<?php

namespace Tests\Unit;

use App\Tasks\StaleTaskExecutionRecovery;
use DateTimeImmutable;
use PaqSuite\LaravelCore\Tasks\Backup\ConfigBackupInstallationProfileResolver;
use PaqSuite\LaravelCore\Tasks\Backup\RdsBackupExecutionLogSupport;
use PaqSuite\LaravelCore\Tasks\TaskExecutionStatuses;
use Tests\TestCase;

require_once __DIR__.'/RdsBackupTestDoubles.php';

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

        $logSupport = new RdsBackupExecutionLogSupport($repo, new FakeMonoRdsBackupGatewayForRecovery());
        $service = new StaleTaskExecutionRecovery($repo, $lock, $profileResolver, $logSupport);
        $count = $service->recover(3600);

        $this->assertSame(1, $count);
        $this->assertSame(TaskExecutionStatuses::FALLIDA, $repo->lastStatus);
        $this->assertSame(9, $repo->lastExecutionId);
        $this->assertSame('tasks:backup:rds:dev', $lock->releasedKey);
    }

    public function test_recover_overlaps_usa_umbral_corto(): void
    {
        $repo = new FakeTaskExecutionRepositoryForRecovery([
            [
                'id' => 11,
                'startedAt' => new DateTimeImmutable('-20 minutes'),
            ],
        ]);
        $lock = new FakeRdsBackupLock();
        config([
            'paqsuite.tasks.backup.backend' => 'rds',
            'paqsuite.tenancy' => 'single',
            'paqsuite.tasks.backup.lockLeaseSeconds' => 900,
            'paqsuite.tasks.execution.overlapRecoveryMaxAgeSeconds' => 0,
        ]);
        $profileResolver = new ConfigBackupInstallationProfileResolver(static fn (): string => 'dev');

        $logSupport = new RdsBackupExecutionLogSupport($repo, new FakeMonoRdsBackupGatewayForRecovery());
        $service = new StaleTaskExecutionRecovery($repo, $lock, $profileResolver, $logSupport);
        $count = $service->recoverOverlaps();

        $this->assertSame(1, $count);
    }

    public function test_recover_cierra_exitosa_si_rds_ya_termino(): void
    {
        $repo = new FakeTaskExecutionRepositoryForRecovery([
            [
                'id' => 42,
                'startedAt' => new DateTimeImmutable('-2 hours'),
            ],
        ]);
        $repo->logsByExecution[42] = [
            [
                'message' => 'RDS export started',
                'context' => ['taskId' => '9001'],
            ],
        ];
        $lock = new FakeRdsBackupLock();
        config(['paqsuite.tasks.backup.backend' => 'rds', 'paqsuite.tenancy' => 'single']);
        $profileResolver = new ConfigBackupInstallationProfileResolver(static fn (): string => 'paq');
        $logSupport = new RdsBackupExecutionLogSupport($repo, new FakeMonoRdsBackupGatewayForRecovery('success'));

        $service = new StaleTaskExecutionRecovery($repo, $lock, $profileResolver, $logSupport);
        $count = $service->recover(3600);

        $this->assertSame(1, $count);
        $this->assertSame(TaskExecutionStatuses::EXITOSA, $repo->lastStatus);
        $this->assertSame('rds backup ok', $repo->lastMessage);
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

        $logSupport = new RdsBackupExecutionLogSupport($repo, new FakeMonoRdsBackupGatewayForRecovery());
        $service = new StaleTaskExecutionRecovery($repo, $lock, $profileResolver, $logSupport);
        $count = $service->recover(3600);

        $this->assertSame(0, $count);
        $this->assertNull($repo->lastStatus);
    }
}
