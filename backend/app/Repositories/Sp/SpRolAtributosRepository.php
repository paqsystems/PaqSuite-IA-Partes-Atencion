<?php

namespace App\Repositories\Sp;

use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Security\RolAtributosRepository;

/**
 * GEN-06-roles-atributos vía SP `pq_sp_admin_roles_atributos_*` / `pq_sp_admin_menus_arbol_enabled`.
 * API SPEC: create/delete/update/report ↔ DB permiso_alta/baja/modi/repo.
 */
final class SpRolAtributosRepository implements RolAtributosRepository
{
    public function __construct(
        private readonly SpCaller $spCaller
    ) {
    }

    public function getRolSummary(int $rolId): ?array
    {
        $row = $this->spCaller->callFirst('pq_sp_admin_roles_get', ['id' => $rolId]);

        if ($row === null) {
            return null;
        }

        $descripcion = $row->descripcion ?? null;

        return [
            'id' => (int) $row->id,
            'codigo' => (string) $row->codigo,
            'nombre' => (string) $row->nombre,
            'descripcion' => $descripcion !== null ? (string) $descripcion : null,
            'accesoTotal' => (bool) $row->accesoTotal,
        ];
    }

    public function arbolEnabled(): array
    {
        $rows = $this->spCaller->call('pq_sp_admin_menus_arbol_enabled');

        return array_map(static fn (object $row): array => [
            'menuId' => (int) $row->menuId,
            'padreId' => $row->padreId !== null ? (int) $row->padreId : null,
            'titulo' => (string) $row->titulo,
            'esProceso' => (bool) $row->esProceso,
        ], $rows);
    }

    public function menuIdsProcesoEnabled(): array
    {
        return array_values(array_map(
            static fn (array $node): int => $node['menuId'],
            array_filter($this->arbolEnabled(), static fn (array $node): bool => $node['esProceso'])
        ));
    }

    public function listItems(int $rolId): array
    {
        $rows = $this->spCaller->call('pq_sp_admin_roles_atributos_get', ['rol_id' => $rolId]);

        return array_map(static fn (object $row): array => [
            'menuId' => (int) $row->menuId,
            'create' => (bool) ($row->permisoAlta ?? $row->create ?? false),
            'delete' => (bool) ($row->permisoBaja ?? $row->delete ?? false),
            'update' => (bool) ($row->permisoModi ?? $row->update ?? false),
            'report' => (bool) ($row->permisoRepo ?? $row->report ?? false),
        ], $rows);
    }

    public function replaceItems(int $rolId, array $items): void
    {
        DB::transaction(function () use ($rolId, $items): void {
            $this->spCaller->execute('pq_sp_admin_roles_atributos_delete_all', ['rol_id' => $rolId]);

            foreach ($items as $item) {
                $menuId = (int) ($item['menuId'] ?? 0);
                if ($menuId <= 0) {
                    continue;
                }

                $create = (bool) ($item['create'] ?? $item['permisoAlta'] ?? false);
                $delete = (bool) ($item['delete'] ?? $item['permisoBaja'] ?? false);
                $update = (bool) ($item['update'] ?? $item['permisoModi'] ?? false);
                $report = (bool) ($item['report'] ?? $item['permisoRepo'] ?? false);

                $this->spCaller->execute('pq_sp_admin_roles_atributos_insert', [
                    'rol_id' => $rolId,
                    'menu_id' => $menuId,
                    'permiso_alta' => $create,
                    'permiso_baja' => $delete,
                    'permiso_modi' => $update,
                    'permiso_repo' => $report,
                ]);
            }
        });
    }
}
