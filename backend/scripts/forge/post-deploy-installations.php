<?php

declare(strict_types=1);

use PaqSuite\LaravelCore\Tenancy\InstalacionInfraException;
use PaqSuite\LaravelCore\Tenancy\InstalacionRecord;
use PaqSuite\LaravelCore\Tenancy\InstalacionResolver;

/**
 * Instalaciones a actualizar en post-deploy Forge (GEN-34).
 *
 * Por defecto: todas las filas SQL directo (sin agente) activas en EMPRESAS_CONEXION
 * para el proyecto. Un solo PAQSUITE_CLIENTE en .env no alcanza en hosts multi-tenant.
 *
 * Override: PAQSUITE_DEPLOY_CLIENTES=PAQ,DEMO (coma, sin espacios extra).
 * Desactivar multi: PAQSUITE_DEPLOY_UPDATE_ALL=0 → solo PAQSUITE_CLIENTE del .env.
 *
 * @return list<array{cliente: string, proyecto: string}>
 */
function resolveForgePostDeployInstallationTargets(string $proyecto): array
{
    $proyecto = trim($proyecto);
    if ($proyecto === '') {
        throw new RuntimeException('Proyecto vacío para post-deploy.');
    }

    $explicitClientes = trim(getenv('PAQSUITE_DEPLOY_CLIENTES') ?: '');
    if ($explicitClientes !== '') {
        $clientes = array_values(array_filter(array_map(
            static fn (string $c): string => trim($c),
            explode(',', $explicitClientes),
        )));

        return array_map(
            static fn (string $cliente): array => ['cliente' => $cliente, 'proyecto' => $proyecto],
            $clientes,
        );
    }

    $updateAll = strtolower(trim(getenv('PAQSUITE_DEPLOY_UPDATE_ALL') ?: '1'));
    if (in_array($updateAll, ['0', 'false', 'no', 'off'], true)) {
        $cliente = trim(getenv('PAQSUITE_CLIENTE') ?: (string) config('paqsuite.clienteCodigo', ''));
        if ($cliente === '') {
            throw new RuntimeException('PAQSUITE_DEPLOY_UPDATE_ALL=0 requiere PAQSUITE_CLIENTE.');
        }

        return [['cliente' => $cliente, 'proyecto' => $proyecto]];
    }

    $clientes = app(\App\Tenancy\EmpresasConexionSqlDirectClienteCatalog::class)
        ->listActiveClienteCodigos($proyecto);

    if ($clientes === []) {
        throw new RuntimeException("Sin instalaciones SQL directo en EMPRESAS_CONEXION para proyecto {$proyecto}.");
    }

    return array_map(
        static fn (string $cliente): array => ['cliente' => $cliente, 'proyecto' => $proyecto],
        $clientes,
    );
}

function bootstrapTenantSqlsrvForInstallation(string $cliente, string $proyecto): InstalacionRecord
{
    $cliente = trim($cliente);
    $proyecto = trim($proyecto);

    if ($cliente === '' || $proyecto === '') {
        throw new RuntimeException('cliente y proyecto son obligatorios.');
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

    /** @var \App\Tenancy\InstalacionConnectionConfigurator $configurator */
    $configurator = app(\App\Tenancy\InstalacionConnectionConfigurator::class);

    if (! $configurator->apply($instalacion)) {
        throw new RuntimeException("No se pudo aplicar conexión sqlsrv para {$cliente}/{$proyecto}");
    }

    return $instalacion;
}
