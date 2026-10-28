<?php

namespace App\Console\Commands;

use App\Tasks\StaleTaskExecutionRecovery;
use App\Tenancy\CliInstalacionContext;
use Illuminate\Console\Command;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskDefinitionRepository;
use PaqSuite\LaravelCore\Tasks\TaskExecutionService;

/**
 * GEN-13 host: resuelve tenant (PAQSUITE_CLIENTE), recupera zombies y ejecuta due.
 */
final class PartesTasksRunDueCommand extends Command
{
    protected $signature = 'partes:tasks-run-due
                            {--dry-run : Lista definiciones due sin ejecutar}
                            {--cliente= : Código tenant (default config paqsuite.clienteCodigo)}
                            {--skip-recover : No cerrar ejecuciones EN_EJECUCION expiradas}';

    protected $description = 'Ejecuta tareas programadas due en la BD del tenant (PAQSUITE_CLIENTE)';

    public function handle(
        CliInstalacionContext $cliInstalacion,
        StaleTaskExecutionRecovery $staleRecovery,
        TaskDefinitionRepository $definitionRepository,
        TaskExecutionService $executionService,
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

        $this->line(sprintf(
            'Tenant %s → BD %s',
            $record->cliente,
            (string) $record->databaseName,
        ));

        if (! $this->option('skip-recover')) {
            $recovered = $staleRecovery->recover();
            if ($recovered > 0) {
                $this->warn("Recuperadas {$recovered} ejecución(es) EN_EJECUCION expirada(s).");
            }
        }

        $asOf = new \DateTimeImmutable('now');

        if ($this->option('dry-run')) {
            $due = $definitionRepository->listDueDefinitions($asOf);
            $this->info('Due definitions: '.count($due));
            foreach ($due as $row) {
                $this->line(sprintf(
                    '  #%d %s next=%s',
                    (int) ($row['id'] ?? $row['taskDefinitionId'] ?? 0),
                    (string) ($row['taskTitle'] ?? ''),
                    (string) ($row['nextRunAt'] ?? ''),
                ));
            }

            return self::SUCCESS;
        }

        $batches = $executionService->runDue($asOf);
        $this->info('Executed batches: '.count($batches));
        foreach ($batches as $batch) {
            $this->line(sprintf(
                '  definition=%d batch=%d',
                (int) ($batch['definitionId'] ?? 0),
                (int) ($batch['batchId'] ?? 0),
            ));
        }

        return self::SUCCESS;
    }
}
