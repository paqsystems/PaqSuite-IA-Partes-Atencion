<?php

namespace App\Tasks\Backup;

use PaqSuite\LaravelCore\Tasks\Backup\BackupCompanyTarget;
use PaqSuite\LaravelCore\Tasks\Backup\BackupCompanyTargetResolver;
use PaqSuite\LaravelCore\Tasks\Backup\EmpresaAdminBackupCompanyTargetResolver;

/**
 * MONO unified: si la empresa no trae nombre_bd en catálogo, usar la BD operativa del tenant.
 */
final class PartesMonoBackupCompanyTargetResolver implements BackupCompanyTargetResolver
{
    public function __construct(
        private readonly EmpresaAdminBackupCompanyTargetResolver $inner,
    ) {
    }

    public function listTargets(): array
    {
        $targets = $this->inner->listTargets();
        if ($targets !== []) {
            return $targets;
        }

        $fallback = $this->fallbackTarget(1);
        if ($fallback === null) {
            return [];
        }

        return [$fallback];
    }

    public function resolve(int $companyId): BackupCompanyTarget
    {
        try {
            return $this->inner->resolve($companyId);
        } catch (\InvalidArgumentException) {
            $fallback = $this->fallbackTarget($companyId);
            if ($fallback === null) {
                throw new \InvalidArgumentException('Backup company target not found');
            }

            return $fallback;
        }
    }

    private function fallbackTarget(int $companyId): ?BackupCompanyTarget
    {
        if (! $this->isMonoUnified()) {
            return null;
        }

        $databaseName = trim((string) config(
            'paqsuite.installation.database_name',
            config('database.connections.'.config('database.default').'.database', ''),
        ));
        if ($databaseName === '') {
            return null;
        }

        return new BackupCompanyTarget(
            $companyId > 0 ? $companyId : 1,
            $databaseName,
            $databaseName,
        );
    }

    private function isMonoUnified(): bool
    {
        $tenancy = strtolower(trim((string) config('paqsuite.tenancy', 'single')));
        $dbMode = strtolower(trim((string) config('paqsuite.db', 'unified')));

        return $tenancy === 'single' && $dbMode === 'unified';
    }
}
