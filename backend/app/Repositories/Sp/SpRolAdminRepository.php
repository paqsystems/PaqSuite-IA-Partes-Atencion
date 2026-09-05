<?php

namespace App\Repositories\Sp;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PaqSuite\LaravelCore\Security\RolAdminRepository;

/**
 * ABM roles (GEN-06) vía SP `pq_sp_admin_roles_*`.
 * Contrato SPEC: nombre, descripcion, accesoTotal.
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

    public function findById(int $id): ?array
    {
        $row = $this->spCaller->callFirst('pq_sp_admin_roles_get', ['id' => $id]);

        return $row === null ? null : $this->mapRow($row);
    }

    public function create(array $data): array
    {
        $nombre = (string) $data['nombre'];
        $codigo = isset($data['codigo']) && is_string($data['codigo']) && $data['codigo'] !== ''
            ? (string) $data['codigo']
            : $this->uniqueCodigoFromNombre($nombre);

        $row = $this->spCaller->callFirst('pq_sp_admin_roles_create', [
            'codigo' => $codigo,
            'nombre' => $nombre,
            'descripcion' => array_key_exists('descripcion', $data)
                ? ($data['descripcion'] !== null ? (string) $data['descripcion'] : null)
                : null,
            'acceso_total' => (bool) ($data['accesoTotal'] ?? $data['acceso_total'] ?? false),
            'activo' => (bool) ($data['activo'] ?? true),
        ]);

        return $this->mapRow($row);
    }

    public function update(int $id, array $data): ?array
    {
        $params = ['id' => $id];

        if (array_key_exists('codigo', $data) && is_string($data['codigo']) && $data['codigo'] !== '') {
            $params['codigo'] = (string) $data['codigo'];
        }
        if (array_key_exists('nombre', $data)) {
            $params['nombre'] = (string) $data['nombre'];
        }
        if (array_key_exists('descripcion', $data)) {
            $params['descripcion'] = $data['descripcion'] !== null ? (string) $data['descripcion'] : null;
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
        $descripcion = $row->descripcion ?? null;

        return [
            'id' => (int) $row->id,
            'nombre' => (string) $row->nombre,
            'descripcion' => $descripcion !== null ? (string) $descripcion : null,
            'accesoTotal' => (bool) $row->accesoTotal,
        ];
    }

    private function uniqueCodigoFromNombre(string $nombre): string
    {
        $base = Str::upper(Str::slug($nombre, '_'));
        if ($base === '') {
            $base = 'ROL';
        }
        $base = Str::limit($base, 48, '');
        $codigo = $base;
        $suffix = 1;
        while (DB::table('pq_roles')->where('codigo', $codigo)->exists()) {
            $codigo = Str::limit($base, 48, '').'_'.$suffix;
            $suffix++;
        }

        return $codigo;
    }
}
