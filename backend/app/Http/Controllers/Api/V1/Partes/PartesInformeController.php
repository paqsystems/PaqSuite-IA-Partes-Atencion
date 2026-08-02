<?php

namespace App\Http\Controllers\Api\V1\Partes;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsurePartesFunctionalProfile;
use App\Repositories\Sp\SpCaller;
use App\Services\Partes\PartesInformeOperations;
use App\Services\Partes\PartesTareaException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;

final class PartesInformeController extends Controller
{
    public function __construct(private readonly SpCaller $spCaller)
    {
    }

    public function listTareas(Request $request): JsonResponse
    {
        try {
            $rows = $this->spCaller->call('pq_sp_partes_tarea_list', array_merge($this->actorParams($request), [
                'p_fecha_desde' => $request->query('fechaDesde'),
                'p_fecha_hasta' => $request->query('fechaHasta'),
                'p_cliente_id' => $request->query('clienteId'),
                'p_usuario_id' => $request->query('usuarioId'),
                'p_tipo_tarea_id' => $request->query('tipoTareaId'),
                'p_estado_cerrado' => $request->query('estadoCerrado', 'todas'),
                'p_page' => $request->query('page', 1),
                'p_page_size' => $request->query('pageSize', 50),
            ]));
            $total = 0;
            $items = [];
            foreach ($rows as $row) {
                $arr = (array) $row;
                if (isset($arr['_total'])) {
                    $total = (int) $arr['_total'];
                    unset($arr['_total']);
                }
                $items[] = $this->toCamel($arr);
            }
            if ($total === 0) {
                $total = count($items);
            }

            return ApiResponse::success(['items' => $items, 'total' => $total]);
        } catch (PartesTareaException $e) {
            return $this->fromException($e);
        }
    }

    public function agrupado(Request $request): JsonResponse
    {
        try {
            $rows = $this->spCaller->call('pq_sp_partes_informe_agrupado', array_merge($this->actorParams($request), [
                'p_eje' => $request->query('eje'),
                'p_granularidad_fecha' => $request->query('granularidadFecha'),
                'p_fecha_desde' => $request->query('fechaDesde'),
                'p_fecha_hasta' => $request->query('fechaHasta'),
                'p_cliente_id' => $request->query('clienteId'),
                'p_usuario_id' => $request->query('usuarioId'),
                'p_tipo_tarea_id' => $request->query('tipoTareaId'),
                'p_estado_cerrado' => $request->query('estadoCerrado', 'todas'),
            ]));
            $items = array_map(fn ($row) => $this->toCamel((array) $row), $rows);

            return ApiResponse::success(['items' => $items, 'total' => count($items)]);
        } catch (PartesTareaException $e) {
            return $this->fromException($e);
        }
    }

    public function dashboard(Request $request): JsonResponse
    {
        try {
            [$fechaDesde, $fechaHasta] = $this->resolvePeriodo($request);
            $row = $this->spCaller->call('pq_sp_partes_dashboard_snapshot', array_merge($this->actorParams($request), [
                'p_fecha_desde' => $fechaDesde,
                'p_fecha_hasta' => $fechaHasta,
                'p_top_n' => PartesInformeOperations::resolveDashboardTopN(),
            ]))[0] ?? null;
            $top = [];
            if ($row !== null && ! empty($row->top_json)) {
                $decoded = json_decode((string) $row->top_json, true);
                $top = is_array($decoded) ? $decoded : [];
            }

            return ApiResponse::success([
                'totalMinutos' => (int) ($row->total_minutos ?? 0),
                'cantidadTareas' => (int) ($row->cantidad_tareas ?? 0),
                'top' => $top,
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta,
            ]);
        } catch (PartesTareaException $e) {
            return $this->fromException($e);
        }
    }

    public function dashboardParametros(): JsonResponse
    {
        return ApiResponse::success([
            'topN' => PartesInformeOperations::resolveDashboardTopN(),
            'refreshSeg' => PartesInformeOperations::resolveDashboardRefreshSeg(),
        ]);
    }

    public function paqueteHoras(Request $request): JsonResponse
    {
        try {
            [$fechaDesde, $fechaHasta] = $this->resolvePeriodo($request);
            $row = $this->spCaller->call('pq_sp_partes_informe_paquete_horas', array_merge($this->actorParams($request), [
                'p_fecha_desde' => $fechaDesde,
                'p_fecha_hasta' => $fechaHasta,
                'p_cliente_id' => $request->query('clienteId'),
                'p_usuario_id' => $request->query('usuarioId'),
            ]))[0] ?? null;

            $itemsRaw = json_decode((string) ($row->items_json ?? '[]'), true);
            $items = is_array($itemsRaw) ? $itemsRaw : [];
            $itemsCamel = array_map(function ($item) {
                $arr = is_array($item) ? $item : (array) $item;
                $camel = $this->toCamel($arr);
                foreach (['esSaldoInicial', 'sinCargo', 'presencial', 'cerrado', 'esTarea'] as $boolKey) {
                    if (array_key_exists($boolKey, $camel)) {
                        $camel[$boolKey] = (bool) $camel[$boolKey];
                    }
                }

                return $camel;
            }, $items);

            return ApiResponse::success([
                'items' => $itemsCamel,
                'total' => (int) ($row->total ?? count($itemsCamel)),
                'saldoInicial' => (int) ($row->saldo_inicial ?? 0),
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta,
            ]);
        } catch (PartesTareaException $e) {
            return $this->fromException($e);
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolvePeriodo(Request $request): array
    {
        $mes = trim((string) $request->query('mes', ''));
        if ($mes !== '' && preg_match('/^\d{4}-\d{2}$/', $mes) === 1) {
            $desde = $mes.'-01';
            $hasta = date('Y-m-t', strtotime($desde));

            return [$desde, $hasta];
        }
        $fechaDesde = trim((string) $request->query('fechaDesde', ''));
        $fechaHasta = trim((string) $request->query('fechaHasta', ''));
        if ($fechaDesde === '' || $fechaHasta === '') {
            throw new PartesTareaException('partes.tarea.fechasRequeridas');
        }

        return [$fechaDesde, $fechaHasta];
    }

    /**
     * @return array<string, mixed>
     */
    private function actorParams(Request $request): array
    {
        /** @var array<string, mixed> $partes */
        $partes = $request->attributes->get(EnsurePartesFunctionalProfile::REQUEST_ATTR, []);

        return [
            'p_actor_tipo_funcional' => $partes['tipoFuncional'] ?? null,
            'p_actor_asistente_id' => $partes['asistenteId'] ?? null,
            'p_actor_cliente_id' => $partes['clienteId'] ?? null,
            'p_actor_es_supervisor' => (bool) ($partes['esSupervisor'] ?? false),
        ];
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

    private function fromException(PartesTareaException $e): JsonResponse
    {
        $error = match ($e->httpStatus) {
            404 => PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND,
            403 => PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN,
            409 => PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
            default => PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
        };

        return ApiResponse::error($error, $e->respuesta, $e->httpStatus);
    }
}
