<?php

namespace App\Services\Emissions;

use App\Http\Middleware\EnsurePartesFunctionalProfile;
use App\Repositories\Sp\SpCaller;
use App\Services\Partes\PartesTareaException;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionDatasetPort;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionContext;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionDataset;
use PaqSuite\LaravelCore\Emissions\EmissionException;

final class PartesPaqueteHorasEmissionPort implements EmissionDatasetPort
{
    public const PROCESS_CODE = 'partes.informes.paqueteHoras';

    /** @var list<string> */
    private const DATASET_COLUMNS = [
        'fecha',
        'clienteCode',
        'clienteNombre',
        'usuarioCode',
        'usuarioNombre',
        'tipoClienteCode',
        'tipoClienteDescripcion',
        'tipoTareaCode',
        'tipoTareaDescripcion',
        'duracionMinutos',
        'saldo',
        'esTarea',
        'observacion',
        'erpCliente',
        'erpArticulo',
        'sinCargo',
        'presencial',
        'cerrado',
    ];

    public function __construct(
        private readonly SpCaller $spCaller,
        private readonly PartesEmissionHostContextStore $hostContextStore,
    ) {
    }

    /**
     * @return list<string>
     */
    public static function designDatasetColumnNames(): array
    {
        return self::DATASET_COLUMNS;
    }

    public function resolveDataset(EmissionContext $context): EmissionDataset
    {
        $hostContext = $this->resolveHostContext($context);
        $fechaDesde = trim((string) ($hostContext['fechaDesde'] ?? ''));
        $fechaHasta = trim((string) ($hostContext['fechaHasta'] ?? ''));
        $clienteId = $this->nullableInt($hostContext['clienteId'] ?? null);
        if ($fechaDesde === '' || $fechaHasta === ''
            || preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde) !== 1
            || preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta) !== 1
            || $clienteId === null || $clienteId <= 0
        ) {
            throw EmissionException::fromCode(4701);
        }

        $partes = request()->attributes->get(EnsurePartesFunctionalProfile::REQUEST_ATTR, []);
        $selectedTareaIds = $this->resolveSelectedTareaIds($hostContext);

        try {
            $row = $this->spCaller->call('pq_sp_partes_informe_paquete_horas', [
                'p_actor_tipo_funcional' => $partes['tipoFuncional'] ?? null,
                'p_actor_asistente_id' => $partes['asistenteId'] ?? null,
                'p_actor_cliente_id' => $partes['clienteId'] ?? null,
                'p_actor_es_supervisor' => (bool) ($partes['esSupervisor'] ?? false),
                'p_fecha_desde' => $fechaDesde,
                'p_fecha_hasta' => $fechaHasta,
                'p_cliente_id' => $clienteId,
                'p_usuario_id' => null,
                'p_tipo_cliente_id' => $this->nullableInt($hostContext['tipoClienteId'] ?? null),
            ])[0] ?? null;
        } catch (PartesTareaException) {
            throw EmissionException::fromCode(4701);
        }

        $itemsRaw = json_decode((string) ($row->items_json ?? '[]'), true);
        $items = is_array($itemsRaw) ? $itemsRaw : [];

        $selectedSet = array_fill_keys($selectedTareaIds, true);
        $datasetRows = [];
        foreach ($items as $item) {
            $camel = $this->toCamel(is_array($item) ? $item : (array) $item);
            if (! empty($camel['esSaldoInicial'])) {
                continue;
            }
            $rowId = isset($camel['id']) ? (int) $camel['id'] : 0;
            if ($rowId === 0 || ! isset($selectedSet[$rowId])) {
                continue;
            }
            $datasetRows[] = $this->pickColumns($camel);
        }

        $schema = [];
        foreach (self::DATASET_COLUMNS as $column) {
            if (in_array($column, ['duracionMinutos', 'saldo'], true)) {
                $schema[$column] = 'number';
            } elseif (in_array($column, ['esTarea', 'sinCargo', 'presencial', 'cerrado'], true)) {
                $schema[$column] = 'boolean';
            } else {
                $schema[$column] = 'string';
            }
        }

        return new EmissionDataset($datasetRows, $schema);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveHostContext(EmissionContext $context): array
    {
        $fromCache = $context->jobId !== null ? $this->hostContextStore->get($context->jobId) : null;
        if (is_array($fromCache)) {
            return $fromCache;
        }

        $fromRequest = request()->input('hostContext');
        if (! is_array($fromRequest)) {
            throw EmissionException::fromCode(4701);
        }

        if ($context->jobId !== null) {
            $this->hostContextStore->put($context->jobId, $fromRequest);
        }

        return $fromRequest;
    }

    /**
     * @param  array<string, mixed>  $hostContext
     * @return list<int>
     */
    private function resolveSelectedTareaIds(array $hostContext): array
    {
        if (! array_key_exists('selectedTareaIds', $hostContext)) {
            throw EmissionException::fromCode(4701);
        }

        $raw = $hostContext['selectedTareaIds'];
        if (! is_array($raw) || $raw === []) {
            throw EmissionException::fromCode(4701);
        }

        $ids = [];
        foreach ($raw as $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $id = (int) $value;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        if ($ids === []) {
            throw EmissionException::fromCode(4701);
        }

        return array_values($ids);
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function toCamel(array $row): array
    {
        $out = [];
        foreach ($row as $key => $value) {
            $camel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', (string) $key))));
            $out[$camel] = $value;
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function pickColumns(array $row): array
    {
        $out = [];
        foreach (self::DATASET_COLUMNS as $column) {
            $out[$column] = $row[$column] ?? null;
        }

        return $out;
    }
}
