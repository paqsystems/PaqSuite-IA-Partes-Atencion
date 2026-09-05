<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\Security\PermisoAdminRepository;

/**
 * ABM permisos usuario x empresa x rol (GEN-06).
 * Contrato SPEC: usuarioId / empresaId / rolId (+ filtros en index).
 */
final class PermisosController extends Controller
{
    private const BATCH_MAX_ITEMS = 1000;

    public function __construct(
        private readonly PermisoAdminRepository $permisoAdminRepository
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = [];
        if ($request->filled('usuarioId')) {
            $filters['usuarioId'] = (int) $request->query('usuarioId');
        }
        if ($request->filled('empresaId')) {
            $filters['empresaId'] = (int) $request->query('empresaId');
        }
        if ($request->filled('rolId')) {
            $filters['rolId'] = (int) $request->query('rolId');
        }

        return ApiResponse::success([
            'items' => $this->permisoAdminRepository->listAll($filters),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'usuarioId' => ['required', 'integer', Rule::exists('users', 'id')],
            'empresaId' => ['required', 'integer', Rule::exists('pq_empresa', 'id')],
            'rolId' => ['required', 'integer', Rule::exists('pq_roles', 'id')],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        try {
            $item = $this->permisoAdminRepository->create($validator->validated());
        } catch (QueryException) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['respuesta' => 'permisos.duplicate']
            );
        }

        return ApiResponse::success(['item' => $item], PaqSuiteEnvelopeCatalog::RESPUESTA_OK, 201);
    }

    public function batch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array', 'min:1', 'max:'.self::BATCH_MAX_ITEMS],
            'items.*.usuarioId' => ['required', 'integer', Rule::exists('users', 'id')],
            'items.*.empresaId' => ['required', 'integer', Rule::exists('pq_empresa', 'id')],
            'items.*.rolId' => ['required', 'integer', Rule::exists('pq_roles', 'id')],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $resultado = $this->permisoAdminRepository->createBatch($validator->validated()['items']);

        return ApiResponse::success($resultado);
    }

    public function destroy(int $id): JsonResponse
    {
        if (!$this->permisoAdminRepository->deleteById($id)) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        return ApiResponse::success(['deleted' => true]);
    }
}
