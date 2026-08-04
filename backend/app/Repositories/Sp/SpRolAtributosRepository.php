<?php

namespace App\Repositories\Sp;

use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Security\RolAtributosRepository;

/**
 * GEN-06-roles-atributos vía SP `pq_sp_admin_roles_atributos_*` / `pq_sp_admin_menus_arbol_enabled`.
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

        return [
            'id' => (int) $row->id,
            'codigo' => (string) $row->codigo,
            'nombre' => (string) $row->nombre,
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
            'permisoAlta' => (bool) $row->permisoAlta,
            'permisoBaja' => (bool) $row->permisoBaja,
            'permisoModi' => (bool) $row->permisoModi,
            'permisoRepo' => (bool) $row->permisoRepo,
        ], $rows);
    }

    public function replaceItems(int $rolId, array $items): void
    {
        DB::transaction(function () use ($rolId, $items): void {
            $this->spCaller->execute('pq_sp_admin_roles_atributos_delete_all', ['rol_id' => $rolId]);

            foreach ($items as $item) {
                if (!$item['permisoAlta'] && !$item['permisoBaja'] && !$item['permisoModi'] && !$item['permisoRepo']) {
                    continue;
                }

                $this->spCaller->execute('pq_sp_admin_roles_atributos_insert', [
                    'rol_id' => $rolId,
                    'menu_id' => $item['menuId'],
                    'permiso_alta' => $item['permisoAlta'],
                    'permiso_baja' => $item['permisoBaja'],
                    'permiso_modi' => $item['permisoModi'],
                    'permiso_repo' => $item['permisoRepo'],
                ]);
            }
        });
    }
}
