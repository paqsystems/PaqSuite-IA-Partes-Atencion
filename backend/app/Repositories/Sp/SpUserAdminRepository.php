<?php

namespace App\Repositories\Sp;

use Illuminate\Support\Facades\Hash;
use PaqSuite\LaravelCore\Security\UserAdminRepository;

/**
 * ABM usuarios (GEN-06) vía SP `pq_sp_admin_usuarios_*` (regla BASE 74/75).
 * Contrato SPEC: codigo / nombre / email / activo (+ asignaciones en findById).
 * Persistencia interna: columna `usuario` ↔ API `codigo`.
 */
final class SpUserAdminRepository implements UserAdminRepository
{
    public function __construct(
        private readonly SpCaller $spCaller
    ) {
    }

    public function listAll(): array
    {
        $rows = $this->spCaller->call('pq_sp_admin_usuarios_list');

        return array_map(fn (object $row): array => $this->mapRow($row), $rows);
    }

    public function findById(int $id): ?array
    {
        $row = $this->spCaller->callFirst('pq_sp_admin_usuarios_get', ['id' => $id]);
        if ($row === null) {
            return null;
        }

        $payload = $this->mapRow($row);
        $payload['asignaciones'] = $this->mapAsignaciones($id);

        return $payload;
    }

    public function create(array $data): array
    {
        $codigo = (string) ($data['codigo'] ?? $data['usuario'] ?? '');
        $nombre = (string) ($data['nombre'] ?? $data['name'] ?? $codigo);

        $row = $this->spCaller->callFirst('pq_sp_admin_usuarios_create', [
            'usuario' => $codigo,
            'nombre' => $nombre,
            'email' => (string) $data['email'],
            'password_hash' => Hash::make((string) $data['password']),
            'activo' => (bool) ($data['activo'] ?? true),
        ]);

        return $this->mapRow($row);
    }

    public function update(int $id, array $data): ?array
    {
        $params = ['id' => $id];

        if (array_key_exists('codigo', $data) || array_key_exists('usuario', $data)) {
            $params['usuario'] = (string) ($data['codigo'] ?? $data['usuario']);
        }
        if (array_key_exists('nombre', $data) || array_key_exists('name', $data)) {
            $params['nombre'] = (string) ($data['nombre'] ?? $data['name']);
        }
        if (array_key_exists('email', $data)) {
            $params['email'] = (string) $data['email'];
        }
        if (array_key_exists('activo', $data)) {
            $params['activo'] = (bool) $data['activo'];
            if ($params['activo']) {
                $params['inhabilitado'] = false;
            }
        }
        if (array_key_exists('inhabilitado', $data)) {
            $params['inhabilitado'] = (bool) $data['inhabilitado'];
        }
        if (array_key_exists('password', $data) && is_string($data['password']) && $data['password'] !== '') {
            $params['password_hash'] = Hash::make($data['password']);
        }

        $row = $this->spCaller->callFirst('pq_sp_admin_usuarios_update', $params);
        if ($row === null) {
            return null;
        }

        $payload = $this->mapRow($row);
        $payload['asignaciones'] = $this->mapAsignaciones($id);

        return $payload;
    }

    public function softDelete(int $id): bool
    {
        $row = $this->spCaller->callFirst('pq_sp_admin_usuarios_soft_delete', ['id' => $id]);

        return $row !== null && (int) ($row->updated_rows ?? 0) > 0;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRow(object $row): array
    {
        return [
            'id' => (int) $row->id,
            'codigo' => (string) $row->usuario,
            'nombre' => (string) $row->nombre,
            'email' => (string) $row->email,
            'activo' => (bool) $row->activo,
        ];
    }

    /**
     * @return list<array{empresaId: int, empresaNombre: string, rolId: int, rolNombre: string}>
     */
    private function mapAsignaciones(int $userId): array
    {
        $rows = $this->spCaller->call('pq_sp_admin_permisos_list_by_user', ['user_id' => $userId]);

        return array_map(static function (object $row): array {
            return [
                'empresaId' => (int) $row->empresaId,
                'empresaNombre' => (string) ($row->empresaNombre ?? ''),
                'rolId' => (int) $row->rolId,
                'rolNombre' => (string) ($row->rolNombre ?? ''),
            ];
        }, $rows);
    }
}
