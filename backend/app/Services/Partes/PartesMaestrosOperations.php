<?php

namespace App\Services\Partes;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Implementación Query Builder de contratos SP Partes maestros (fallback sqlite / tests).
 * En SQL Server el runtime MUST usar SpCaller → SP desplegados (migración TR-003).
 */
final class PartesMaestrosOperations
{
    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    public static function dispatch(string $procedure, array $params): array
    {
        return match ($procedure) {
            'pq_sp_partes_usuarios_list' => self::usuariosList($params),
            'pq_sp_partes_usuarios_get' => self::usuariosGet($params),
            'pq_sp_partes_usuarios_upsert' => self::usuariosUpsert($params),
            'pq_sp_partes_usuarios_set_estado' => self::usuariosSetEstado($params),
            'pq_sp_partes_usuarios_delete' => self::usuariosDelete($params),
            'pq_sp_partes_clientes_list' => self::clientesList($params),
            'pq_sp_partes_clientes_get' => self::clientesGet($params),
            'pq_sp_partes_clientes_upsert' => self::clientesUpsert($params),
            'pq_sp_partes_clientes_set_estado' => self::clientesSetEstado($params),
            'pq_sp_partes_clientes_delete' => self::clientesDelete($params),
            'pq_sp_partes_clientes_set_acceso' => self::clientesSetAcceso($params),
            'pq_sp_partes_tipos_cliente_list' => self::tiposClienteList($params),
            'pq_sp_partes_tipos_cliente_get' => self::tiposClienteGet($params),
            'pq_sp_partes_tipos_cliente_upsert' => self::tiposClienteUpsert($params),
            'pq_sp_partes_tipos_cliente_set_estado' => self::tiposClienteSetEstado($params),
            'pq_sp_partes_tipos_cliente_delete' => self::tiposClienteDelete($params),
            'pq_sp_partes_tipos_tarea_list' => self::tiposTareaList($params),
            'pq_sp_partes_tipos_tarea_get' => self::tiposTareaGet($params),
            'pq_sp_partes_tipos_tarea_upsert' => self::tiposTareaUpsert($params),
            'pq_sp_partes_tipos_tarea_set_estado' => self::tiposTareaSetEstado($params),
            'pq_sp_partes_tipos_tarea_delete' => self::tiposTareaDelete($params),
            'pq_sp_partes_cliente_tipo_tarea_list' => self::clienteTipoTareaList($params),
            'pq_sp_partes_cliente_tipo_tarea_upsert' => self::clienteTipoTareaUpsert($params),
            'pq_sp_partes_cliente_tipo_tarea_delete' => self::clienteTipoTareaDelete($params),
            'pq_sp_partes_catalogo_usuarios_dominio' => self::catalogoUsuariosDominio(),
            'pq_sp_partes_catalogo_clientes' => self::catalogoClientes(),
            'pq_sp_partes_catalogo_tipos_cliente' => self::catalogoTiposCliente(),
            'pq_sp_partes_catalogo_tipos_tarea_universo' => self::catalogoTiposTareaUniverso($params),
            default => throw new RuntimeException("Partes SP {$procedure} sin Operations"),
        };
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function usuariosList(array $params): array
    {
        $q = DB::table('PQ_PARTES_USUARIOS as u');
        if (! empty($params['p_code'])) {
            $q->where('u.code', 'like', '%'.$params['p_code'].'%');
        }
        $page = max(1, (int) ($params['p_page'] ?? 1));
        $pageSize = max(1, min(200, (int) ($params['p_page_size'] ?? 50)));
        $total = (clone $q)->count();
        $rows = $q->orderBy('u.code')->forPage($page, $pageSize)->get([
            'u.id', 'u.user_id', 'u.code', 'u.nombre', 'u.email', 'u.supervisor', 'u.activo', 'u.inhabilitado',
        ]);

        return self::withTotal($rows, $total);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function usuariosGet(array $params): array
    {
        $row = DB::table('PQ_PARTES_USUARIOS')->where('id', (int) $params['p_id'])->first();
        if ($row === null) {
            self::fail('partes.maestros.notFound');
        }

        return [$row];
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function usuariosUpsert(array $params): array
    {
        $id = isset($params['p_id']) && $params['p_id'] !== null ? (int) $params['p_id'] : null;
        $userId = (int) ($params['p_user_id'] ?? 0);
        $code = trim((string) ($params['p_code'] ?? ''));
        if ($userId <= 0) {
            self::fail('partes.maestros.userIdRequired');
        }
        if ($code === '') {
            self::fail('partes.maestros.codeRequired');
        }
        self::assertExclusividad($userId, 'usuario');
        self::assertUniqueCode('PQ_PARTES_USUARIOS', $code, $id);

        $now = now();
        $payload = [
            'user_id' => $userId,
            'code' => $code,
            'nombre' => (string) ($params['p_nombre'] ?? ''),
            'email' => $params['p_email'] ?? null,
            'supervisor' => (bool) ($params['p_supervisor'] ?? false),
            'activo' => (bool) ($params['p_activo'] ?? true),
            'inhabilitado' => (bool) ($params['p_inhabilitado'] ?? false),
            'updated_at' => $now,
        ];

        if ($id === null) {
            $payload['created_at'] = $now;
            $id = DB::table('PQ_PARTES_USUARIOS')->insertGetId($payload);
        } else {
            if (! DB::table('PQ_PARTES_USUARIOS')->where('id', $id)->exists()) {
                self::fail('partes.maestros.notFound');
            }
            DB::table('PQ_PARTES_USUARIOS')->where('id', $id)->update($payload);
        }

        return self::usuariosGet(['p_id' => $id]);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function usuariosSetEstado(array $params): array
    {
        $id = (int) $params['p_id'];
        if (! DB::table('PQ_PARTES_USUARIOS')->where('id', $id)->exists()) {
            self::fail('partes.maestros.notFound');
        }
        $patch = ['updated_at' => now()];
        if (array_key_exists('p_activo', $params) && $params['p_activo'] !== null) {
            $patch['activo'] = (bool) $params['p_activo'];
        }
        if (array_key_exists('p_inhabilitado', $params) && $params['p_inhabilitado'] !== null) {
            $patch['inhabilitado'] = (bool) $params['p_inhabilitado'];
        }
        DB::table('PQ_PARTES_USUARIOS')->where('id', $id)->update($patch);

        return self::usuariosGet(['p_id' => $id]);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function usuariosDelete(array $params): array
    {
        $id = (int) $params['p_id'];
        if (DB::table('PQ_PARTES_REGISTRO_TAREA')->where('usuario_id', $id)->exists()) {
            self::fail('partes.maestros.deleteConReferencias');
        }
        $deleted = DB::table('PQ_PARTES_USUARIOS')->where('id', $id)->delete();
        if ($deleted === 0) {
            self::fail('partes.maestros.notFound');
        }

        return [(object) ['deleted' => 1]];
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function clientesList(array $params): array
    {
        $q = DB::table('PQ_PARTES_CLIENTES as c')
            ->leftJoin('PQ_PARTES_TIPOS_CLIENTE as t', 't.id', '=', 'c.tipo_cliente_id');
        if (! empty($params['p_code'])) {
            $q->where('c.code', 'like', '%'.$params['p_code'].'%');
        }
        $page = max(1, (int) ($params['p_page'] ?? 1));
        $pageSize = max(1, min(200, (int) ($params['p_page_size'] ?? 50)));
        $total = (clone $q)->count();
        $rows = $q->orderBy('c.code')->forPage($page, $pageSize)->get([
            'c.id', 'c.user_id', 'c.code', 'c.nombre', 'c.email', 'c.tipo_cliente_id',
            't.code as tipo_cliente_code', 't.descripcion as tipo_cliente_descripcion',
            'c.activo', 'c.inhabilitado',
        ]);

        return self::withTotal($rows, $total);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function clientesGet(array $params): array
    {
        $row = DB::table('PQ_PARTES_CLIENTES')->where('id', (int) $params['p_id'])->first();
        if ($row === null) {
            self::fail('partes.maestros.notFound');
        }

        return [$row];
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function clientesUpsert(array $params): array
    {
        $id = isset($params['p_id']) && $params['p_id'] !== null ? (int) $params['p_id'] : null;
        $code = trim((string) ($params['p_code'] ?? ''));
        $tipoClienteId = (int) ($params['p_tipo_cliente_id'] ?? 0);
        $userId = array_key_exists('p_user_id', $params) ? $params['p_user_id'] : null;
        if ($code === '' || $tipoClienteId <= 0) {
            self::fail('partes.maestros.validationFailed');
        }
        if ($userId !== null && $userId !== '') {
            self::assertExclusividad((int) $userId, 'cliente');
        }
        self::assertUniqueCode('PQ_PARTES_CLIENTES', $code, $id);

        $now = now();
        $payload = [
            'user_id' => $userId !== null && $userId !== '' ? (int) $userId : null,
            'code' => $code,
            'nombre' => (string) ($params['p_nombre'] ?? ''),
            'email' => $params['p_email'] ?? null,
            'tipo_cliente_id' => $tipoClienteId,
            'activo' => (bool) ($params['p_activo'] ?? true),
            'inhabilitado' => (bool) ($params['p_inhabilitado'] ?? false),
            'updated_at' => $now,
        ];

        if ($id === null) {
            $payload['created_at'] = $now;
            $id = DB::table('PQ_PARTES_CLIENTES')->insertGetId($payload);
        } else {
            if (! DB::table('PQ_PARTES_CLIENTES')->where('id', $id)->exists()) {
                self::fail('partes.maestros.notFound');
            }
            DB::table('PQ_PARTES_CLIENTES')->where('id', $id)->update($payload);
        }

        return self::clientesGet(['p_id' => $id]);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function clientesSetEstado(array $params): array
    {
        $id = (int) $params['p_id'];
        if (! DB::table('PQ_PARTES_CLIENTES')->where('id', $id)->exists()) {
            self::fail('partes.maestros.notFound');
        }
        $patch = ['updated_at' => now()];
        if (array_key_exists('p_activo', $params) && $params['p_activo'] !== null) {
            $patch['activo'] = (bool) $params['p_activo'];
        }
        if (array_key_exists('p_inhabilitado', $params) && $params['p_inhabilitado'] !== null) {
            $patch['inhabilitado'] = (bool) $params['p_inhabilitado'];
        }
        DB::table('PQ_PARTES_CLIENTES')->where('id', $id)->update($patch);

        return self::clientesGet(['p_id' => $id]);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function clientesDelete(array $params): array
    {
        $id = (int) $params['p_id'];
        if (DB::table('PQ_PARTES_REGISTRO_TAREA')->where('cliente_id', $id)->exists()
            || DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')->where('cliente_id', $id)->exists()) {
            self::fail('partes.maestros.deleteConReferencias');
        }
        if (DB::table('PQ_PARTES_CLIENTES')->where('id', $id)->delete() === 0) {
            self::fail('partes.maestros.notFound');
        }

        return [(object) ['deleted' => 1]];
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function clientesSetAcceso(array $params): array
    {
        $id = (int) $params['p_id'];
        $userId = array_key_exists('p_user_id', $params) ? $params['p_user_id'] : null;
        if (! DB::table('PQ_PARTES_CLIENTES')->where('id', $id)->exists()) {
            self::fail('partes.maestros.notFound');
        }
        if ($userId !== null && $userId !== '') {
            self::assertExclusividad((int) $userId, 'cliente');
            DB::table('PQ_PARTES_CLIENTES')->where('id', $id)->update([
                'user_id' => (int) $userId,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('PQ_PARTES_CLIENTES')->where('id', $id)->update([
                'user_id' => null,
                'updated_at' => now(),
            ]);
        }

        return self::clientesGet(['p_id' => $id]);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function tiposClienteList(array $params): array
    {
        return self::simpleCatalogList('PQ_PARTES_TIPOS_CLIENTE', $params);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function tiposClienteGet(array $params): array
    {
        return self::simpleCatalogGet('PQ_PARTES_TIPOS_CLIENTE', $params);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function tiposClienteUpsert(array $params): array
    {
        return self::simpleCatalogUpsert('PQ_PARTES_TIPOS_CLIENTE', $params);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function tiposClienteSetEstado(array $params): array
    {
        return self::simpleCatalogSetEstado('PQ_PARTES_TIPOS_CLIENTE', $params, 'PQ_PARTES_CLIENTES', 'tipo_cliente_id');
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function tiposClienteDelete(array $params): array
    {
        $id = (int) $params['p_id'];
        if (DB::table('PQ_PARTES_CLIENTES')->where('tipo_cliente_id', $id)->exists()) {
            self::fail('partes.maestros.deleteConReferencias');
        }

        return self::simpleCatalogDelete('PQ_PARTES_TIPOS_CLIENTE', $id);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function tiposTareaList(array $params): array
    {
        return self::simpleCatalogList('PQ_PARTES_TIPOS_TAREA', $params, true);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function tiposTareaGet(array $params): array
    {
        return self::simpleCatalogGet('PQ_PARTES_TIPOS_TAREA', $params);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function tiposTareaUpsert(array $params): array
    {
        $id = isset($params['p_id']) && $params['p_id'] !== null ? (int) $params['p_id'] : null;
        $code = trim((string) ($params['p_code'] ?? ''));
        if ($code === '') {
            self::fail('partes.maestros.codeRequired');
        }
        self::assertUniqueCode('PQ_PARTES_TIPOS_TAREA', $code, $id);
        $isDefault = (bool) ($params['p_is_default'] ?? false);
        $isGenerico = (bool) ($params['p_is_generico'] ?? false);
        if ($isDefault) {
            $isGenerico = true;
        }

        $now = now();
        $payload = [
            'code' => $code,
            'descripcion' => (string) ($params['p_descripcion'] ?? ''),
            'is_generico' => $isGenerico,
            'is_default' => false,
            'activo' => (bool) ($params['p_activo'] ?? true),
            'inhabilitado' => (bool) ($params['p_inhabilitado'] ?? false),
            'updated_at' => $now,
        ];

        if ($id === null) {
            $payload['created_at'] = $now;
            $id = DB::table('PQ_PARTES_TIPOS_TAREA')->insertGetId($payload);
        } else {
            if (! DB::table('PQ_PARTES_TIPOS_TAREA')->where('id', $id)->exists()) {
                self::fail('partes.maestros.notFound');
            }
            DB::table('PQ_PARTES_TIPOS_TAREA')->where('id', $id)->update($payload);
        }

        if ($isDefault) {
            if (DB::connection()->getDriverName() === 'sqlsrv') {
                DB::statement('EXEC dbo.pq_sp_partes_tipos_tarea_marcar_default @p_tipo_tarea_id = ?', [$id]);
            } else {
                DB::table('PQ_PARTES_TIPOS_TAREA')->where('is_default', true)->where('id', '<>', $id)->update(['is_default' => false]);
                DB::table('PQ_PARTES_TIPOS_TAREA')->where('id', $id)->update(['is_default' => true, 'is_generico' => true]);
            }
        }

        return self::tiposTareaGet(['p_id' => $id]);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function tiposTareaSetEstado(array $params): array
    {
        $id = (int) $params['p_id'];
        $row = DB::table('PQ_PARTES_TIPOS_TAREA')->where('id', $id)->first();
        if ($row === null) {
            self::fail('partes.maestros.notFound');
        }
        $inhabilitado = array_key_exists('p_inhabilitado', $params) ? $params['p_inhabilitado'] : null;
        if ($inhabilitado !== null && (bool) $inhabilitado && (bool) $row->is_default) {
            self::fail('partes.maestros.tipoDefaultNoInhabilitar');
        }
        $patch = ['updated_at' => now()];
        if (array_key_exists('p_activo', $params) && $params['p_activo'] !== null) {
            $patch['activo'] = (bool) $params['p_activo'];
        }
        if ($inhabilitado !== null) {
            $patch['inhabilitado'] = (bool) $inhabilitado;
        }
        DB::table('PQ_PARTES_TIPOS_TAREA')->where('id', $id)->update($patch);

        return self::tiposTareaGet(['p_id' => $id]);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function tiposTareaDelete(array $params): array
    {
        $id = (int) $params['p_id'];
        if (DB::table('PQ_PARTES_REGISTRO_TAREA')->where('tipo_tarea_id', $id)->exists()
            || DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')->where('tipo_tarea_id', $id)->exists()) {
            self::fail('partes.maestros.deleteConReferencias');
        }

        return self::simpleCatalogDelete('PQ_PARTES_TIPOS_TAREA', $id);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function clienteTipoTareaList(array $params): array
    {
        $q = DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA as a')
            ->join('PQ_PARTES_CLIENTES as c', 'c.id', '=', 'a.cliente_id')
            ->join('PQ_PARTES_TIPOS_TAREA as t', 't.id', '=', 'a.tipo_tarea_id');
        if (! empty($params['p_cliente_id'])) {
            $q->where('a.cliente_id', (int) $params['p_cliente_id']);
        }
        $rows = $q->orderBy('c.code')->orderBy('t.code')->get([
            'a.id', 'a.cliente_id', 'a.tipo_tarea_id',
            'c.code as cliente_code', 'c.nombre as cliente_nombre',
            't.code as tipo_tarea_code', 't.descripcion as tipo_tarea_descripcion', 't.is_generico',
        ]);

        return $rows->all();
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function clienteTipoTareaUpsert(array $params): array
    {
        $clienteId = (int) ($params['p_cliente_id'] ?? 0);
        $tipoId = (int) ($params['p_tipo_tarea_id'] ?? 0);
        $tipo = DB::table('PQ_PARTES_TIPOS_TAREA')->where('id', $tipoId)->first();
        if ($tipo === null || ! DB::table('PQ_PARTES_CLIENTES')->where('id', $clienteId)->exists()) {
            self::fail('partes.maestros.notFound');
        }
        if ((bool) $tipo->is_generico) {
            self::fail('partes.maestros.tipoGenericoNoAsignable');
        }
        $existing = DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')
            ->where('cliente_id', $clienteId)
            ->where('tipo_tarea_id', $tipoId)
            ->first();
        if ($existing !== null) {
            return [$existing];
        }
        $id = DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')->insertGetId([
            'cliente_id' => $clienteId,
            'tipo_tarea_id' => $tipoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')->where('id', $id)->first()];
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function clienteTipoTareaDelete(array $params): array
    {
        $id = (int) $params['p_id'];
        if (DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')->where('id', $id)->delete() === 0) {
            self::fail('partes.maestros.notFound');
        }

        return [(object) ['deleted' => 1]];
    }

    /** @return list<object> */
    private static function catalogoUsuariosDominio(): array
    {
        return DB::table('PQ_PARTES_USUARIOS')
            ->where('activo', 1)->where('inhabilitado', 0)
            ->orderBy('code')
            ->get(['id', 'code', 'nombre', 'supervisor'])
            ->all();
    }

    /** @return list<object> */
    private static function catalogoClientes(): array
    {
        return DB::table('PQ_PARTES_CLIENTES')
            ->where('activo', 1)->where('inhabilitado', 0)
            ->orderBy('code')
            ->get(['id', 'code', 'nombre'])
            ->all();
    }

    /** @return list<object> */
    private static function catalogoTiposCliente(): array
    {
        return DB::table('PQ_PARTES_TIPOS_CLIENTE')
            ->where('activo', 1)->where('inhabilitado', 0)
            ->orderBy('code')
            ->get(['id', 'code', 'descripcion'])
            ->all();
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function catalogoTiposTareaUniverso(array $params): array
    {
        if (empty($params['p_cliente_id'])) {
            self::fail('partes.maestros.clienteIdRequired');
        }
        $clienteId = (int) $params['p_cliente_id'];
        $genericos = DB::table('PQ_PARTES_TIPOS_TAREA')
            ->where('activo', 1)->where('inhabilitado', 0)->where('is_generico', 1)
            ->get(['id', 'code', 'descripcion', 'is_generico', 'is_default']);
        $asignados = DB::table('PQ_PARTES_TIPOS_TAREA as t')
            ->join('PQ_PARTES_CLIENTE_TIPO_TAREA as a', 'a.tipo_tarea_id', '=', 't.id')
            ->where('a.cliente_id', $clienteId)
            ->where('t.activo', 1)->where('t.inhabilitado', 0)
            ->get(['t.id', 't.code', 't.descripcion', 't.is_generico', 't.is_default']);

        $map = [];
        foreach ($genericos->concat($asignados) as $row) {
            $map[(int) $row->id] = $row;
        }

        return array_values($map);
    }

    private static function assertExclusividad(int $userId, string $lado): void
    {
        try {
            DB::statement('EXEC dbo.pq_sp_partes_assert_user_id_exclusividad @p_user_id = ?, @p_lado = ?', [$userId, $lado]);
        } catch (\Throwable $e) {
            if (DB::connection()->getDriverName() === 'sqlite') {
                if ($lado === 'usuario' && DB::table('PQ_PARTES_CLIENTES')->where('user_id', $userId)->exists()) {
                    self::fail('partes.maestros.exclusividadUserId');
                }
                if ($lado === 'cliente' && DB::table('PQ_PARTES_USUARIOS')->where('user_id', $userId)->exists()) {
                    self::fail('partes.maestros.exclusividadUserId');
                }

                return;
            }
            if (str_contains($e->getMessage(), 'PARTES_EXCLUSIVIDAD_USER_ID')) {
                self::fail('partes.maestros.exclusividadUserId');
            }
            throw $e;
        }
    }

    private static function assertUniqueCode(string $table, string $code, ?int $ignoreId): void
    {
        $q = DB::table($table)->where('code', $code);
        if ($ignoreId !== null) {
            $q->where('id', '<>', $ignoreId);
        }
        if ($q->exists()) {
            self::fail('partes.maestros.codeDuplicate');
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $rows
     * @return list<object>
     */
    private static function withTotal($rows, int $total): array
    {
        $out = [];
        foreach ($rows as $row) {
            $arr = (array) $row;
            $arr['_total'] = $total;
            $out[] = (object) $arr;
        }

        return $out;
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function simpleCatalogList(string $table, array $params, bool $withFlags = false): array
    {
        $q = DB::table($table);
        if (! empty($params['p_code'])) {
            $q->where('code', 'like', '%'.$params['p_code'].'%');
        }
        $page = max(1, (int) ($params['p_page'] ?? 1));
        $pageSize = max(1, min(200, (int) ($params['p_page_size'] ?? 50)));
        $total = (clone $q)->count();
        $cols = ['id', 'code', 'descripcion', 'activo', 'inhabilitado'];
        if ($withFlags) {
            $cols = array_merge($cols, ['is_generico', 'is_default']);
        }
        $rows = $q->orderBy('code')->forPage($page, $pageSize)->get($cols);

        return self::withTotal($rows, $total);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function simpleCatalogGet(string $table, array $params): array
    {
        $row = DB::table($table)->where('id', (int) $params['p_id'])->first();
        if ($row === null) {
            self::fail('partes.maestros.notFound');
        }

        return [$row];
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function simpleCatalogUpsert(string $table, array $params): array
    {
        $id = isset($params['p_id']) && $params['p_id'] !== null ? (int) $params['p_id'] : null;
        $code = trim((string) ($params['p_code'] ?? ''));
        if ($code === '') {
            self::fail('partes.maestros.codeRequired');
        }
        self::assertUniqueCode($table, $code, $id);
        $now = now();
        $payload = [
            'code' => $code,
            'descripcion' => (string) ($params['p_descripcion'] ?? ''),
            'activo' => (bool) ($params['p_activo'] ?? true),
            'inhabilitado' => (bool) ($params['p_inhabilitado'] ?? false),
            'updated_at' => $now,
        ];
        if ($id === null) {
            $payload['created_at'] = $now;
            $id = DB::table($table)->insertGetId($payload);
        } else {
            if (! DB::table($table)->where('id', $id)->exists()) {
                self::fail('partes.maestros.notFound');
            }
            DB::table($table)->where('id', $id)->update($payload);
        }

        return self::simpleCatalogGet($table, ['p_id' => $id]);
    }

    /** @param array<string, mixed> $params @return list<object> */
    private static function simpleCatalogSetEstado(string $table, array $params, ?string $refTable = null, ?string $refCol = null): array
    {
        $id = (int) $params['p_id'];
        if (! DB::table($table)->where('id', $id)->exists()) {
            self::fail('partes.maestros.notFound');
        }
        $patch = ['updated_at' => now()];
        if (array_key_exists('p_activo', $params) && $params['p_activo'] !== null) {
            $patch['activo'] = (bool) $params['p_activo'];
        }
        if (array_key_exists('p_inhabilitado', $params) && $params['p_inhabilitado'] !== null) {
            $patch['inhabilitado'] = (bool) $params['p_inhabilitado'];
        }
        DB::table($table)->where('id', $id)->update($patch);

        return self::simpleCatalogGet($table, ['p_id' => $id]);
    }

    /** @return list<object> */
    private static function simpleCatalogDelete(string $table, int $id): array
    {
        if (DB::table($table)->where('id', $id)->delete() === 0) {
            self::fail('partes.maestros.notFound');
        }

        return [(object) ['deleted' => 1]];
    }

    private static function fail(string $respuesta): never
    {
        throw new PartesMaestrosException($respuesta);
    }
}
