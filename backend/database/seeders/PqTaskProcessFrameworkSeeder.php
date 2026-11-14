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

        $processTable = $this->resolveTable($schema, ['pq_task_process', 'PQ_TASK_PROCESS']);
        if ($processTable === null) {
            $this->command?->warn('GEN-13: ejecutar la provisión del esquema/SP de tareas antes del seed.');

            return;
        }

        $parameterTable = $this->resolveTable($schema, ['pq_task_process_parameter', 'PQ_TASK_PROCESS_PARAMETER']);
        if ($parameterTable === null) {
            $this->command?->warn('GEN-13: falta tabla de parámetros de proceso.');

            return;
        }

        $definition = TaskProcessBackupSeeder::processDefinition();
        $now = $connection->getDriverName() === 'sqlsrv'
            ? DB::raw('GETDATE()')
            : now();

        $notes = $definition['notes'] ?? null;

        $processPayload = $this->filterToExistingColumns($schema, $processTable, [
            'process_name' => $definition['processName'],
            'process_description' => $notes,
            'notes' => $notes,
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
        ]);

        $connection->table($processTable)->updateOrInsert(
            ['process_code' => $definition['processCode']],
            $processPayload,
        );

        $process = $connection->table($processTable)
            ->where('process_code', $definition['processCode'])
            ->first();

        if ($process === null) {
            throw new \RuntimeException('GEN-13: no se pudo resolver BACKUP_DATABASE.');
        }

        $processId = $process->process_id;

        foreach ($definition['parameters'] as $parameter) {
            $parameterPayload = $this->filterToExistingColumns($schema, $parameterTable, [
                'parameter_name' => $parameter['parameterName'],
                'parameter_description' => $parameter['parameterName'],
                'data_type' => $parameter['dataType'],
                'ui_control_type' => $parameter['uiControlType'] ?? null,
                'is_required' => $parameter['isRequired'],
                'default_value_text' => $this->encodeDefaultValue($parameter['defaultValue'] ?? null),
                'allowed_values_source' => $parameter['allowedValuesSource'] ?? null,
                'display_order' => $parameter['displayOrder'],
                'is_user_editable' => $parameter['isUserEditable'],
                'is_multivalue' => $parameter['isMultivalue'],
                'metadata_json' => $parameter['metadataJson'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $connection->table($parameterTable)->updateOrInsert(
                [
                    'process_id' => $processId,
                    'parameter_code' => $parameter['parameterCode'],
                ],
                $parameterPayload,
            );
        }
    }

    /**
     * Persiste defaults tipados en NVARCHAR. `(string) false` en PHP es "" y tumba BOOL required.
     */
    private function encodeDefaultValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return (string) $value;
    }

    /**
     * @param  list<string>  $candidates
     */
    private function resolveTable(\Illuminate\Database\Schema\Builder $schema, array $candidates): ?string
    {
        foreach ($candidates as $name) {
            if ($schema->hasTable($name)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function filterToExistingColumns(
        \Illuminate\Database\Schema\Builder $schema,
        string $table,
        array $row,
    ): array {
        $columns = array_map(
            static fn (string $column): string => strtolower($column),
            $schema->getColumnListing($table),
        );

        $filtered = [];
        foreach ($row as $key => $value) {
            if (in_array(strtolower($key), $columns, true)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }
}
