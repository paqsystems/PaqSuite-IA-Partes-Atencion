<?php

namespace App\Tenancy;

use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Tenancy\ClienteCodeNormalizer;
use Throwable;

/**
 * Clientes SQL directo (sin agente-gateway) activos en EMPRESAS_CONEXION.
 *
 * Misma regla que el post-deploy GEN-34: un solo PAQSUITE_CLIENTE no cubre
 * un Forge que atiende varios tenants del proyecto.
 */
class EmpresasConexionSqlDirectClienteCatalog
{
    /**
     * @return list<string>
     */
    public function listActiveClienteCodigos(string $proyecto): array
    {
        $proyecto = trim($proyecto);
        if ($proyecto === '') {
            return [];
        }

        $centralConnection = (string) config('paqsuite.instalacion.centralConnection', 'paqsuite_central');

        try {
            $rows = DB::connection($centralConnection)->select(
                'SELECT ec.cliente
                 FROM dbo.EMPRESAS_CONEXION AS ec WITH (NOLOCK)
                 WHERE ec.proyecto = ?
                   AND ec.activo = 1
                   AND (ec.agent_id IS NULL OR LTRIM(RTRIM(ec.agent_id)) = N\'\')
                   AND (ec.client_id IS NULL OR LTRIM(RTRIM(ec.client_id)) = N\'\')
                 ORDER BY ec.cliente',
                [$proyecto],
            );
        } catch (Throwable) {
            return [];
        }

        $clientes = [];
        foreach ($rows as $row) {
            $cliente = ClienteCodeNormalizer::normalize((string) ($row->cliente ?? ''));
            if ($cliente === '' || in_array($cliente, $clientes, true)) {
                continue;
            }
            $clientes[] = $cliente;
        }

        return $clientes;
    }
}
