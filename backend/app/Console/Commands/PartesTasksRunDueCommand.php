<?php

namespace App\Console\Commands;

use App\Tasks\StaleTaskExecutionRecovery;
use App\Tenancy\CliInstalacionContext;
use App\Tenancy\SchedulerClienteResolver;
use Illuminate\Console\Command;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskDefinitionRepository;
use PaqSuite\LaravelCore\Tasks\TaskExecutionService;

/**
 * GEN-13 host: recorre instalaciones SQL del proyecto, recupera zombies y ejecuta due.
 */
final class PartesTasksRunDueCommand extends Command
{
    protected $signature = 'partes:tasks-run-due
                            {--dry-run : Lista definiciones due sin ejecutar}
                            {--cliente= : Un solo tenant (si se omite, todos los SQL directo del proyecto)}
                            {--skip-recover : No cerrar ejecuciones EN_EJECUCION expiradas}';

    protected $description = 'Ejecuta tareas programadas due en cada BD tenant SQL directo del proyecto';

    public function handle(
        CliInstalacionContext $cliInstalacion,
        SchedulerClienteResolver $clienteResolver,
        StaleTaskExecutionRecovery $staleRecovery,
        TaskDefinitionRepository $definitionRepository,
        TaskExecutionService $executionService,
    ): int {
        $clientes = $clienteResolver->resolve($this->option('cliente') ? (string) $this->option('cliente') : null);
        if ($clientes === []) {
            $this->error('No hay tenants para el scheduler: definí --cliente=, PAQSUITE_TASKS_CLIENTES, EMPRESAS_CONEXION o PAQSUITE_CLIENTE.');

            return self::FAILURE;
        }

        $exit = self::SUCCESS;
        foreach ($clientes as $cliente) {
            if (! $this->runForCliente(
                $cliente,
                $cliInstalacion,
                $staleRecovery,
                $definitionRepository,
                $executionService,
            )) {
                $exit = self::FAILURE;
            }
        }

        return $exit;
    }

    private function runForCliente(
        string $cliente,
        CliInstalacionContext $cliInstalacion,
        StaleTaskExecutionRecovery $staleRecovery,
        TaskDefinitionRepository $definitionRepository,
        TaskExecutionService $executionService,
    ): bool {
        $record = $cliInstalacion->applyForCliente($cliente);
        if ($record === null) {
            $this->error("No se pudo resolver/aplicar instalación para cliente «{$cliente}».");

            return false;
        }

        $this->line(sprintf(
            'Tenant %s → BD %s',
            $record->cliente,
            (string) $record->databaseName,
        ));

        if (! $this->option('skip-recover')) {
            $recovered = $staleRecovery->recoverOverlaps();
            if ($recovered > 0) {
                $this->warn("Recuperadas {$recovered} ejecución(es) EN_EJECUCION expirada(s) (overlap).");
            }
        }

        $tzName = (string) config('paqsuite.tasks.scheduleTimezone', config('app.timezone', 'UTC'));
        $scheduleTz = new \DateTimeZone($tzName !== '' ? $tzName : 'UTC');
        $asOf = new \DateTimeImmutable('now', $scheduleTz);

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

            return true;
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

        return true;
    }
}
