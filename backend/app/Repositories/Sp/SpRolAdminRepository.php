<?php

namespace App\Repositories\Sp;

use PaqSuite\LaravelCore\Security\RolAdminRepository;

/**
 * ABM roles (GEN-06) vía SP `pq_sp_admin_roles_*`.
 */
final class SpRolAdminRepository implements RolAdminRepository
{
    public function __construct(
        private readonly SpCaller $spCaller
    ) {
    }

    public function listAll(): array
    {
        $rows = $this->spCaller->call('pq_sp_admin_roles_list');

        return array_map(fn (object $row): array => $this->mapRow($row), $rows);
    }

    public function create(array $data): array
    {
        $row = $this->spCaller->callFirst('pq_sp_admin_roles_create', [
            'codigo' => (string) $data['codigo'],
            'nombre' => (string) $data['nombre'],
            'acceso_total' => (bool) ($data['accesoTotal'] ?? $data['acceso_total'] ?? false),
            'activo' => (bool) ($data['activo'] ?? true),
        ]);

        return $this->mapRow($row);
    }

    public function update(int $id, array $data): ?array
    {
        $params = ['id' => $id];

        if (array_key_exists('codigo', $data)) {
            $params['codigo'] = (string) $data['codigo'];
        }
        if (array_key_exists('nombre', $data)) {
            $params['nombre'] = (string) $data['nombre'];
        }
        if (array_key_exists('accesoTotal', $data) || array_key_exists('acceso_total', $data)) {
            $params['acceso_total'] = (bool) ($data['accesoTotal'] ?? $data['acceso_total']);
        }
        if (array_key_exists('activo', $data)) {
            $params['activo'] = (bool) $data['activo'];
        }

        $row = $this->spCaller->callFirst('pq_sp_admin_roles_update', $params);

        return $row === null ? null : $this->mapRow($row);
    }

    public function delete(int $id): string
    {
        $row = $this->spCaller->callFirst('pq_sp_admin_roles_delete', ['id' => $id]);
        if ($row === null) {
            return 'not_found';
        }

        $outcome = (string) ($row->outcome ?? 'not_found');

        return match ($outcome) {
            'ok' => 'ok',
            'has_permisos' => 'has_permisos',
            default => 'not_found',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRow(object $row): array
    {
        return [
            'id' => (int) $row->id,
            'codigo' => (string) $row->codigo,
            'nombre' => (string) $row->nombre,
            'accesoTotal' => (bool) $row->accesoTotal,
            'activo' => (bool) $row->activo,
        ];
    }
}
