<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PaqSuite\LaravelCore\Database\Seeders\TaskProcessBackupSeeder;

final class PqTaskProcessFrameworkSeeder extends Seeder
{
    public function run(): void
    {
        $connection = DB::connection();
        $schema = Schema::connection($connection->getName());

        if (! $schema->hasTable('PQ_TASK_PROCESS')) {
            $this->command?->warn('GEN-13: ejecutar la provisión del esquema/SP de tareas antes del seed.');

            return;
        }

        $definition = TaskProcessBackupSeeder::processDefinition();
        $now = DB::raw($connection->getDriverName() === 'sqlsrv' ? 'GETDATE()' : 'CURRENT_TIMESTAMP');

        $connection->table('PQ_TASK_PROCESS')->updateOrInsert(
            ['process_code' => $definition['processCode']],
            [
                'process_name' => $definition['processName'],
                'process_description' => $definition['notes'],
                'module_code' => $definition['moduleCode'],
                'module_name' => 'Framework',
                'handler_class' => $definition['handlerClass'],
                'is_active' => $definition['isActive'],
                'supports_manual_execution' => $definition['supportsManualExecution'],
                'supports_scheduled_execution' => $definition['supportsScheduledExecution'],
                'supports_simulation' => $definition['supportsSimulation'],
                'works_by_company' => $definition['worksByCompany'],
                'selection_strategy_auto' => $definition['selectionStrategyAuto'],
                'selection_strategy_manual' => $definition['selectionStrategyManual'],
                'idempotency_strategy' => $definition['idempotencyStrategy'],
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => self::class,
                'updated_by' => self::class,
            ],
        );

        $process = $connection->table('PQ_TASK_PROCESS')
            ->where('process_code', $definition['processCode'])
            ->first();

        if ($process === null) {
            throw new \RuntimeException('GEN-13: no se pudo resolver BACKUP_DATABASE.');
        }

        foreach ($definition['parameters'] as $parameter) {
            $connection->table('PQ_TASK_PROCESS_PARAMETER')->updateOrInsert(
                [
                    'process_id' => $process->process_id,
                    'parameter_code' => $parameter['parameterCode'],
                ],
                [
                    'parameter_name' => $parameter['parameterName'],
                    'parameter_description' => $parameter['parameterName'],
                    'data_type' => $parameter['dataType'],
                    'is_required' => $parameter['isRequired'],
                    'default_value_text' => isset($parameter['defaultValue']) ? (string) $parameter['defaultValue'] : null,
                    'display_order' => $parameter['displayOrder'],
                    'is_user_editable' => $parameter['isUserEditable'],
                    'is_multivalue' => $parameter['isMultivalue'],
                    'metadata_json' => $parameter['metadataJson'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }
}
