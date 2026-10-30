<?php

namespace Tests\Unit;

use App\Tasks\Backup\PartesMonoBackupCompanyTargetResolver;
use PaqSuite\LaravelCore\Tasks\Backup\BackupCompanyTarget;
use PaqSuite\LaravelCore\Tasks\Backup\EmpresaAdminBackupCompanyTargetResolver;
use Tests\TestCase;

final class PartesMonoBackupCompanyTargetResolverTest extends TestCase
{
    public function testResolveUsesTenantDatabaseWhenCatalogHasNoNombreBd(): void
    {
        config([
            'paqsuite.tenancy' => 'single',
            'paqsuite.db' => 'unified',
            'paqsuite.installation.database_name' => 'paqsystems_partesatencion_dev',
        ]);

        $inner = new class implements \PaqSuite\LaravelCore\Security\EmpresaAdminRepository {
            public function listAll(): array
            {
                return [['id' => 1, 'nombreEmpresa' => 'Demo']];
            }

            public function findById(int $id): ?array
            {
                return ['id' => 1, 'nombreEmpresa' => 'Demo'];
            }

            public function update(int $id, array $data): ?array
            {
                return null;
            }

            public function listCandidatosErp(): array
            {
                return [];
            }

            public function createFromAlta(array $data): array
            {
                return [];
            }
        };

        $resolver = new PartesMonoBackupCompanyTargetResolver(
            new EmpresaAdminBackupCompanyTargetResolver($inner),
        );

        $target = $resolver->resolve(1);

        $this->assertInstanceOf(BackupCompanyTarget::class, $target);
        $this->assertSame('paqsystems_partesatencion_dev', $target->databaseName);
    }
}
