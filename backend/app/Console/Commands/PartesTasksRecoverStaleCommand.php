<?php

namespace App\Console\Commands;

use App\Tasks\StaleTaskExecutionRecovery;
use App\Tenancy\CliInstalacionContext;
use Illuminate\Console\Command;

/**
 * Cierra ejecuciones EN_EJECUCION huérfanas (overlap / scheduler).
 */
final class PartesTasksRecoverStaleCommand extends Command
{
    protected $signature = 'partes:tasks-recover-stale
                            {--cliente= : Código tenant (default config paqsuite.clienteCodigo)}
                            {--full : Usar umbral largo (backup RDS) en lugar de overlap}';

    protected $description = 'Recupera ejecuciones GEN-13 EN_EJECUCION expiradas en la BD del tenant';

    public function handle(
        CliInstalacionContext $cliInstalacion,
        StaleTaskExecutionRecovery $staleRecovery,
    ): int {
        $cliente = trim((string) ($this->option('cliente') ?: $cliInstalacion->defaultClienteCodigo()));
        if ($cliente === '') {
            $this->error('Definí PAQSUITE_CLIENTE en el entorno del sitio (Forge) o usá --cliente=');

            return self::FAILURE;
        }

        $record = $cliInstalacion->applyForCliente($cliente);
        if ($record === null) {
            $this->error("No se pudo resolver/aplicar instalación para cliente «{$cliente}».");

            return self::FAILURE;
        }

        $this->line(sprintf('Tenant %s → BD %s', $record->cliente, (string) $record->databaseName));

        $recovered = $this->option('full')
            ? $staleRecovery->recover()
            : $staleRecovery->recoverOverlaps();

        $this->info("Recuperadas {$recovered} ejecución(es).");

        return self::SUCCESS;
    }
}
