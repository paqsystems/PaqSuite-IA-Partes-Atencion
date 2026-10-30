<?php

namespace App\Services\Partes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Contratos SP `pq_sp_partes_paquete_*` (TR-013). Runtime MONO vía Query Builder.
 */
final class PartesPaqueteOperations
{
    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    public static function dispatch(string $procedure, array $params = []): array
    {
        return match ($procedure) {
            'pq_sp_partes_paquete_list' => self::list($params),
            'pq_sp_partes_paquete_get' => self::get($params),
            'pq_sp_partes_paquete_upsert' => self::upsert($params),
            'pq_sp_partes_paquete_delete' => self::delete($params),
            default => throw new PartesPaqueteException('partes.paquete.procedureUnknown', 500),
        };
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function list(array $params): array
    {
        self::assertActorAsistente($params);
        self::assertSupervisor($params);

        $fechaDesde = trim((string) ($params['p_fecha_desde'] ?? ''));
        $fechaHasta = trim((string) ($params['p_fecha_hasta'] ?? ''));
        if ($fechaDesde === '' || $fechaHasta === '') {
            self::fail('partes.paquete.fechasRequeridas');
        }

        $q = self::baseJoin()
            ->whereDate('r.fecha', '>=', $fechaDesde)
            ->whereDate('r.fecha', '<=', $fechaHasta)
            ->where('r.es_tarea', 0);

        if (! empty($params['p_cliente_id'])) {
            $q->where('r.cliente_id', (int) $params['p_cliente_id']);
        }

        $page = max(1, (int) ($params['p_page'] ?? 1));
        $pageSize = min(200, max(1, (int) ($params['p_page_size'] ?? 50)));
        $total = (clone $q)->count();
        $rows = $q->orderByDesc('r.fecha')->orderByDesc('r.id')
            ->forPage($page, $pageSize)
            ->get(self::selectColumns());

        $out = [];
        foreach ($rows as $row) {
            $mapped = self::mapRow($row);
            $mapped['_total'] = $total;
            $out[] = (object) $mapped;
        }

        return $out;
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function get(array $params): array
    {
        self::assertActorAsistente($params);
        self::assertSupervisor($params);
        $id = (int) ($params['p_id'] ?? 0);
        $row = self::baseJoin()->where('r.id', $id)->first(self::selectColumns());
        if ($row === null) {
            self::fail('partes.paquete.notFound', 404);
        }
        if ((bool) ($row->es_tarea ?? true)) {
            self::fail('partes.paquete.notPaquete');
        }

        return [(object) self::mapRow($row)];
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function upsert(array $params): array
    {
        self::assertActorAsistente($params);
        self::assertSupervisor($params);

        $id = isset($params['p_id']) && $params['p_id'] !== null ? (int) $params['p_id'] : null;
        $actorAsistenteId = (int) $params['p_actor_asistente_id'];

        $clienteId = (int) ($params['p_cliente_id'] ?? 0);
        $fecha = trim((string) ($params['p_fecha'] ?? ''));
        $duracion = (int) ($params['p_duracion_minutos'] ?? 0);
        $observacion = trim((string) ($params['p_observacion'] ?? ''));
        $confirmarFutura = (bool) ($params['p_confirmar_fecha_futura'] ?? false);

        if ($clienteId <= 0 || $fecha === '' || $actorAsistenteId <= 0) {
            self::fail('partes.paquete.camposObligatorios');
        }
        if ($observacion === '') {
            self::fail('partes.paquete.observacionRequerida');
        }

        $tipoDefaultId = self::resolveTipoDefaultId();
        self::assertDuracion($duracion);
        self::assertFechaFutura($fecha, $confirmarFutura);

        $now = now();

        if ($id === null) {
            self::assertClienteUsable($clienteId);
            self::assertTipoEnUniverso($clienteId, $tipoDefaultId);

            $payload = [
                'usuario_id' => $actorAsistenteId,
                'cliente_id' => $clienteId,
                'tipo_tarea_id' => $tipoDefaultId,
                'fecha' => $fecha,
                'duracion_minutos' => $duracion,
                'sin_cargo' => false,
                'presencial' => false,
                'observacion' => $observacion,
                'cerrado' => false,
                'es_tarea' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
                $payload['row_version'] = 1;
            }
            $id = (int) DB::table('PQ_PARTES_REGISTRO_TAREA')->insertGetId($payload);
        } else {
            $existing = DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->first();
            if ($existing === null) {
                self::fail('partes.paquete.notFound', 404);
            }
            if ((bool) ($existing->es_tarea ?? true)) {
                self::fail('partes.paquete.notPaquete');
            }
            self::assertRowVersion($existing->row_version, (string) ($params['p_row_version'] ?? ''));

            if ((int) $existing->cliente_id !== $clienteId) {
                self::assertClienteUsable($clienteId);
            }
            self::assertTipoEnUniverso($clienteId, $tipoDefaultId);

            $patch = [
                'cliente_id' => $clienteId,
                'tipo_tarea_id' => $tipoDefaultId,
                'fecha' => $fecha,
                'duracion_minutos' => $duracion,
                'sin_cargo' => false,
                'presencial' => false,
                'observacion' => $observacion,
                'es_tarea' => false,
                'cerrado' => false,
                'updated_at' => $now,
            ];
            if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
                $patch['row_version'] = ((int) $existing->row_version) + 1;
            }
            DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->update($patch);
        }

        return self::get(array_merge($params, ['p_id' => $id]));
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function delete(array $params): array
    {
        self::assertActorAsistente($params);
        self::assertSupervisor($params);
        $id = (int) ($params['p_id'] ?? 0);
        $existing = DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->first();
        if ($existing === null) {
            self::fail('partes.paquete.notFound', 404);
        }
        if ((bool) ($existing->es_tarea ?? true)) {
            self::fail('partes.paquete.notPaquete');
        }
        self::assertRowVersion($existing->row_version, (string) ($params['p_row_version'] ?? ''));
        DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->delete();

        return [(object) ['deleted' => 1]];
    }

    private static function resolveTipoDefaultId(): int
    {
        $id = DB::table('PQ_PARTES_TIPOS_TAREA')
            ->where('is_default', 1)
            ->where('activo', 1)
            ->where('inhabilitado', 0)
            ->value('id');
        if ($id === null) {
            self::fail('partes.paquete.tipoDefaultFaltante');
        }

        return (int) $id;
    }

    /** @param array<string, mixed> $params */
    private static function assertSupervisor(array $params): void
    {
        if (! (bool) ($params['p_actor_es_supervisor'] ?? false)) {
            self::fail('partes.paquete.soloSupervisor', 403);
        }
    }

    /** @param array<string, mixed> $params */
    private static function assertActorAsistente(array $params): void
    {
        if (empty($params['p_actor_asistente_id'])) {
            self::fail('partes.paquete.soloSupervisor', 403);
        }
    }

    private static function assertRowVersion(mixed $dbValue, string $clientHex): void
    {
        if ($clientHex === '' || PartesTareaOperations::encodeRowVersion($dbValue) !== strtoupper(trim($clientHex))) {
            self::fail('partes.paquete.conflictoVersion', 409);
        }
    }

    private static function assertDuracion(int $duracion): void
    {
        if ($duracion < 60 || $duracion > 14400 || ($duracion % 60) !== 0) {
            self::fail('partes.paquete.duracionInvalida');
        }
    }

    private static function assertFechaFutura(string $fecha, bool $confirmada): void
    {
        $hoy = now()->startOfDay();
        try {
            $f = \Illuminate\Support\Carbon::parse($fecha)->startOfDay();
        } catch (\Throwable) {
            self::fail('partes.paquete.fechaInvalida');
        }
        if ($f->gt($hoy) && ! $confirmada) {
            self::fail('partes.paquete.fechaFuturaConfirmacion');
        }
    }

    private static function assertClienteUsable(int $id): void
    {
        $ok = DB::table('PQ_PARTES_CLIENTES')
            ->where('id', $id)->where('activo', 1)->where('inhabilitado', 0)->exists();
        if (! $ok) {
            self::fail('partes.paquete.clienteNoUsable');
        }
    }

    private static function assertTipoEnUniverso(int $clienteId, int $tipoId): void
    {
        $tipo = DB::table('PQ_PARTES_TIPOS_TAREA')
            ->where('id', $tipoId)->where('activo', 1)->where('inhabilitado', 0)->first();
        if ($tipo === null) {
            self::fail('partes.paquete.tipoNoUsable');
        }
        if ((bool) $tipo->is_generico) {
            return;
        }
        $asig = DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')
            ->where('cliente_id', $clienteId)
            ->where('tipo_tarea_id', $tipoId)
            ->exists();
        if (! $asig) {
            self::fail('partes.paquete.tipoFueraUniverso');
        }
    }

    private static function baseJoin()
    {
        return DB::table('PQ_PARTES_REGISTRO_TAREA as r')
            ->join('PQ_PARTES_USUARIOS as u', 'u.id', '=', 'r.usuario_id')
            ->join('PQ_PARTES_CLIENTES as c', 'c.id', '=', 'r.cliente_id')
            ->join('PQ_PARTES_TIPOS_TAREA as t', 't.id', '=', 'r.tipo_tarea_id');
    }

    /** @return list<string> */
    private static function selectColumns(): array
    {
        return [
            'r.id', 'r.usuario_id', 'r.cliente_id', 'r.tipo_tarea_id', 'r.fecha',
            'r.duracion_minutos', 'r.sin_cargo', 'r.presencial', 'r.observacion', 'r.cerrado',
            'r.es_tarea', 'r.row_version', 'r.created_at', 'r.updated_at',
            'u.code as usuario_code', 'u.nombre as usuario_nombre',
            'c.code as cliente_code', 'c.nombre as cliente_nombre',
            't.code as tipo_tarea_code', 't.descripcion as tipo_tarea_descripcion',
        ];
    }

    /**
     * @param  object  $row
     * @return array<string, mixed>
     */
    private static function mapRow(object $row): array
    {
        return [
            'id' => (int) $row->id,
            'usuario_id' => (int) $row->usuario_id,
            'cliente_id' => (int) $row->cliente_id,
            'tipo_tarea_id' => (int) $row->tipo_tarea_id,
            'fecha' => (string) $row->fecha,
            'duracion_minutos' => (int) $row->duracion_minutos,
            'sin_cargo' => (bool) $row->sin_cargo,
            'presencial' => (bool) $row->presencial,
            'observacion' => (string) $row->observacion,
            'cerrado' => (bool) $row->cerrado,
            'es_tarea' => (bool) ($row->es_tarea ?? false),
            'row_version' => PartesTareaOperations::encodeRowVersion($row->row_version),
            'usuario_code' => (string) $row->usuario_code,
            'usuario_nombre' => (string) $row->usuario_nombre,
            'cliente_code' => (string) $row->cliente_code,
            'cliente_nombre' => (string) $row->cliente_nombre,
            'tipo_tarea_code' => (string) $row->tipo_tarea_code,
            'tipo_tarea_descripcion' => (string) $row->tipo_tarea_descripcion,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ];
    }

    private static function fail(string $respuesta, int $status = 422): never
    {
        throw new PartesPaqueteException($respuesta, $status);
    }
}
