<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskDefinitionRepository;

final class PartesTasksBackfillNextRunAtCommand extends Command
{
    protected $signature = 'partes:tasks-backfill-next-run-at
                            {--dry-run : Solo listar definiciones a actualizar}';

    protected $description = 'Completa next_run_at en definiciones GEN-13 activas que lo tengan NULL (CC PQ #10)';

    public function handle(TaskDefinitionRepository $definitions): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;
        $page = 1;
        $pageSize = 100;

        do {
            $pageResult = $definitions->listDefinitions(['isActive' => true], $page, $pageSize);
            $items = $pageResult['items'] ?? [];
            foreach ($items as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $nextRunAt = $row['nextRunAt'] ?? $row['next_run_at'] ?? null;
                if ($this->hasNextRunAt($nextRunAt)) {
                    continue;
                }
                $definitionId = (int) ($row['id'] ?? 0);
                if ($definitionId <= 0) {
                    continue;
                }
                $title = (string) ($row['taskTitle'] ?? $row['taskCode'] ?? $definitionId);
                if ($dryRun) {
                    $this->line("DRY-RUN definición #{$definitionId} ({$title}): nextRunAt NULL → recalcular");
                    $updated++;

                    continue;
                }
                $definitions->updateDefinition($definitionId, [
                    'frequencyType' => $row['frequencyType'] ?? $row['frequency_type'] ?? null,
                    'frequencyInterval' => $row['frequencyInterval'] ?? $row['frequency_interval'] ?? null,
                    'frequencyDaysMask' => $row['frequencyDaysMask'] ?? $row['frequency_days_mask'] ?? null,
                    'baseDate' => $row['baseDate'] ?? $row['base_date'] ?? null,
                    'baseTime' => $row['baseTime'] ?? $row['base_time'] ?? null,
                    'isActive' => $row['isActive'] ?? $row['is_active'] ?? true,
                ]);
                $this->info("Actualizada definición #{$definitionId} ({$title})");
                $updated++;
            }
            $total = (int) ($pageResult['total'] ?? 0);
            $page++;
        } while (($page - 1) * $pageSize < $total);

        $this->info($dryRun
            ? "Definiciones pendientes de backfill: {$updated}"
            : "Definiciones actualizadas: {$updated}");

        return self::SUCCESS;
    }

    private function hasNextRunAt(mixed $value): bool
    {
        if ($value instanceof \DateTimeInterface) {
            return true;
        }

        return is_string($value) && trim($value) !== '';
    }
}
