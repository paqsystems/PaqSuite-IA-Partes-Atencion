<?php

namespace App\Repositories\Sp;

use PaqSuite\LaravelCore\Menu\UserEffectiveMenuPermissions;

/**
 * Unión OR de `pq_rol_atributos` para los roles del usuario en la empresa (GEN-07).
 */
final class SpUserEffectiveMenuPermissions implements UserEffectiveMenuPermissions
{
    public function __construct(
        private readonly SpCaller $spCaller
    ) {
    }

    public function forUserEmpresa(int $userId, int $empresaId): array
    {
        $rows = $this->spCaller->call('pq_sp_user_menu_permisos_efectivos', [
            'user_id' => $userId,
            'empresa_id' => $empresaId,
        ]);

        $map = [];
        foreach ($rows as $row) {
            $menuId = (int) ($row->menuId ?? $row->menu_id ?? 0);
            if ($menuId <= 0) {
                continue;
            }
            $map[$menuId] = [
                'create' => (bool) ($row->permisoAlta ?? $row->permiso_alta ?? false),
                'delete' => (bool) ($row->permisoBaja ?? $row->permiso_baja ?? false),
                'update' => (bool) ($row->permisoModi ?? $row->permiso_modi ?? false),
                'report' => (bool) ($row->permisoRepo ?? $row->permiso_repo ?? false),
            ];
        }

        return $map;
    }
}
