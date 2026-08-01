<?php

namespace App\Services\Partes;

use Illuminate\Support\Facades\DB;

/**
 * Contratos SP informes/dashboard (TR-006) + paquete-horas cuenta corriente (TR-006-update).
 */
final class PartesInformeOperations
{
    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    public static function dispatch(string $procedure, array $params = []): array
    {
        return match ($procedure) {
            'pq_sp_partes_informe_agrupado' => self::agrupado($params),
            'pq_sp_partes_dashboard_snapshot' => self::dashboardSnapshot($params),
            'pq_sp_partes_informe_paquete_horas' => self::paqueteHoras($params),
            default => throw new PartesTareaException('partes.informe.procedureUnknown', 500),
        };
    }

    public static function resolveDashboardTopN(): int
    {
        $row = DB::table('pq_parametros_gral')
            ->where('programa', 'Partes')
            ->where('clave', 'PartesDashboardTopN')
            ->first();
        $value = $row !== null ? (int) ($row->valor_int ?? 0) : 0;

        return $value >= 1 ? $value : 10;
    }

    public static function resolveDashboardRefreshSeg(): int
    {
        $row = DB::table('pq_parametros_gral')
            ->where('programa', 'Partes')
            ->where('clave', 'PartesDashboardRefreshSeg')
            ->first();

        return max(0, (int) ($row->valor_int ?? 60));
    }

