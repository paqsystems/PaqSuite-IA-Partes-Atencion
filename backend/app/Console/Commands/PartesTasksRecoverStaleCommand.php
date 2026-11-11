<?php

namespace App\Console\Commands;

use PaqSuite\LaravelCore\Tasks\Backup\RdsDeferredBackupExecutionPoller;
use App\Tasks\StaleTaskExecutionRecovery;
use App\Tenancy\CliInstalacionContext;
use App\Tenancy\SchedulerClienteResolver;
use Illuminate\Console\Command;

/**
 * Cierra ejecuciones EN_EJECUCION huérfanas (overlap / scheduler).
 */
final class PartesTasksRecoverStaleCommand extends Command
{
    protected $signature = 'partes:tasks-recover-stale
                            {--cliente= : Un solo tenant (si se omite, todos los SQL directo del proyecto)}
                            {--full : Usar umbral largo (backup RDS) en lugar de overlap}';

    protected $description = 'Recupera ejecuciones GEN-13 EN_EJECUCION expiradas en cada BD tenant SQL directo';

    public function handle(
        CliInstalacionContext $cliInstalacion,
        SchedulerClienteResolver $clienteResolver,
        RdsDeferredBackupExecutionPoller $rdsPoller,
        StaleTaskExecutionRecovery $staleRecovery,
    ): int {
        $clientes = $clienteResolver->resolve($this->option('cliente') ? (string) $this->option('cliente') : null);
        if ($clientes === []) {
            $this->error('No hay tenants: definí --cliente=, PAQSUITE_TASKS_CLIENTES, EMPRESAS_CONEXION o PAQSUITE_CLIENTE.');

            return self::FAILURE;
        }

        $exit = self::SUCCESS;
        $total = 0;
        foreach ($clientes as $cliente) {
            $record = $cliInstalacion->applyForCliente($cliente);
            if ($record === null) {
                $this->error("No se pudo resolver/aplicar instalación para cliente «{$cliente}».");
                $exit = self::FAILURE;
                continue;
            }

            $this->line(sprintf('Tenant %s → BD %s', $record->cliente, (string) $record->databaseName));

            $polled = $rdsPoller->pollOpenRdsBackups();
            if ($polled > 0) {
                $this->info("Backups RDS cerrados por scheduler: {$polled}.");
            }

            $recovered = $this->option('full')
                ? $staleRecovery->recover()
                : $staleRecovery->recoverOverlaps();
            $total += $recovered;
            $this->info("Recuperadas {$recovered} ejecución(es).");
        }

        if (count($clientes) > 1) {
            $this->info("Total recuperadas: {$total}.");
        }

        return $exit;
    }
}
