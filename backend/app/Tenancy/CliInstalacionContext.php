<?php

namespace App\Tenancy;

use PaqSuite\LaravelCore\Support\PaqSuiteConfig;
use PaqSuite\LaravelCore\Tenancy\ClienteCodeNormalizer;
use PaqSuite\LaravelCore\Tenancy\InstalacionRecord;
use PaqSuite\LaravelCore\Tenancy\InstalacionResolver;

/**
 * Resuelve X-Paq-Cliente / PAQSUITE_CLIENTE en CLI y aplica la BD tenant (GEN-19).
 */
final class CliInstalacionContext
{
    public function __construct(
        private readonly InstalacionResolver $instalacionResolver,
        private readonly InstalacionConnectionConfigurator $connectionConfigurator,
    ) {
    }

    public function resolveRecord(?string $clienteCodigo = null): ?InstalacionRecord
    {
        $cliente = ClienteCodeNormalizer::normalize($clienteCodigo ?? $this->defaultClienteCodigo());
        if ($cliente === '') {
            return null;
        }

        $proyecto = trim(PaqSuiteConfig::proyecto());
        if ($proyecto === '') {
            return null;
        }

        $record = $this->instalacionResolver->resolve($cliente, $proyecto);
        if ($record === null || ! $record->habilitado || $record->isGatewayMode()) {
            return null;
        }

        return $record;
    }

    public function applyForCliente(?string $clienteCodigo = null): ?InstalacionRecord
    {
        $record = $this->resolveRecord($clienteCodigo);
        if ($record === null) {
            return null;
        }

        if (! $this->connectionConfigurator->apply($record)) {
            return null;
        }

        return $record;
    }

    public function defaultClienteCodigo(): string
    {
        return trim((string) config('paqsuite.clienteCodigo', ''));
    }
}
