<?php

namespace App\Services\Emissions;

use App\Http\Middleware\EnsurePartesFunctionalProfile;
use App\Repositories\Sp\SpCaller;
use App\Services\Partes\PartesTareaException;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionDatasetPort;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionContext;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionDataset;
use PaqSuite\LaravelCore\Emissions\EmissionException;

final class PartesConsultaDetalladaEmissionPort implements EmissionDatasetPort
{
    public const PROCESS_CODE = 'partes.informes.consultaDetallada';

    /** @var list<string> */
    private const DATASET_COLUMNS = [
        'fecha',
        'clienteCode',
        'clienteNombre',
        'usuarioCode',
        'usuarioNombre',
        'tipoTareaCode',
        'tipoTareaDescripcion',
        'duracionMinutos',
        'sinCargo',
        'presencial',
        'cerrado',
        'observacion',
        'erpCliente',
        'erpArticulo',
    ];

    public function __construct(
        private readonly SpCaller $spCaller,
        private readonly PartesEmissionHostContextStore $hostContextStore,
    ) {
    }

    public function resolveDataset(EmissionContext $context): EmissionDataset
    {
        $hostContext = $this->resolveHostContext($context);
        $fechaDesde = trim((string) ($hostContext['fechaDesde'] ?? ''));
        $fechaHasta = trim((string) ($hostContext['fechaHasta'] ?? ''));
        if ($fechaDesde === '' || $fechaHasta === ''
            || preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde) !== 1
            || preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta) !== 1
        ) {
            throw EmissionException::fromCode(4701);
        }

        $estadoCerrado = (string) ($hostContext['estadoCerrado'] ?? 'todas');
        if (! in_array($estadoCerrado, ['todas', 'abiertas', 'cerradas'], true)) {
            $estadoCerrado = 'todas';
        }

        $partes = request()->attributes->get(EnsurePartesFunctionalProfile::REQUEST_ATTR, []);
        $esSupervisor = (bool) ($partes['esSupervisor'] ?? false);
        $usuarioId = $esSupervisor ? $this->nullableInt($hostContext['usuarioId'] ?? null) : null;

        try {
            $rows = $this->spCaller->call('pq_sp_partes_tarea_list', [
                'p_actor_tipo_funcional' => $partes['tipoFuncional'] ?? null,
                'p_actor_asistente_id' => $partes['asistenteId'] ?? null,
                'p_actor_cliente_id' => $partes['clienteId'] ?? null,
                'p_actor_es_supervisor' => $esSupervisor,
                'p_fecha_desde' => $fechaDesde,
                'p_fecha_hasta' => $fechaHasta,
                'p_cliente_id' => $this->nullableInt($hostContext['clienteId'] ?? null),
                'p_usuario_id' => $usuarioId,
                'p_tipo_tarea_id' => $this->nullableInt($hostContext['tipoTareaId'] ?? null),
                'p_estado_cerrado' => $estadoCerrado,
                'p_page' => 1,
                'p_page_size' => 0,
            ]);
        } catch (PartesTareaException $exception) {
            throw EmissionException::fromCode(4701);
        }

        $datasetRows = [];
        foreach ($rows as $row) {
            $camel = $this->toCamel((array) $row);
            unset($camel['_total']);
            $datasetRows[] = $this->pickColumns($camel);
        }

        $schema = [];
        foreach (self::DATASET_COLUMNS as $column) {
            $schema[$column] = $column === 'duracionMinutos' ? 'number' : 'string';
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