    /** Signed minutes: tarea suma, compra resta. */
    public static function signedMinutos(bool $esTarea, int $duracionMinutos): int
    {
        return $esTarea ? $duracionMinutos : -$duracionMinutos;
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function agrupado(array $params): array
    {
        self::assertActor($params);
        $fechaDesde = trim((string) ($params['p_fecha_desde'] ?? ''));
        $fechaHasta = trim((string) ($params['p_fecha_hasta'] ?? ''));
        if ($fechaDesde === '' || $fechaHasta === '') {
            self::fail('partes.tarea.fechasRequeridas');
        }
        $eje = (string) ($params['p_eje'] ?? '');
        if (! in_array($eje, ['cliente', 'asistente', 'tipo', 'fecha'], true)) {
            self::fail('partes.consulta.ejeInvalido');
        }
        $granularidad = (string) ($params['p_granularidad_fecha'] ?? '');
        if ($eje === 'fecha' && ! in_array($granularidad, ['dia', 'mes'], true)) {
            self::fail('partes.consulta.granularidadRequerida');
        }

        $q = self::baseFiltered($params, $fechaDesde, $fechaHasta, true);

        $aggSelect = [
            DB::raw('SUM(r.duracion_minutos) as total_minutos'),
            DB::raw('COUNT(r.id) as cantidad_tareas'),
            DB::raw('SUM(CASE WHEN r.sin_cargo = 1 THEN 1 ELSE 0 END) as cantidad_sin_cargo'),
            DB::raw('SUM(CASE WHEN r.presencial = 1 THEN 1 ELSE 0 END) as cantidad_presencial'),
        ];

        $rows = match ($eje) {
            'cliente' => $q->groupBy('c.id', 'c.code', 'c.nombre', 'c.erp_cliente', 'c.erp_articulo')
                ->orderBy('c.code')
                ->get(array_merge([
                    'c.id as eje_key',
                    'c.code as eje_codigo',
                    'c.nombre as eje_descripcion',
                    'c.erp_cliente',
                    'c.erp_articulo',
                ], $aggSelect)),
            'asistente' => $q->groupBy('u.id', 'u.code', 'u.nombre')
                ->orderBy('u.code')
                ->get(array_merge([
                    'u.id as eje_key',
                    'u.code as eje_codigo',
                    'u.nombre as eje_descripcion',
                ], $aggSelect)),
            'tipo' => $q->groupBy('t.id', 't.code', 't.descripcion')
                ->orderBy('t.code')
                ->get(array_merge([
                    't.id as eje_key',
                    't.code as eje_codigo',
                    't.descripcion as eje_descripcion',
                ], $aggSelect)),
            default => self::agruparPorFecha($q, $granularidad),
        };

        $out = [];
        foreach ($rows as $row) {
            $out[] = (object) [
                'eje_key' => $row->eje_key,
                'eje_codigo' => (string) ($row->eje_codigo ?? ''),
                'eje_descripcion' => (string) ($row->eje_descripcion ?? ''),
                'erp_cliente' => (string) ($row->erp_cliente ?? ''),
                'erp_articulo' => (string) ($row->erp_articulo ?? ''),
                'total_minutos' => (int) $row->total_minutos,
                'cantidad_tareas' => (int) $row->cantidad_tareas,
                'cantidad_sin_cargo' => (int) ($row->cantidad_sin_cargo ?? 0),
                'cantidad_presencial' => (int) ($row->cantidad_presencial ?? 0),
            ];
        }

        return $out;
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function dashboardSnapshot(array $params): array
    {
        self::assertActor($params);
        $fechaDesde = trim((string) ($params['p_fecha_desde'] ?? ''));
        $fechaHasta = trim((string) ($params['p_fecha_hasta'] ?? ''));
        if ($fechaDesde === '' || $fechaHasta === '') {
            self::fail('partes.tarea.fechasRequeridas');
        }
        $topN = max(1, (int) ($params['p_top_n'] ?? self::resolveDashboardTopN()));

        $base = self::baseFiltered($params, $fechaDesde, $fechaHasta, true);
        $summary = (clone $base)->selectRaw('COALESCE(SUM(r.duracion_minutos),0) as total_minutos, COUNT(r.id) as cantidad_tareas')->first();

        $top = self::baseFiltered($params, $fechaDesde, $fechaHasta, true)
            ->groupBy('c.id', 'c.code', 'c.nombre')
            ->orderByDesc(DB::raw('SUM(r.duracion_minutos)'))
            ->limit($topN)
            ->get([
                'c.id as clave',
                'c.code as codigo',
                'c.nombre as descripcion',
                DB::raw('SUM(r.duracion_minutos) as total_minutos'),
                DB::raw('COUNT(r.id) as cantidad_tareas'),
            ]);

        $topRows = [];
        foreach ($top as $row) {
            $topRows[] = [
                'clave' => (int) $row->clave,
                'codigo' => (string) $row->codigo,
                'descripcion' => (string) $row->descripcion,
                'totalMinutos' => (int) $row->total_minutos,
                'cantidadTareas' => (int) $row->cantidad_tareas,
            ];
        }

        return [(object) [
            'total_minutos' => (int) ($summary->total_minutos ?? 0),
            'cantidad_tareas' => (int) ($summary->cantidad_tareas ?? 0),
            'top_json' => json_encode($topRows, JSON_UNESCAPED_UNICODE),
        ]];
    }

    /**
     * Cuenta corriente: saldo inicial + movimientos del periodo (sin filtrar es_tarea).
     *
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private static function paqueteHoras(array $params): array
    {
        self::assertActor($params);
        $fechaDesde = trim((string) ($params['p_fecha_desde'] ?? ''));
        $fechaHasta = trim((string) ($params['p_fecha_hasta'] ?? ''));
        if ($fechaDesde === '' || $fechaHasta === '') {
            self::fail('partes.tarea.fechasRequeridas');
        }

        $prevRows = self::baseScoped($params, soloTareas: false)
            ->whereDate('r.fecha', '<', $fechaDesde)
            ->get(['r.es_tarea', 'r.duracion_minutos']);

        $saldoInicial = 0;
        foreach ($prevRows as $prev) {
            $saldoInicial += self::signedMinutos((bool) $prev->es_tarea, (int) $prev->duracion_minutos);
        }

        $movRows = self::baseFiltered($params, $fechaDesde, $fechaHasta, false)
            ->orderBy('r.fecha')
            ->orderBy('r.id')
            ->get(self::detalleSelectColumns());

        $items = [];
        $running = $saldoInicial;
        $items[] = (object) [
            'id' => -1,
            'es_saldo_inicial' => true,
            'fecha' => $fechaDesde,
            'usuario_id' => null,
            'cliente_id' => null,
            'tipo_tarea_id' => null,
            'duracion_minutos' => $saldoInicial,
            'sin_cargo' => false,
            'presencial' => false,
            'observacion' => 'Saldo inicial',
            'cerrado' => false,
            'es_tarea' => true,
            'usuario_code' => '',
            'usuario_nombre' => '',
            'cliente_code' => '',
            'cliente_nombre' => '',
            'erp_cliente' => '',
            'erp_articulo' => '',
            'tipo_tarea_code' => '',
            'tipo_tarea_descripcion' => '',
            'saldo' => $saldoInicial,
        ];

        foreach ($movRows as $row) {
            $esTarea = (bool) ($row->es_tarea ?? true);
            $duracion = (int) $row->duracion_minutos;
            $running += self::signedMinutos($esTarea, $duracion);
            $items[] = (object) [
                'id' => (int) $row->id,
                'es_saldo_inicial' => false,
                'fecha' => (string) $row->fecha,
                'usuario_id' => (int) $row->usuario_id,
                'cliente_id' => (int) $row->cliente_id,
                'tipo_tarea_id' => (int) $row->tipo_tarea_id,
                'duracion_minutos' => $duracion,
                'sin_cargo' => (bool) $row->sin_cargo,
                'presencial' => (bool) $row->presencial,
                'observacion' => (string) $row->observacion,
                'cerrado' => (bool) $row->cerrado,
                'es_tarea' => $esTarea,
                'usuario_code' => (string) $row->usuario_code,
                'usuario_nombre' => (string) $row->usuario_nombre,
                'cliente_code' => (string) $row->cliente_code,
                'cliente_nombre' => (string) $row->cliente_nombre,
                'erp_cliente' => (string) ($row->erp_cliente ?? ''),
                'erp_articulo' => (string) ($row->erp_articulo ?? ''),
                'tipo_tarea_code' => (string) $row->tipo_tarea_code,
                'tipo_tarea_descripcion' => (string) $row->tipo_tarea_descripcion,
                'saldo' => $running,
            ];
        }

        return [(object) [
            'saldo_inicial' => $saldoInicial,
            'items_json' => json_encode($items, JSON_UNESCAPED_UNICODE),
            'total' => count($items),
        ]];
    }

    /** @return list<string|\Illuminate\Database\Query\Expression> */
    private static function detalleSelectColumns(): array
    {
        return [
            'r.id', 'r.usuario_id', 'r.cliente_id', 'r.tipo_tarea_id', 'r.fecha',
            'r.duracion_minutos', 'r.sin_cargo', 'r.presencial', 'r.observacion', 'r.cerrado',
            'r.es_tarea',
            'u.code as usuario_code', 'u.nombre as usuario_nombre',
            'c.code as cliente_code', 'c.nombre as cliente_nombre',
            'c.erp_cliente', 'c.erp_articulo',
            't.code as tipo_tarea_code', 't.descripcion as tipo_tarea_descripcion',
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return \Illuminate\Database\Query\Builder
     */
    private static function baseFiltered(
        array $params,
        string $fechaDesde,
        string $fechaHasta,
        bool $soloTareas = true
    ) {
        $q = self::baseScoped($params, $soloTareas);
        $q->whereDate('r.fecha', '>=', $fechaDesde)
            ->whereDate('r.fecha', '<=', $fechaHasta);

        return $q;
    }

    /**
     * Join + delimitación de rol/filtros (sin rango de fechas).
     *
     * @param  array<string, mixed>  $params
     * @return \Illuminate\Database\Query\Builder
     */
    private static function baseScoped(array $params, bool $soloTareas = true)
    {
        $q = DB::table('PQ_PARTES_REGISTRO_TAREA as r')
            ->join('PQ_PARTES_USUARIOS as u', 'u.id', '=', 'r.usuario_id')
            ->join('PQ_PARTES_CLIENTES as c', 'c.id', '=', 'r.cliente_id')
            ->join('PQ_PARTES_TIPOS_TAREA as t', 't.id', '=', 'r.tipo_tarea_id');

        if ($soloTareas) {
            $q->where('r.es_tarea', 1);
        }

        $tipo = (string) ($params['p_actor_tipo_funcional'] ?? 'asistente');
        if ($tipo === 'cliente') {
            $clienteId = (int) ($params['p_actor_cliente_id'] ?? 0);
            $q->where('r.cliente_id', $clienteId);
        } else {
            $esSupervisor = (int) ($params['p_actor_es_supervisor'] ?? 0) === 1
                || ($params['p_actor_es_supervisor'] ?? false) === true;
            $actorId = (int) ($params['p_actor_asistente_id'] ?? 0);
            if (! $esSupervisor) {
                $q->where('r.usuario_id', $actorId);
            } elseif (! empty($params['p_usuario_id'])) {
                $q->where('r.usuario_id', (int) $params['p_usuario_id']);
            }
        }

        if (! empty($params['p_cliente_id']) && $tipo !== 'cliente') {
            $q->where('r.cliente_id', (int) $params['p_cliente_id']);
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

    /** @param \Illuminate\Database\Query\Builder $q @return \Illuminate\Support\Collection */
    private static function agruparPorFecha($q, string $granularidad)
    {
        $driver = DB::connection()->getDriverName();
        if ($granularidad === 'mes') {
            $expr = $driver === 'sqlite'
                ? "strftime('%Y-%m', r.fecha)"
                : "FORMAT(r.fecha, 'yyyy-MM')";
        } else {
            $expr = $driver === 'sqlite'
                ? "strftime('%Y-%m-%d', r.fecha)"
                : "FORMAT(r.fecha, 'yyyy-MM-dd')";
        }

        return $q->groupBy(DB::raw($expr))
            ->orderBy(DB::raw($expr))
            ->get([
                DB::raw("$expr as eje_key"),
                DB::raw("$expr as eje_codigo"),
                DB::raw("$expr as eje_descripcion"),
                DB::raw('SUM(r.duracion_minutos) as total_minutos'),
                DB::raw('COUNT(r.id) as cantidad_tareas'),
                DB::raw('SUM(CASE WHEN r.sin_cargo = 1 THEN 1 ELSE 0 END) as cantidad_sin_cargo'),
                DB::raw('SUM(CASE WHEN r.presencial = 1 THEN 1 ELSE 0 END) as cantidad_presencial'),
            ]);
    }

    /** @param array<string, mixed> $params */
    private static function assertActor(array $params): void
    {
        $tipo = (string) ($params['p_actor_tipo_funcional'] ?? '');
        if ($tipo === 'cliente' && ! empty($params['p_actor_cliente_id'])) {
            return;
        }
        if ($tipo === 'asistente' && ! empty($params['p_actor_asistente_id'])) {
            return;
        }
        self::fail('partes.tarea.forbidden', 403);
    }

    private static function fail(string $respuesta, int $status = 422): never
    {
        throw new PartesTareaException($respuesta, $status);
    }
}
