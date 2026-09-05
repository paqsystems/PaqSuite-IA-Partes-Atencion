<?php

namespace App\Repositories\Sp;

use PaqSuite\LaravelCore\Security\EmpresaAdminRepository;

/**
 * Consulta/edición empresas (GEN-06) vía SP `pq_sp_admin_empresas_*`.
 * MONO: sin alta/baja — solo `update`. Contrato SPEC: nombreEmpresa / habilitada / theme.
 * Persistencia interna: `nombre` ↔ nombreEmpresa, `activo` ↔ habilitada.
 */
final class SpEmpresaAdminRepository implements EmpresaAdminRepository
{
    public function __construct(
        private readonly SpCaller $spCaller
    ) {
    }

    public function listAll(): array
    {
        $rows = $this->spCaller->call('pq_sp_admin_empresas_list');

        return array_map(fn (object $row): array => $this->mapRow($row), $rows);
    }

    public function findById(int $id): ?array
    {
        $row = $this->spCaller->callFirst('pq_sp_admin_empresas_get', ['id' => $id]);

        return $row === null ? null : $this->mapRow($row);
    }

    public function update(int $id, array $data): ?array
    {
        $params = ['id' => $id];

        if (array_key_exists('nombreEmpresa', $data) || array_key_exists('nombre', $data)) {
            $params['nombre'] = (string) ($data['nombreEmpresa'] ?? $data['nombre']);
        }
        if (array_key_exists('habilitada', $data) || array_key_exists('activo', $data)) {
            $params['activo'] = (bool) ($data['habilitada'] ?? $data['activo']);
        }
        if (array_key_exists('theme', $data)) {
            $params['theme'] = $data['theme'] !== null ? (string) $data['theme'] : 'generic.light';
        }

        $row = $this->spCaller->callFirst('pq_sp_admin_empresas_update', $params);

        return $row === null ? null : $this->mapRow($row);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRow(object $row): array
    {
        return [
            'id' => (int) $row->id,
            'nombreEmpresa' => (string) $row->nombre,
            'habilitada' => (bool) $row->activo,
            'theme' => $row->theme !== null ? (string) $row->theme : 'generic.light',
        ];
    }
}
