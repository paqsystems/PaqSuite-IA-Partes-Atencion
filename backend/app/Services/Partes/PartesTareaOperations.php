<?php

namespace App\Services\Partes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Contratos SP `pq_sp_partes_tarea_*` (TR-004). Runtime MONO vía Query Builder;
 * scripts T-SQL gateway = follow-up.
 */
final class PartesTareaOperations
{
    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    public static function dispatch(string $procedure, array $params = []): array
    {
        return match ($procedure) {
            'pq_sp_partes_tarea_list' => self::list($params),
            'pq_sp_partes_tarea_list_ids' => self::listIds($params),
            'pq_sp_partes_tarea_get' => self::get($params),
            'pq_sp_partes_tarea_upsert' => self::upsert($params),
            'pq_sp_partes_tarea_delete' => self::delete($params),
            'pq_sp_partes_tarea_set_cerrado' => self::setCerrado($params),
            'pq_sp_partes_tarea_masivo_set_cerrado' => self::masivoSetCerrado($params),
            'pq_sp_partes_tarea_masivo_actualizar' => self::masivoActualizar($params),
            default => throw new PartesTareaException('partes.tarea.procedureUnknown', 500),
        };
    }

    public const MASIVO_TECH_MAX = 5000;

    public static function resolveTramoMinutos(): int
    {
        $row = DB::table('pq_parametros_gral')
            ->where('programa', 'Partes')
            ->where('clave', 'PartesDuracionTramoMin')
            ->first();
        $value = $row !== null ? (int) ($row->valor_int ?? 0) : 0;

        return $value > 0 ? $value : 15;
    }

    /** Tope de negocio (0 = sin tope negocio; runtime usa MASIVO_TECH_MAX). */
    public static function resolveMasivoMaxIdsNegocio(): int
    {
        $row = DB::table('pq_parametros_gral')
            ->where('programa', 'Partes')
            ->where('clave', 'PartesMasivoMaxIds')
            ->first();

        return max(0, (int) ($row->valor_int ?? 0));
    }

    public static function resolveMasivoMaxIdsEfectivo(): int
    {
        $negocio = self::resolveMasivoMaxIdsNegocio();

        return $negocio > 0 ? $negocio : self::MASIVO_TECH_MAX;
    }

    public static function encodeRowVersion(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_resource($value)) {
            $bin = stream_get_contents($value);
            $value = $bin === false ? '' : $bin;
        }
        if (is_string($value) && $value !== '' && ! ctype_digit($value)) {
            if (str_starts_with(strtolower($value), '0x')) {
                return strtoupper(substr($value, 2));
            }
            // binary / opaque from sqlsrv
            if (! ctype_xdigit($value)) {
                return strtoupper(bin2hex($value));
            }

            return strtoupper($value);
        }

