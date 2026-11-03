<?php

declare(strict_types=1);

use App\Tenancy\InstalacionConnectionConfigurator;
use PaqSuite\LaravelCore\Tenancy\InstalacionInfraException;
use PaqSuite\LaravelCore\Tenancy\InstalacionRecord;
use PaqSuite\LaravelCore\Tenancy\InstalacionResolver;

/**
 * Resuelve PAQSUITE_CLIENTE/PROYECTO y pisa database.default (sqlsrv) como en HTTP.
 *
 * @return InstalacionRecord
 */
function bootstrapTenantSqlsrvFromEnv(): InstalacionRecord
{
    $cliente = trim(getenv('PAQSUITE_CLIENTE') ?: (string) config('paqsuite.clienteCodigo', ''));
    $proyecto = trim(getenv('PAQSUITE_PROYECTO') ?: (string) config('paqsuite.proyecto', 'partesatencion'));

    if ($cliente === '' || $proyecto === '') {
        throw new RuntimeException('PAQSUITE_CLIENTE y PAQSUITE_PROYECTO son obligatorios.');
    }

    require_once __DIR__ . '/post-deploy-installations.php';

    return bootstrapTenantSqlsrvForInstallation($cliente, $proyecto);
}
