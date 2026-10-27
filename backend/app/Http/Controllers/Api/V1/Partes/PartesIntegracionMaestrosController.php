<?php

namespace App\Http\Controllers\Api\V1\Partes;

use App\Http\Controllers\Controller;
use App\Repositories\Sp\Partes\PartesIntegracionMaestrosRepository;
use App\Repositories\Sp\SpParametroRepository;
use App\Services\Partes\PartesIntegracionMaestrosException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;

final class PartesIntegracionMaestrosController extends Controller
{
    private const MAX_ITEMS = 1500;

    /** @var array<string, string> */
    private const PROCEDURES = [
        'clientes' => 'pq_sp_partes_integracion_maestros_clientes',
        'asistentes' => 'pq_sp_partes_integracion_maestros_asistentes',
        'tipos-cliente' => 'pq_sp_partes_integracion_maestros_tipos_cliente',
        'tipos-tarea' => 'pq_sp_partes_integracion_maestros_tipos_tarea',
        'asignaciones-cliente-tipo' => 'pq_sp_partes_integracion_maestros_asignaciones',
    ];

    public function __construct(
        private readonly PartesIntegracionMaestrosRepository $repository,
        private readonly SpParametroRepository $parametroRepository,
    ) {
    }

    public function clientes(Request $request): JsonResponse
    {
        return $this->process($request, 'clientes');
    }

    public function asistentes(Request $request): JsonResponse
    {
        return $this->process($request, 'asistentes');
    }

    public function tiposCliente(Request $request): JsonResponse
    {
        return $this->process($request, 'tipos-cliente');
    }

    public function tiposTarea(Request $request): JsonResponse
    {
        return $this->process($request, 'tipos-tarea');
    }

    public function asignaciones(Request $request): JsonResponse
    {
        return $this->process($request, 'asignaciones-cliente-tipo');
    }

