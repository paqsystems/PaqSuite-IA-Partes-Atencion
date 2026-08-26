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

        $items = $this->rolAtributosRepository->listItems($id);
        $flagsByMenu = [];
        foreach ($items as $item) {
            $flagsByMenu[(int) $item['menuId']] = $item;
        }

        $arbol = array_map(static function (array $node) use ($flagsByMenu): array {
            $flags = $flagsByMenu[(int) $node['menuId']] ?? null;

            return [
                'menuId' => (int) $node['menuId'],
                'padreId' => $node['padreId'],
                'menuTitulo' => (string) $node['titulo'],
                'esProceso' => (bool) $node['esProceso'],
                'create' => (bool) ($flags['create'] ?? false),
                'delete' => (bool) ($flags['delete'] ?? false),
                'update' => (bool) ($flags['update'] ?? false),
                'report' => (bool) ($flags['report'] ?? false),
            ];
        }, $this->rolAtributosRepository->arbolEnabled());

        return ApiResponse::success([
            'accesoTotal' => (bool) $rol['accesoTotal'],
            'rol' => $rol,
            'items' => $items,
            'arbol' => $this->buildTree($arbol),
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
            $items[] = $item;
        }

        $this->rolAtributosRepository->replaceItems($id, $items);

        return $this->show($id);
    }

    /**
     * @param  list<array<string, mixed>>  $flat
     * @return list<array<string, mixed>>
     */
    private function buildTree(array $flat): array
    {
        $nodes = [];
        foreach ($flat as $node) {
            $menuId = (int) $node['menuId'];
            $nodes[$menuId] = [
                'menuId' => $menuId,
                'padreId' => $node['padreId'],
                'menuTitulo' => (string) $node['menuTitulo'],
                'esProceso' => (bool) $node['esProceso'],
                'create' => (bool) $node['create'],
                'delete' => (bool) $node['delete'],
                'update' => (bool) $node['update'],
                'report' => (bool) $node['report'],
                'children' => [],
            ];
        }

        $roots = [];
        foreach ($nodes as $menuId => $node) {
            $padreId = $node['padreId'] !== null ? (int) $node['padreId'] : null;
            if ($padreId !== null && isset($nodes[$padreId])) {
                $nodes[$padreId]['children'][] = $menuId;
            } else {
                $roots[] = $menuId;
            }
        }

        $hydrate = function (int $menuId) use (&$hydrate, &$nodes): array {
            $node = $nodes[$menuId];
            $childIds = $node['children'];
            $node['children'] = [];
            foreach ($childIds as $childId) {
                $node['children'][] = $hydrate((int) $childId);
            }

            return $node;
        };

        return array_map(static fn (int $id): array => $hydrate($id), $roots);
    }
}
