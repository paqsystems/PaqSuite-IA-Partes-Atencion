<?php

namespace App\Repositories\Sp\Partes;

use App\Repositories\Sp\SpCaller;

/**
 * Resolución de identidad funcional Partes (TR-002) vía SP MUST.
 */
final class PartesIdentidadRepository
{
    public function __construct(private readonly SpCaller $spCaller)
    {
    }

    /**
     * @return array{
     *   codigoResultado: int,
     *   tipoFuncional: ?string,
     *   asistenteId: ?int,
     *   clienteId: ?int,
     *   esSupervisor: bool,
     *   code: ?string,
     *   nombre: ?string,
     *   email: ?string
     * }
     */
    public function resolveByUserId(int $userId): array
    {
        $row = $this->spCaller->callFirst('pq_sp_partes_identidad_resolver', [
            'p_user_id' => $userId,
        ]);

        if ($row === null) {
            return [
                'codigoResultado' => 1,
                'tipoFuncional' => null,
                'asistenteId' => null,
                'clienteId' => null,
                'esSupervisor' => false,
                'code' => null,
                'nombre' => null,
                'email' => null,
            ];
        }

        return [
            'codigoResultado' => (int) ($row->codigo_resultado ?? 1),
            'tipoFuncional' => isset($row->tipo_funcional) && $row->tipo_funcional !== null
                ? (string) $row->tipo_funcional
                : null,
            'asistenteId' => isset($row->asistente_id) && $row->asistente_id !== null
                ? (int) $row->asistente_id
                : null,
            'clienteId' => isset($row->cliente_id) && $row->cliente_id !== null
                ? (int) $row->cliente_id
                : null,
            'esSupervisor' => (int) ($row->es_supervisor ?? 0) === 1,
            'code' => isset($row->code) && $row->code !== null ? (string) $row->code : null,
            'nombre' => isset($row->nombre) && $row->nombre !== null ? (string) $row->nombre : null,
            'email' => isset($row->email) && $row->email !== null ? (string) $row->email : null,
        ];
    }

    /**
     * Payload `resultado.partes` para merge en sesión.
     *
     * @return array{
     *   tipoFuncional: string,
     *   asistenteId: ?int,
     *   clienteId: ?int,
     *   esSupervisor: bool,
     *   code: string,
     *   nombre: string,
     *   email: ?string
     * }
     */
    public function toPartesPayload(array $resolved): array
    {
        return [
            'tipoFuncional' => (string) $resolved['tipoFuncional'],
            'asistenteId' => $resolved['asistenteId'],
            'clienteId' => $resolved['clienteId'],
            'esSupervisor' => (bool) $resolved['esSupervisor'],
            'code' => (string) ($resolved['code'] ?? ''),
            'nombre' => (string) ($resolved['nombre'] ?? ''),
            'email' => $resolved['email'],
        ];
    }
}
