<?php

namespace App\Http\Controllers\Api\V1\Partes;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsurePartesFunctionalProfile;
use App\Services\Partes\PartesMasivoInformarClientesService;
use App\Services\Partes\PartesTareaException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;

final class PartesMasivoController extends Controller
{
    public function __construct(
        private readonly PartesMasivoInformarClientesService $informarService,
    ) {
    }

    public function previewInformarClientes(Request $request): JsonResponse
    {
        try {
            $params = $this->masivoFilterParams($request);

            return ApiResponse::success($this->informarService->preview($params));
        } catch (PartesTareaException $e) {
            return $this->fromException($e);
        }
    }

    public function informarClientes(Request $request): JsonResponse
    {
        try {
            $params = $this->masivoFilterParams($request);
            $tenant = $this->resolveTenantCode($request);
            $result = $this->informarService->informar($tenant, $params);

            return ApiResponse::success(['item' => $result]);
        } catch (PartesTareaException $e) {
            return $this->fromException($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function masivoFilterParams(Request $request): array
    {
        $fechaDesde = $request->query('fechaDesde', $request->input('fechaDesde'));
        $fechaHasta = $request->query('fechaHasta', $request->input('fechaHasta'));

        return array_merge($this->actorParams($request), [
            'p_fecha_desde' => $fechaDesde,
            'p_fecha_hasta' => $fechaHasta,
            'p_cliente_id' => $request->query('clienteId', $request->input('clienteId')),
            'p_usuario_id' => $request->query('usuarioId', $request->input('usuarioId')),
            'p_estado_cerrado' => $request->query('estadoCerrado', $request->input('estadoCerrado', 'todas')),
            'p_cliente_code' => $request->query('clienteCode', $request->input('clienteCode')),
            'p_cliente_nombre' => $request->query('clienteNombre', $request->input('clienteNombre')),
            'p_tipo_tarea_code' => $request->query('tipoTareaCode', $request->input('tipoTareaCode')),
            'p_tipo_tarea_descripcion' => $request->query('tipoTareaDescripcion', $request->input('tipoTareaDescripcion')),
            'p_eq_json' => $request->query('eq', $request->input('eq')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
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

    private function resolveTenantCode(Request $request): string
    {
        $header = (string) config('paqsuite.headers.cliente', 'X-Paq-Cliente');
        $fromHeader = trim((string) $request->header($header, ''));
        if ($fromHeader !== '') {
            return $fromHeader;
        }

        return trim((string) config('paqsuite.clienteCodigo', 'DEMO'));
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
