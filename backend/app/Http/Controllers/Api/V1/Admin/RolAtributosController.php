<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\Security\RolAtributosRepository;

/**
 * Roles y atributos por opción de menú (GEN-06-roles-atributos).
 * Árbol jerárquico con menuTitulo/children/flags (SPEC, como template RolesController::atributos).
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

        $items = $this->mapItemsForApi($this->rolAtributosRepository->listItems($id));
        $arbol = $this->mapArbolForApi($this->rolAtributosRepository->arbolEnabled());

        return ApiResponse::success([
            'accesoTotal' => (bool) $rol['accesoTotal'],
            'rol' => $rol,
            'items' => $items,
            'arbol' => $arbol,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $rol = $this->rolAtributosRepository->getRolSummary($id);
        if ($rol === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        if ($rol['accesoTotal'] === true) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['respuesta' => 'roles.atributos.accesoTotal']
            );
        }

        $validator = Validator::make($request->all(), [
            // `present` (no `required`): permite items=[] para vaciar el set (sync PUT).
            'items' => ['present', 'array'],
            'items.*.menuId' => ['required', 'integer'],
            'items.*.create' => ['sometimes', 'boolean'],
            'items.*.delete' => ['sometimes', 'boolean'],
            'items.*.update' => ['sometimes', 'boolean'],
            'items.*.report' => ['sometimes', 'boolean'],
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

        $allowed = array_flip($this->rolAtributosRepository->menuIdsProcesoEnabled());
        $items = [];
        foreach ($validator->validated()['items'] as $item) {
            $menuId = (int) $item['menuId'];
            if (! isset($allowed[$menuId])) {
                return ApiResponse::errorFromCatalog(
                    PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                    ['respuesta' => 'roles.atributos.menuIdInvalid']
                );
            }
            $items[] = $this->normalizeItemForRepository($item);
        }

        $this->rolAtributosRepository->replaceItems($id, $items);

        return $this->show($id);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function mapArbolForApi(array $rows): array
    {
        return array_map(static function (array $node): array {
            $padreId = $node['padreId'] ?? null;

            return [
                'menuId' => (int) $node['menuId'],
                'padreId' => $padreId !== null && (int) $padreId > 0 ? (int) $padreId : null,
                'titulo' => (string) $node['titulo'],
                'esProceso' => (bool) $node['esProceso'],
            ];
        }, $rows);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function mapItemsForApi(array $rows): array
    {
        return array_map(static function (array $item): array {
            return [
                'menuId' => (int) $item['menuId'],
                'permisoAlta' => (bool) ($item['permisoAlta'] ?? $item['create'] ?? false),
                'permisoBaja' => (bool) ($item['permisoBaja'] ?? $item['delete'] ?? false),
                'permisoModi' => (bool) ($item['permisoModi'] ?? $item['update'] ?? false),
                'permisoRepo' => (bool) ($item['permisoRepo'] ?? $item['report'] ?? false),
            ];
        }, $rows);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function normalizeItemForRepository(array $item): array
    {
        return [
            'menuId' => (int) $item['menuId'],
            'create' => (bool) ($item['create'] ?? $item['permisoAlta'] ?? false),
            'delete' => (bool) ($item['delete'] ?? $item['permisoBaja'] ?? false),
            'update' => (bool) ($item['update'] ?? $item['permisoModi'] ?? false),
            'report' => (bool) ($item['report'] ?? $item['permisoRepo'] ?? false),
        ];
    }
}
