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

    /** @var InstalacionResolver $resolver */
    $resolver = app(InstalacionResolver::class);

    try {
        $instalacion = $resolver->resolve($cliente, $proyecto);
    } catch (InstalacionInfraException $e) {
        throw new RuntimeException($e->getMessage(), 0, $e);
    }

    if ($instalacion === null) {
        throw new RuntimeException("Instalación no encontrada: {$cliente}/{$proyecto}");
    }

    if (! $instalacion->habilitado) {
        throw new RuntimeException("Instalación deshabilitada o vencida: {$cliente}/{$proyecto}");
    }

    /** @var InstalacionConnectionConfigurator $configurator */
    $configurator = app(InstalacionConnectionConfigurator::class);

    if (! $configurator->apply($instalacion)) {
        throw new RuntimeException("No se pudo aplicar conexión sqlsrv para {$cliente}/{$proyecto}");
    }

    return $instalacion;
}
