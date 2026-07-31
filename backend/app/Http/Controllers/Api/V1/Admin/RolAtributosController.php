<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\Security\RolAtributosRepository;

/**
 * Roles y atributos por opción de menú (GEN-06-roles-atributos).
 * Árbol de `pq_menus` habilitado viaja embebido en la misma respuesta GET (D1-06-10).
 */
final class RolAtributosController extends Controller
{
    public function __construct(
        private readonly RolAtributosRepository $rolAtributosRepository
    ) {
    }

    public function show(int $id): JsonResponse
    {
        $rol = $this->rolAtributosRepository->getRolSummary($id);
        if ($rol === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        return ApiResponse::success([
            'accesoTotal' => $rol['accesoTotal'],
            'codigo' => $rol['codigo'],
            'nombre' => $rol['nombre'],
            'items' => $rol['accesoTotal'] ? [] : $this->rolAtributosRepository->listItems($id),
            'arbol' => $this->rolAtributosRepository->arbolEnabled(),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $rol = $this->rolAtributosRepository->getRolSummary($id);
        if ($rol === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        if ($rol['accesoTotal']) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['respuesta' => 'roles.atributos.accesoTotalNoEditable']
            );
        }

        $validMenuIds = $this->rolAtributosRepository->menuIdsProcesoEnabled();

        $validator = Validator::make($request->all(), [
            'items' => ['present', 'array'],
            'items.*.menuId' => ['required', 'integer', 'distinct', Rule::in($validMenuIds)],
            'items.*.permisoAlta' => ['sometimes', 'boolean'],
            'items.*.permisoBaja' => ['sometimes', 'boolean'],
            'items.*.permisoModi' => ['sometimes', 'boolean'],
            'items.*.permisoRepo' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $items = array_map(static fn (array $item): array => [
            'menuId' => (int) $item['menuId'],
            'permisoAlta' => (bool) ($item['permisoAlta'] ?? false),
            'permisoBaja' => (bool) ($item['permisoBaja'] ?? false),
            'permisoModi' => (bool) ($item['permisoModi'] ?? false),
            'permisoRepo' => (bool) ($item['permisoRepo'] ?? false),
        ], $validator->validated()['items']);

        $this->rolAtributosRepository->replaceItems($id, $items);

        return ApiResponse::success([
            'accesoTotal' => false,
            'codigo' => $rol['codigo'],
            'nombre' => $rol['nombre'],
            'items' => $this->rolAtributosRepository->listItems($id),
            'arbol' => $this->rolAtributosRepository->arbolEnabled(),
        ]);
    }
}
