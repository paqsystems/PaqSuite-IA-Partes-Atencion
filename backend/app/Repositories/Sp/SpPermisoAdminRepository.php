<?php

namespace App\Repositories\Sp;

use PaqSuite\LaravelCore\Security\PermisoAdminRepository;

/**
 * ABM permisos usuario x empresa x rol (GEN-06) vía SP `pq_sp_admin_permisos_*`.
 */
final class SpPermisoAdminRepository implements PermisoAdminRepository
{
    public function __construct(
        private readonly SpCaller $spCaller
    ) {
    }

    public function listByUserId(int $userId): array
    {
        $rows = $this->spCaller->call('pq_sp_admin_permisos_list_by_user', ['user_id' => $userId]);

        return array_map(fn (object $row): array => $this->mapRow($row), $rows);
    }

    public function listAll(): array
    {
        $rows = $this->spCaller->call('pq_sp_admin_permisos_list', []);

        return array_map(fn (object $row): array => $this->mapRow($row), $rows);
    }

    public function create(array $data): array
    {
        $row = $this->spCaller->callFirst('pq_sp_admin_permisos_create', [
            'user_id' => (int) $data['userId'],
            'empresa_id' => (int) $data['empresaId'],
            'rol_id' => (int) $data['rolId'],
        ]);

        return $this->mapRow($row);
    }

    public function createBatch(array $items): array
    {
        $creados = 0;
        $omitidos = 0;

        foreach ($items as $item) {
            $row = $this->spCaller->callFirst('pq_sp_admin_permisos_create_if_absent', [
                'user_id' => (int) $item['userId'],
                'empresa_id' => (int) $item['empresaId'],
                'rol_id' => (int) $item['rolId'],
            ]);

            if ($row !== null && (int) ($row->created ?? 0) === 1) {
                $creados++;
            } else {
                $omitidos++;
            }
        }

        return ['creados' => $creados, 'omitidos' => $omitidos];
    }

    public function deleteById(int $id): bool
    {
        $row = $this->spCaller->callFirst('pq_sp_admin_permisos_delete', ['id' => $id]);

        return $row !== null && (int) ($row->updated_rows ?? 0) > 0;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRow(object $row): array
    {
        return [
            'id' => (int) $row->id,
            'userId' => (int) $row->userId,
            'usuario' => (string) ($row->usuario ?? ''),
            'usuarioNombre' => (string) ($row->usuarioNombre ?? ''),
            'empresaId' => (int) $row->empresaId,
            'empresaNombre' => (string) $row->empresaNombre,
            'rolId' => (int) $row->rolId,
            'rolCodigo' => (string) $row->rolCodigo,
            'rolNombre' => (string) $row->rolNombre,
        ];
    }
}