    private function process(Request $request, string $resource): JsonResponse
    {
        $rawJson = $request->getContent();
        $maxJsonMb = $this->parameterInt('PARTES_INTEGRACION_MAESTROS_MAX_JSON_MB', 10);
        if (strlen($rawJson) > $maxJsonMb * 1024 * 1024) {
            return $this->validationError('integracion.maestro.jsonDemasiadoGrande');
        }

        $payload = $request->json()->all();
        if (! is_array($payload) || $payload === []) {
            return $this->validationError('integracion.maestro.requestVacio');
        }

        $validation = Validator::make($payload, $this->rulesFor($resource));
        if ($validation->fails() || ! $this->strictBooleanValues($payload)) {
            return $this->validationError('integracion.maestro.contratoInvalido');
        }

        $items = $payload['items'];
        if (count($items) > self::MAX_ITEMS) {
            return $this->validationError('integracion.maestro.demasiadosItems');
        }
        if (! $this->hasUniqueKeys($resource, $items)) {
            return $this->validationError('integracion.maestro.duplicado');
        }

        $timeout = $this->parameterInt('PARTES_INTEGRACION_MAESTROS_TIMEOUT_SEGUNDOS', 120);
        set_time_limit(max(1, $timeout));

        try {
            $rows = $this->repository->process(self::PROCEDURES[$resource], [
                'itemsJson' => json_encode($items, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'fechaProceso' => $payload['fechaProceso'],
                'origen' => array_key_exists('origen', $payload) && $payload['origen'] !== '' ? $payload['origen'] : null,
                'dryRun' => (bool) ($payload['dryRun'] ?? false),
            ]);

            return ApiResponse::success(
                $this->publicResult($resource, $payload, $rows),
                'integracion.maestro.procesado'
            );
        } catch (\JsonException) {
            return $this->validationError('integracion.maestro.jsonInvalido');
        } catch (PartesIntegracionMaestrosException $exception) {
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                $exception->respuesta,
                $exception->httpStatus
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function rulesFor(string $resource): array
    {
        $rules = [
            'fechaProceso' => ['required', 'date_format:Y-m-d'],
            'origen' => ['sometimes', 'nullable', 'string', 'max:100'],
            'dryRun' => ['sometimes', 'boolean'],
            'items' => ['required', 'array', 'min:1', 'max:'.self::MAX_ITEMS],
        ];

        $itemRules = match ($resource) {
            'clientes' => [
                'items.*.code' => ['required', 'string', 'max:50'],
                'items.*.nombre' => ['required', 'string', 'max:255'],
                'items.*.tipoCliente.code' => ['required', 'string', 'max:50'],
                'items.*.email' => ['sometimes', 'nullable', 'string', 'max:255'],
                'items.*.erpCliente' => ['sometimes', 'nullable', 'string', 'max:15'],
                'items.*.erpArticulo' => ['sometimes', 'nullable', 'string', 'max:15'],
            ],
            'asistentes' => [
                'items.*.code' => ['required', 'string', 'max:50'],
                'items.*.nombre' => ['required', 'string', 'max:255'],
                'items.*.usuario.code' => ['required', 'string', 'max:50'],
                'items.*.email' => ['sometimes', 'nullable', 'string', 'max:255'],
                'items.*.supervisor' => ['sometimes', 'boolean'],
            ],
            'tipos-cliente' => [
                'items.*.code' => ['required', 'string', 'max:50'],
                'items.*.descripcion' => ['required', 'string', 'max:255'],
            ],
            'tipos-tarea' => [
                'items.*.code' => ['required', 'string', 'max:50'],
                'items.*.descripcion' => ['required', 'string', 'max:255'],
                'items.*.isGenerico' => ['sometimes', 'boolean'],
                'items.*.isDefault' => ['sometimes', 'boolean'],
            ],
            'asignaciones-cliente-tipo' => [
                'items.*.code' => ['required', 'string', 'max:50'],
                'items.*.tiposTarea' => ['required', 'array'],
                'items.*.tiposTarea.*.code' => ['required', 'string', 'max:50'],
            ],
        };

        return array_merge($rules, $itemRules);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function strictBooleanValues(array $payload): bool
    {
        foreach ($payload as $key => $value) {
            if (in_array($key, ['dryRun', 'supervisor', 'isGenerico', 'isDefault'], true) && $value !== null && ! is_bool($value)) {
                return false;
            }
            if (is_array($value) && ! $this->strictBooleanValues($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function hasUniqueKeys(string $resource, array $items): bool
    {
        $seen = [];
        foreach ($items as $item) {
            $key = trim((string) ($item['code'] ?? ''));
            if ($resource === 'asignaciones-cliente-tipo') {
                $nested = [];
                foreach (($item['tiposTarea'] ?? []) as $type) {
                    $typeCode = trim((string) ($type['code'] ?? ''));
                    if (isset($nested[$typeCode])) {
                        return false;
                    }
                    $nested[$typeCode] = true;
                }
            }
            if (isset($seen[$key])) {
                return false;
            }
            $seen[$key] = true;
        }

        return true;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function publicResult(string $resource, array $payload, array $rows): array
    {
        $summary = $rows[0] ?? [];
        $details = array_slice($rows, 1);
        $errors = [];
        $resultados = [];
        foreach ($details as $detail) {
            $item = [
                'indice' => $detail['indice'] ?? null,
                'clave' => $detail['clave'] ?? null,
                'accion' => $detail['accion'] ?? null,
            ];
            if (isset($detail['codigo'])) {
                $item['codigo'] = $detail['codigo'];
                $errors[] = [
                    'indice' => $item['indice'],
                    'clave' => $item['clave'],
                    'codigo' => $detail['codigo'],
                ];
            }
            $resultados[] = $item;
        }

        return [
            'recurso' => $resource,
            'dryRun' => (bool) ($payload['dryRun'] ?? false),
            'recibidos' => (int) ($summary['recibidos'] ?? count($payload['items'])),
            'aplicados' => (int) ($summary['aplicados'] ?? 0),
            'omitidos' => (int) ($summary['omitidos'] ?? 0),
            'rechazados' => (int) ($summary['rechazados'] ?? 0),
            'erroresTruncados' => (bool) ($summary['erroresTruncados'] ?? false),
            'resultados' => $resultados,
            'errores' => array_slice($errors, 0, 50),
        ];
    }

    private function parameterInt(string $key, int $default): int
    {
        $parameter = $this->parametroRepository->get('Partes', $key);
        $value = $parameter['valor'] ?? null;

        return is_numeric($value) && (int) $value > 0 ? (int) $value : $default;
    }

    private function validationError(string $key): JsonResponse
    {
        return ApiResponse::error(
            PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
            $key,
            422
        );
    }
}
