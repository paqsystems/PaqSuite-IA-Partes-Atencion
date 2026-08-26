<?php

namespace App\Repositories\Sp;

use PaqSuite\LaravelCore\Security\PermisoAdminRepository;

/**
 * ABM permisos usuario x empresa x rol (GEN-06) vía SP `pq_sp_admin_permisos_*`.
 * Contrato SPEC: usuarioId / empresaId / rolId.
 */
final class SpPermisoAdminRepository implements PermisoAdminRepository
{
    public function __construct(
        private readonly SpCaller $spCaller
    ) {
    }

    public function listByUserId(int $userId): array
    {
        return $this->listAll(['usuarioId' => $userId]);
    }

    public function listAll(array $filters = []): array
    {
        $usuarioId = isset($filters['usuarioId']) ? (int) $filters['usuarioId'] : 0;
        if ($usuarioId > 0) {
            $rows = $this->spCaller->call('pq_sp_admin_permisos_list_by_user', ['user_id' => $usuarioId]);
        } else {
            $rows = $this->spCaller->call('pq_sp_admin_permisos_list', []);
        }

        $items = array_map(fn (object $row): array => $this->mapRow($row), $rows);

        $empresaId = isset($filters['empresaId']) ? (int) $filters['empresaId'] : 0;
        if ($empresaId > 0) {
            $items = array_values(array_filter(
                $items,
                static fn (array $item): bool => (int) $item['empresaId'] === $empresaId
            ));
        }

        $rolId = isset($filters['rolId']) ? (int) $filters['rolId'] : 0;
        if ($rolId > 0) {
            $items = array_values(array_filter(
                $items,
                static fn (array $item): bool => (int) $item['rolId'] === $rolId
            ));
        }

        return $items;
    }

    public function create(array $data): array
    {
        $userId = (int) ($data['usuarioId'] ?? $data['userId'] ?? 0);

        $row = $this->spCaller->callFirst('pq_sp_admin_permisos_create', [
            'user_id' => $userId,
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
            $userId = (int) ($item['usuarioId'] ?? $item['userId'] ?? 0);
            $row = $this->spCaller->callFirst('pq_sp_admin_permisos_create_if_absent', [
                'user_id' => $userId,
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
            'usuarioId' => (int) ($row->usuarioId ?? $row->userId ?? 0),
            'usuarioNombre' => (string) ($row->usuarioNombre ?? ''),
            'empresaId' => (int) $row->empresaId,
            'empresaNombre' => (string) $row->empresaNombre,
            'rolId' => (int) $row->rolId,
            'rolNombre' => (string) $row->rolNombre,
        ];
    }
}
