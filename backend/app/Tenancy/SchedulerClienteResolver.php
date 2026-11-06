<?php

namespace App\Tenancy;

use PaqSuite\LaravelCore\Tenancy\ClienteCodeNormalizer;

/**
 * Tenants sobre los que corre el scheduler GEN-13 en un host multi-instalación.
 *
 * Precedencia: --cliente → PAQSUITE_TASKS_CLIENTES → EMPRESAS_CONEXION SQL
 * directo del proyecto → PAQSUITE_CLIENTE (fallback un sitio / PHPUnit).
 */
final class SchedulerClienteResolver
{
    public function __construct(
        private readonly EmpresasConexionSqlDirectClienteCatalog $catalog,
    ) {
    }

    /**
     * @return list<string>
     */
    public function resolve(?string $explicitCliente = null): array
    {
        $explicit = ClienteCodeNormalizer::normalize($explicitCliente);
        if ($explicit !== '') {
            return [$explicit];
        }

        $fromEnv = $this->parseCsv((string) config('paqsuite.tasks.schedulerClientes', ''));
        if ($fromEnv !== []) {
            return $fromEnv;
        }

        $proyecto = trim((string) config('paqsuite.proyecto', ''));
        if ($proyecto !== '') {
            $fromSql = $this->catalog->listActiveClienteCodigos($proyecto);
            if ($fromSql !== []) {
                return $fromSql;
            }
        }

        $fallback = ClienteCodeNormalizer::normalize((string) config('paqsuite.clienteCodigo', ''));

        return $fallback !== '' ? [$fallback] : [];
    }

    /**
     * @return list<string>
     */
    private function parseCsv(string $raw): array
    {
        $clientes = [];
        foreach (explode(',', $raw) as $part) {
            $cliente = ClienteCodeNormalizer::normalize($part);
            if ($cliente === '' || in_array($cliente, $clientes, true)) {
                continue;
            }
            $clientes[] = $cliente;
        }

        return $clientes;
    }
}