        return strtoupper(str_pad(dechex((int) $value), 16, '0', STR_PAD_LEFT));
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function list(array $params): array
    {
        self::assertActorAsistente($params);
        $q = self::filteredQuery($params);
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
    private static function listIds(array $params): array
    {
        self::assertActorAsistente($params);
        if (! (bool) ($params['p_actor_es_supervisor'] ?? false)) {
            self::fail('partes.masivo.forbidden', 403);
        }
        $q = self::filteredQuery($params);
        $total = (clone $q)->count();
        $max = self::resolveMasivoMaxIdsEfectivo();
        if ($total > $max) {
            $negocio = self::resolveMasivoMaxIdsNegocio();
            self::fail($negocio > 0 ? 'partes.masivo.topeExcedido' : 'partes.masivo.loteDemasiadoGrande');
        }
        $rows = $q->orderByDesc('r.fecha')->orderByDesc('r.id')
            ->get(['r.id', 'r.row_version', 'r.fecha', 'u.code as usuario_code']);

        $out = [];
        foreach ($rows as $row) {
            $out[] = (object) [
                'id' => (int) $row->id,
                'row_version' => self::encodeRowVersion($row->row_version),
                'fecha' => (string) $row->fecha,
                'usuario_code' => (string) $row->usuario_code,
                '_total' => $total,
            ];
        }

        return $out;
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function masivoSetCerrado(array $params): array
    {
        self::assertActorAsistente($params);
        if (! (bool) ($params['p_actor_es_supervisor'] ?? false)) {
            self::fail('partes.masivo.forbidden', 403);
        }

        $accion = (string) ($params['p_accion'] ?? '');
        if (! in_array($accion, ['cerrar', 'reabrir'], true)) {
            self::fail('partes.masivo.accionInvalida');
        }
        $cerrado = $accion === 'cerrar';

        $itemsRaw = $params['p_items_json'] ?? '[]';
        if (is_array($itemsRaw)) {
            $items = $itemsRaw;
        } else {
            $decoded = json_decode((string) $itemsRaw, true);
            $items = is_array($decoded) ? $decoded : null;
        }
        if (! is_array($items) || $items === []) {
            self::fail('partes.masivo.emptySelection');
        }

        $count = count($items);
        $negocio = self::resolveMasivoMaxIdsNegocio();
        if ($negocio > 0 && $count > $negocio) {
            self::fail('partes.masivo.topeExcedido');
        }
        if ($count > self::MASIVO_TECH_MAX) {
            self::fail('partes.masivo.loteDemasiadoGrande');
        }

        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    self::fail('partes.masivo.itemInvalido');
                }
                $id = (int) ($item['id'] ?? 0);
                $rowVersion = (string) ($item['rowVersion'] ?? $item['row_version'] ?? '');
                if ($id <= 0 || $rowVersion === '') {
                    self::fail('partes.masivo.itemInvalido');
                }
                $existing = DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->first();
                if ($existing === null) {
                    self::fail('partes.masivo.idInexistente');
                }
                if (! (bool) ($existing->es_tarea ?? true)) {
                    self::fail('partes.masivo.noEsTarea');
                }
                if (self::encodeRowVersion($existing->row_version) !== strtoupper(trim($rowVersion))) {
                    self::fail('partes.masivo.conflictoVersion', 409);
                }
                if ((bool) $existing->cerrado === $cerrado) {
                    continue;
                }
                $patch = [
                    'cerrado' => $cerrado,
                    'updated_at' => now(),
                ];
                if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
                    $patch['row_version'] = ((int) $existing->row_version) + 1;
                }
                DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->update($patch);
            }
            DB::commit();
        } catch (PartesTareaException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [(object) [
            'accion' => $accion,
            'afectados' => $count,
            'ok' => 1,
        ]];
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function masivoActualizar(array $params): array
    {
        self::assertActorAsistente($params);
        if (! (bool) ($params['p_actor_es_supervisor'] ?? false)) {
            self::fail('partes.masivo.forbidden', 403);
        }

        $camposRaw = $params['p_campos_json'] ?? $params['p_campos'] ?? [];
        if (is_array($camposRaw)) {
            $campos = $camposRaw;
        } else {
            $decoded = json_decode((string) $camposRaw, true);
            $campos = is_array($decoded) ? $decoded : null;
        }
        if (! is_array($campos) || $campos === []) {
            self::fail('partes.masivo.atributoInvalido');
        }

        $hasTipo = array_key_exists('tipoTareaId', $campos) || array_key_exists('tipo_tarea_id', $campos);
        $hasSinCargo = array_key_exists('sinCargo', $campos) || array_key_exists('sin_cargo', $campos);
        $hasPresencial = array_key_exists('presencial', $campos);
        $hasUsuario = array_key_exists('usuarioId', $campos) || array_key_exists('usuario_id', $campos);
        $hasFecha = array_key_exists('fecha', $campos);

        if (! $hasTipo && ! $hasSinCargo && ! $hasPresencial && ! $hasUsuario && ! $hasFecha) {
            self::fail('partes.masivo.atributoInvalido');
        }

        $tipoTareaId = $hasTipo
            ? (int) ($campos['tipoTareaId'] ?? $campos['tipo_tarea_id'] ?? 0)
            : null;
        if ($hasTipo && ($tipoTareaId === null || $tipoTareaId <= 0)) {
            self::fail('partes.masivo.atributoInvalido');
        }

        $sinCargo = $hasSinCargo
            ? filter_var($campos['sinCargo'] ?? $campos['sin_cargo'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        if ($hasSinCargo && $sinCargo === null) {
            $raw = $campos['sinCargo'] ?? $campos['sin_cargo'];
            $sinCargo = (bool) $raw;
        }

        $presencial = $hasPresencial
            ? filter_var($campos['presencial'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;
        if ($hasPresencial && $presencial === null) {
            $presencial = (bool) $campos['presencial'];
        }

        $usuarioId = $hasUsuario
            ? (int) ($campos['usuarioId'] ?? $campos['usuario_id'] ?? 0)
            : null;
        if ($hasUsuario && ($usuarioId === null || $usuarioId <= 0)) {
            self::fail('partes.masivo.atributoInvalido');
        }

        $fecha = $hasFecha ? trim((string) ($campos['fecha'] ?? '')) : null;
        if ($hasFecha && ($fecha === null || $fecha === '')) {
            self::fail('partes.masivo.atributoInvalido');
        }

        $itemsRaw = $params['p_items_json'] ?? '[]';
        if (is_array($itemsRaw)) {
            $items = $itemsRaw;
        } else {
            $decoded = json_decode((string) $itemsRaw, true);
            $items = is_array($decoded) ? $decoded : null;
        }
        if (! is_array($items) || $items === []) {
            self::fail('partes.masivo.emptySelection');
        }

        $count = count($items);
        $negocio = self::resolveMasivoMaxIdsNegocio();
        if ($negocio > 0 && $count > $negocio) {
            self::fail('partes.masivo.topeExcedido');
        }
        if ($count > self::MASIVO_TECH_MAX) {
            self::fail('partes.masivo.loteDemasiadoGrande');
        }

        if ($hasUsuario) {
            self::assertAsistenteUsable((int) $usuarioId);
        }

        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    self::fail('partes.masivo.itemInvalido');
                }
                $id = (int) ($item['id'] ?? 0);
                $rowVersion = (string) ($item['rowVersion'] ?? $item['row_version'] ?? '');
                if ($id <= 0 || $rowVersion === '') {
                    self::fail('partes.masivo.itemInvalido');
                }
                $existing = DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->first();
                if ($existing === null) {
                    self::fail('partes.masivo.idInexistente');
                }
                if (! (bool) ($existing->es_tarea ?? true)) {
                    self::fail('partes.masivo.noEsTarea');
                }
                if (self::encodeRowVersion($existing->row_version) !== strtoupper(trim($rowVersion))) {
                    self::fail('partes.masivo.conflictoVersion', 409);
                }

                if ($hasTipo) {
                    try {
                        self::assertTipoEnUniverso((int) $existing->cliente_id, (int) $tipoTareaId);
                    } catch (PartesTareaException $e) {
                        self::fail('partes.masivo.atributoInvalido', 422);
                    }
                }

                $patch = ['updated_at' => now()];
                $changed = false;

                if ($hasTipo && (int) $existing->tipo_tarea_id !== (int) $tipoTareaId) {
                    $patch['tipo_tarea_id'] = (int) $tipoTareaId;
                    $changed = true;
                }
                if ($hasSinCargo && (bool) $existing->sin_cargo !== (bool) $sinCargo) {
                    $patch['sin_cargo'] = (bool) $sinCargo;
                    $changed = true;
                }
                if ($hasPresencial && (bool) $existing->presencial !== (bool) $presencial) {
                    $patch['presencial'] = (bool) $presencial;
                    $changed = true;
                }
                if ($hasUsuario && (int) $existing->usuario_id !== (int) $usuarioId) {
                    $patch['usuario_id'] = (int) $usuarioId;
                    $changed = true;
                }
                if ($hasFecha) {
                    $existingFecha = substr((string) $existing->fecha, 0, 10);
                    if ($existingFecha !== (string) $fecha) {
                        $patch['fecha'] = (string) $fecha;
                        $changed = true;
                    }
                }

                if (! $changed) {
                    continue;
                }

                if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
                    $patch['row_version'] = ((int) $existing->row_version) + 1;
                }
                DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->update($patch);
            }
            DB::commit();
        } catch (PartesTareaException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [(object) [
            'accion' => 'actualizar',
            'afectados' => $count,
            'ok' => 1,
            'campos' => array_keys(array_filter([
                'tipoTareaId' => $hasTipo,
                'sinCargo' => $hasSinCargo,
                'presencial' => $hasPresencial,
                'usuarioId' => $hasUsuario,
                'fecha' => $hasFecha,
            ])),
        ]];
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function get(array $params): array
    {
        self::assertActorAsistente($params);
        $id = (int) ($params['p_id'] ?? 0);
        $row = self::baseJoin()->where('r.id', $id)->first(self::selectColumns());
        if ($row === null) {
            self::fail('partes.tarea.notFound', 404);
        }
        self::assertCanAccessRow($params, (int) $row->usuario_id);

        return [(object) self::mapRow($row)];
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function upsert(array $params): array
    {
        self::assertActorAsistente($params);
        $id = isset($params['p_id']) && $params['p_id'] !== null ? (int) $params['p_id'] : null;
        $esSupervisor = (bool) ($params['p_actor_es_supervisor'] ?? false);
        $actorId = (int) $params['p_actor_asistente_id'];

        $usuarioId = isset($params['p_usuario_id']) ? (int) $params['p_usuario_id'] : $actorId;
        if (! $esSupervisor) {
            if ($usuarioId !== $actorId) {
                self::fail('partes.tarea.forbiddenOwner', 403);
            }
            $usuarioId = $actorId;
        }

        $clienteId = (int) ($params['p_cliente_id'] ?? 0);
        $tipoTareaId = (int) ($params['p_tipo_tarea_id'] ?? 0);
        $fecha = trim((string) ($params['p_fecha'] ?? ''));
        $duracion = (int) ($params['p_duracion_minutos'] ?? 0);
        $observacion = trim((string) ($params['p_observacion'] ?? ''));
        $sinCargo = (bool) ($params['p_sin_cargo'] ?? false);
        $presencial = (bool) ($params['p_presencial'] ?? false);
        $confirmarFutura = (bool) ($params['p_confirmar_fecha_futura'] ?? false);

        if ($clienteId <= 0 || $tipoTareaId <= 0 || $fecha === '' || $usuarioId <= 0) {
            self::fail('partes.tarea.camposObligatorios');
        }
        if ($observacion === '') {
            self::fail('partes.tarea.observacionRequerida');
        }

        self::assertAsistenteUsable($usuarioId);
        self::assertClienteUsable($clienteId);
        self::assertTipoEnUniverso($clienteId, $tipoTareaId);
        self::assertDuracion($duracion);
        self::assertFechaFutura($fecha, $confirmarFutura);

        $now = now();
        if ($id === null) {
            $payload = [
                'usuario_id' => $usuarioId,
                'cliente_id' => $clienteId,
                'tipo_tarea_id' => $tipoTareaId,
                'fecha' => $fecha,
                'duracion_minutos' => $duracion,
                'sin_cargo' => $sinCargo,
                'presencial' => $presencial,
                'observacion' => $observacion,
                'cerrado' => false,
                'es_tarea' => true,
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
                self::fail('partes.tarea.notFound', 404);
            }
            self::assertCanAccessRow($params, (int) $existing->usuario_id);
            if ((bool) $existing->cerrado) {
                self::fail('partes.tarea.cerradaNoEditable');
            }
            self::assertRowVersion($existing->row_version, (string) ($params['p_row_version'] ?? ''));

            $patch = [
                'usuario_id' => $usuarioId,
                'cliente_id' => $clienteId,
                'tipo_tarea_id' => $tipoTareaId,
                'fecha' => $fecha,
                'duracion_minutos' => $duracion,
                'sin_cargo' => $sinCargo,
                'presencial' => $presencial,
                'observacion' => $observacion,
                'es_tarea' => true,
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
        $id = (int) ($params['p_id'] ?? 0);
        $existing = DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->first();
        if ($existing === null) {
            self::fail('partes.tarea.notFound', 404);
        }
        self::assertCanAccessRow($params, (int) $existing->usuario_id);
        if ((bool) $existing->cerrado) {
            self::fail('partes.tarea.cerradaNoEliminable');
        }
        self::assertRowVersion($existing->row_version, (string) ($params['p_row_version'] ?? ''));
        DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->delete();

        return [(object) ['deleted' => 1]];
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function setCerrado(array $params): array
    {
        self::assertActorAsistente($params);
        if (! (bool) ($params['p_actor_es_supervisor'] ?? false)) {
            self::fail('partes.tarea.soloSupervisor', 403);
        }
        $id = (int) ($params['p_id'] ?? 0);
        $existing = DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->first();
        if ($existing === null) {
            self::fail('partes.tarea.notFound', 404);
        }
        self::assertRowVersion($existing->row_version, (string) ($params['p_row_version'] ?? ''));

        $cerrado = (bool) ($params['p_cerrado'] ?? false);
        $patch = [
            'cerrado' => $cerrado,
            'updated_at' => now(),
        ];
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            $patch['row_version'] = ((int) $existing->row_version) + 1;
        }
        DB::table('PQ_PARTES_REGISTRO_TAREA')->where('id', $id)->update($patch);

        return self::get(array_merge($params, ['p_id' => $id]));
    }

    /** @param array<string, mixed> $params */
    private static function assertActorAsistente(array $params): void
    {
        $tipo = (string) ($params['p_actor_tipo_funcional'] ?? 'asistente');
        if ($tipo === 'cliente') {
            if (empty($params['p_actor_cliente_id'])) {
                self::fail('partes.tarea.forbidden', 403);
            }

            return;
        }
        if (empty($params['p_actor_asistente_id'])) {
            self::fail('partes.tarea.forbidden', 403);
        }
    }

    /**
     * @param  array<string, mixed>  $params
     * @return \Illuminate\Database\Query\Builder
     */
    private static function filteredQuery(array $params)
    {
        $fechaDesde = trim((string) ($params['p_fecha_desde'] ?? ''));
        $fechaHasta = trim((string) ($params['p_fecha_hasta'] ?? ''));
        if ($fechaDesde === '' || $fechaHasta === '') {
            self::fail('partes.tarea.fechasRequeridas');
        }

        $q = self::baseJoin();
        $q->whereDate('r.fecha', '>=', $fechaDesde)
            ->whereDate('r.fecha', '<=', $fechaHasta)
            ->where('r.es_tarea', 1);

        $tipo = (string) ($params['p_actor_tipo_funcional'] ?? 'asistente');
        if ($tipo === 'cliente') {
            $q->where('r.cliente_id', (int) $params['p_actor_cliente_id']);
        } else {
            $actorId = (int) $params['p_actor_asistente_id'];
            $esSupervisor = (int) ($params['p_actor_es_supervisor'] ?? 0) === 1
                || ($params['p_actor_es_supervisor'] ?? false) === true;
            if (! $esSupervisor) {
                $q->where('r.usuario_id', $actorId);
            } elseif (! empty($params['p_usuario_id'])) {
                $q->where('r.usuario_id', (int) $params['p_usuario_id']);
            }
            if (! empty($params['p_cliente_id'])) {
                $q->where('r.cliente_id', (int) $params['p_cliente_id']);
            }
        }

        if (! empty($params['p_tipo_tarea_id'])) {
            $q->where('r.tipo_tarea_id', (int) $params['p_tipo_tarea_id']);
        }

        $estado = (string) ($params['p_estado_cerrado'] ?? 'todas');
        if ($estado === 'abiertas') {
            $q->where('r.cerrado', 0);
        } elseif ($estado === 'cerradas') {
            $q->where('r.cerrado', 1);
        }

        return $q;
    }

    /** @param array<string, mixed> $params */
    private static function assertCanAccessRow(array $params, int $ownerUsuarioId): void
    {
        $esSupervisor = (bool) ($params['p_actor_es_supervisor'] ?? false);
        $actorId = (int) ($params['p_actor_asistente_id'] ?? 0);
        if (! $esSupervisor && $ownerUsuarioId !== $actorId) {
            self::fail('partes.tarea.forbiddenOwner', 403);
        }
    }

    private static function assertRowVersion(mixed $dbValue, string $clientHex): void
    {
        if ($clientHex === '' || self::encodeRowVersion($dbValue) !== strtoupper(trim($clientHex))) {
            self::fail('partes.tarea.conflictoVersion', 409);
        }
    }

    private static function assertDuracion(int $duracion): void
    {
        $tramo = self::resolveTramoMinutos();
        if ($duracion <= 0 || $duracion > 1440 || ($duracion % $tramo) !== 0) {
            self::fail('partes.tarea.duracionInvalida');
        }
    }

    private static function assertFechaFutura(string $fecha, bool $confirmada): void
    {
        $hoy = now()->startOfDay();
        try {
            $f = \Illuminate\Support\Carbon::parse($fecha)->startOfDay();
        } catch (\Throwable) {
            self::fail('partes.tarea.fechaInvalida');
        }
        if ($f->gt($hoy) && ! $confirmada) {
            self::fail('partes.tarea.fechaFuturaConfirmacion');
        }
    }

    private static function assertAsistenteUsable(int $id): void
    {
        $ok = DB::table('PQ_PARTES_USUARIOS')
            ->where('id', $id)->where('activo', 1)->where('inhabilitado', 0)->exists();
        if (! $ok) {
            self::fail('partes.tarea.asistenteNoUsable');
        }
    }

    private static function assertClienteUsable(int $id): void
    {
        $ok = DB::table('PQ_PARTES_CLIENTES')
            ->where('id', $id)->where('activo', 1)->where('inhabilitado', 0)->exists();
        if (! $ok) {
            self::fail('partes.tarea.clienteNoUsable');
        }
    }

    private static function assertTipoEnUniverso(int $clienteId, int $tipoId): void
    {
        $tipo = DB::table('PQ_PARTES_TIPOS_TAREA')
            ->where('id', $tipoId)->where('activo', 1)->where('inhabilitado', 0)->first();
        if ($tipo === null) {
            self::fail('partes.tarea.tipoNoUsable');
        }
        if ((bool) $tipo->is_generico) {
            return;
        }
        $asig = DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')
            ->where('cliente_id', $clienteId)
            ->where('tipo_tarea_id', $tipoId)
            ->exists();
        if (! $asig) {
            self::fail('partes.tarea.tipoFueraUniverso');
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
            'es_tarea' => (bool) ($row->es_tarea ?? true),
            'row_version' => self::encodeRowVersion($row->row_version),
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
        throw new PartesTareaException($respuesta, $status);
    }
}
