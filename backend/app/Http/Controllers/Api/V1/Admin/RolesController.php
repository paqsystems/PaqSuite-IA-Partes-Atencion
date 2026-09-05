<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\Security\RolAdminRepository;

/**
 * ABM roles (GEN-06). Contrato SPEC: nombre, descripcion, accesoTotal.
 */
final class RolesController extends Controller
{
    public function __construct(
        private readonly RolAdminRepository $rolAdminRepository
    ) {
    }

    public function index(): JsonResponse
    {
        return ApiResponse::success([
            'items' => $this->rolAdminRepository->listAll(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['sometimes', 'nullable', 'string', 'max:500'],
            'accesoTotal' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $item = $this->rolAdminRepository->create($validator->validated());

        return ApiResponse::success(['item' => $item], PaqSuiteEnvelopeCatalog::RESPUESTA_OK, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => ['sometimes', 'string', 'max:255'],
            'descripcion' => ['sometimes', 'nullable', 'string', 'max:500'],
            'accesoTotal' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $item = $this->rolAdminRepository->update($id, $validator->validated());
        if ($item === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        return ApiResponse::success(['item' => $item]);
    }

    public function destroy(int $id): JsonResponse
    {
        $outcome = $this->rolAdminRepository->delete($id);

        return match ($outcome) {
            'ok' => ApiResponse::success(['deleted' => true]),
            'not_found' => ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND),
            'has_permisos' => ApiResponse::error(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                'roles.delete.hasPermisos',
                422
            ),
            default => ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND),
        };
    }
}
