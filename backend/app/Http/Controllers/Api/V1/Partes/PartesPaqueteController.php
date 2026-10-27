<?php

namespace App\Http\Controllers\Api\V1\Partes;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsurePartesFunctionalProfile;
use App\Repositories\Sp\Partes\PartesPaqueteRepository;
use App\Services\Partes\PartesPaqueteException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;

final class PartesPaqueteController extends Controller
{
    public function __construct(private readonly PartesPaqueteRepository $repository)
    {
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $result = $this->repository->list(array_merge($this->actorParams($request), [
                'p_fecha_desde' => $request->query('fechaDesde'),
                'p_fecha_hasta' => $request->query('fechaHasta'),
                'p_cliente_id' => $request->query('clienteId'),
                'p_page' => $request->query('page', 1),
                'p_page_size' => $request->query('pageSize', 50),
            ]));

            return ApiResponse::success($result);
        } catch (PartesPaqueteException $e) {
            return $this->fromException($e);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $item = $this->repository->getOne('pq_sp_partes_paquete_get', array_merge(
                $this->actorParams($request),
                ['p_id' => $id]
            ));

            return ApiResponse::success(['item' => $item]);
        } catch (PartesPaqueteException $e) {
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
            $this->repository->call('pq_sp_partes_paquete_delete', array_merge(
                $this->actorParams($request),
                ['p_id' => $id, 'p_row_version' => $rowVersion]
            ));

            return ApiResponse::success([]);
        } catch (PartesPaqueteException $e) {
            return $this->fromException($e);
        }
    }

    private function upsert(Request $request, ?int $id): JsonResponse
    {
        try {
            $item = $this->repository->getOne('pq_sp_partes_paquete_upsert', array_merge(
                $this->actorParams($request),
                [
                    'p_id' => $id,
                    'p_cliente_id' => $request->input('clienteId'),
                    'p_fecha' => $request->input('fecha'),
                    'p_duracion_minutos' => $request->input('duracionMinutos'),
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
        } catch (PartesPaqueteException $e) {
            return $this->fromException($e);
        }
    }

    /** @return array<string, mixed> */
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

    private function fromException(PartesPaqueteException $e): JsonResponse
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
