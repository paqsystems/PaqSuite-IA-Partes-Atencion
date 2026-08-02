<?php

namespace App\Http\Controllers\Api\V1\Partes;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsurePartesFunctionalProfile;
use App\Repositories\Sp\Partes\PartesTareaRepository;
use App\Services\Partes\PartesTareaException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;

final class PartesTareaController extends Controller
{
    public function __construct(private readonly PartesTareaRepository $repository)
    {
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $result = $this->repository->list(array_merge($this->actorParams($request), [
                'p_fecha_desde' => $request->query('fechaDesde'),
                'p_fecha_hasta' => $request->query('fechaHasta'),
                'p_cliente_id' => $request->query('clienteId'),
                'p_usuario_id' => $request->query('usuarioId'),
                'p_estado_cerrado' => $request->query('estadoCerrado', 'todas'),
                'p_page' => $request->query('page', 1),
                'p_page_size' => $request->query('pageSize', 50),
            ]));

            return ApiResponse::success($result);
        } catch (PartesTareaException $e) {
            return $this->fromException($e);
        }
    }

    public function listIds(Request $request): JsonResponse
    {
        try {
            $result = $this->repository->listIds(array_merge($this->actorParams($request), [
                'p_fecha_desde' => $request->query('fechaDesde'),
                'p_fecha_hasta' => $request->query('fechaHasta'),
                'p_cliente_id' => $request->query('clienteId'),
                'p_usuario_id' => $request->query('usuarioId'),
                'p_estado_cerrado' => $request->query('estadoCerrado', 'todas'),
            ]));

            return ApiResponse::success($result);
        } catch (PartesTareaException $e) {
            return $this->fromException($e);
        }
    }

    public function masivoSetCerrado(Request $request): JsonResponse
    {
        try {
            $item = $this->repository->getOne('pq_sp_partes_tarea_masivo_set_cerrado', array_merge(
                $this->actorParams($request),
                [
                    'p_accion' => $request->input('accion'),
                    'p_items_json' => $request->input('items', []),
                ]
            ));

            return ApiResponse::success(['item' => $item]);
        } catch (PartesTareaException $e) {
            return $this->fromException($e);
        }
    }

    public function masivoActualizar(Request $request): JsonResponse
    {
        try {
            $item = $this->repository->getOne('pq_sp_partes_tarea_masivo_actualizar', array_merge(
                $this->actorParams($request),
                [
                    'p_campos_json' => $request->input('campos', []),
                    'p_items_json' => $request->input('items', []),
                ]
            ));

            return ApiResponse::success(['item' => $item]);
        } catch (PartesTareaException $e) {
            return $this->fromException($e);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $item = $this->repository->getOne('pq_sp_partes_tarea_get', array_merge(
                $this->actorParams($request),
                ['p_id' => $id]
            ));

            return ApiResponse::success(['item' => $item]);
        } catch (PartesTareaException $e) {
            return $this->fromException($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        return $this->upsert($request, null);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->upsert($request, $id);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $rowVersion = $request->input('rowVersion', $request->query('rowVersion'));
            $this->repository->call('pq_sp_partes_tarea_delete', array_merge(
                $this->actorParams($request),
                ['p_id' => $id, 'p_row_version' => $rowVersion]
            ));

            return ApiResponse::success([]);
        } catch (PartesTareaException $e) {
            return $this->fromException($e);
        }
    }

    public function cerrar(Request $request, int $id): JsonResponse
    {
        return $this->setCerrado($request, $id, true);
    }

    public function reabrir(Request $request, int $id): JsonResponse
    {
        return $this->setCerrado($request, $id, false);
    }

    public function duracionTramo(): JsonResponse
    {
        return ApiResponse::success([
            'tramoMinutos' => $this->repository->tramoMinutos(),
        ]);
    }

    private function upsert(Request $request, ?int $id): JsonResponse
    {
        try {
            $item = $this->repository->getOne('pq_sp_partes_tarea_upsert', array_merge(
                $this->actorParams($request),
                [
                    'p_id' => $id,
                    'p_usuario_id' => $request->input('usuarioId'),
                    'p_cliente_id' => $request->input('clienteId'),
                    'p_tipo_tarea_id' => $request->input('tipoTareaId'),
                    'p_fecha' => $request->input('fecha'),
                    'p_duracion_minutos' => $request->input('duracionMinutos'),
                    'p_sin_cargo' => $request->input('sinCargo', false),
                    'p_presencial' => $request->input('presencial', false),
                    'p_observacion' => $request->input('observacion'),
                    'p_row_version' => $request->input('rowVersion'),
                    'p_confirmar_fecha_futura' => $request->boolean('confirmarFechaFutura'),
                ]
            ));

            return ApiResponse::success(
                ['item' => $item],
                PaqSuiteEnvelopeCatalog::RESPUESTA_OK,
                $id === null ? 201 : 200
            );
        } catch (PartesTareaException $e) {
            return $this->fromException($e);
        }
    }

    private function setCerrado(Request $request, int $id, bool $cerrado): JsonResponse
    {
        try {
            $item = $this->repository->getOne('pq_sp_partes_tarea_set_cerrado', array_merge(
                $this->actorParams($request),
                [
                    'p_id' => $id,
                    'p_cerrado' => $cerrado,
                    'p_row_version' => $request->input('rowVersion'),
                ]
            ));

            return ApiResponse::success(['item' => $item]);
        } catch (PartesTareaException $e) {
            return $this->fromException($e);
        }
    }

    private function actorParams(Request $request): array
    {
        /** @var array<string, mixed> $partes */
        $partes = $request->attributes->get(EnsurePartesFunctionalProfile::REQUEST_ATTR, []);

        return [
            'p_actor_tipo_funcional' => $partes['tipoFuncional'] ?? 'asistente',
            'p_actor_asistente_id' => $partes['asistenteId'] ?? null,
            'p_actor_cliente_id' => $partes['clienteId'] ?? null,
            'p_actor_es_supervisor' => (bool) ($partes['esSupervisor'] ?? false),
        ];
    }

    private function fromException(PartesTareaException $e): JsonResponse
    {
        $error = match ($e->httpStatus) {
            404 => PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND,
            403 => PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN,
            409 => PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
            default => PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
        };

        // Prefer a conflict catalog if available; otherwise keep validation + 409 status.
        return ApiResponse::error($error, $e->respuesta, $e->httpStatus);
    }
}
