<?php

namespace App\Repositories\Sp;

use App\Services\Partes\PartesInformeOperations;
use App\Services\Partes\PartesMaestrosOperations;
use App\Services\Partes\PartesTareaOperations;
use Illuminate\Support\Facades\DB;

/**
 * Helper host para invocar stored procedures (sqlsrv EXEC / mysql CALL).
 * En sqlite (tests) ejecuta SQL equivalente documentado en integracion-framework-sdk.md.
 * Partes maestros/tarea (TR-003/004): contratos SP vía Operations (parity gateway: scripts SP follow-up).
 */
final class SpCaller
{
    /**
     * @param  array<string, mixed>  $params  clave => valor (orden preservado)
     * @return list<object>
     */
    public function call(string $procedure, array $params = []): array
    {
        if ($this->isPartesTareaProcedure($procedure)) {
            return PartesTareaOperations::dispatch($procedure, $params);
        }

        if ($this->isPartesInformeProcedure($procedure)) {
            return PartesInformeOperations::dispatch($procedure, $params);
        }

        if ($this->isPartesMaestrosProcedure($procedure)) {
            return PartesMaestrosOperations::dispatch($procedure, $params);
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return $this->callSqliteFallback($procedure, $params);
        }

        if ($driver === 'sqlsrv') {
            return $this->callSqlsrv($procedure, $params);
        }

        return $this->callMysql($procedure, $params);
    }

    private function isPartesTareaProcedure(string $procedure): bool
    {
        return str_starts_with($procedure, 'pq_sp_partes_tarea_');
    }

    private function isPartesInformeProcedure(string $procedure): bool
    {
        return in_array($procedure, [
            'pq_sp_partes_informe_agrupado',
            'pq_sp_partes_dashboard_snapshot',
            'pq_sp_partes_informe_paquete_horas',
        ], true);
    }

    private function isPartesMaestrosProcedure(string $procedure): bool
    {
        if (! str_starts_with($procedure, 'pq_sp_partes_')) {
            return false;
        }

        if ($this->isPartesTareaProcedure($procedure)) {
            return false;
        }

        return ! in_array($procedure, [
            'pq_sp_partes_identidad_resolver',
            'pq_sp_partes_assert_user_id_exclusividad',
            'pq_sp_partes_tipos_tarea_marcar_default',
        ], true);
    }

    /**
     * EXEC/CALL sin result set (INSERT/UPDATE SP). sqlsrv: DB::statement.
     *
     * @param  array<string, mixed>  $params
     */
    public function execute(string $procedure, array $params = []): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->callSqliteFallback($procedure, $params);

            return;
        }

        if ($driver === 'sqlsrv') {
            [$sql, $bindings] = $this->buildSqlsrvExec($procedure, $params);
            DB::statement($sql, $bindings);

            return;
        }

        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        DB::statement('CALL '.$procedure.'('.$placeholders.')', array_values($params));
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    public function callFirst(string $procedure, array $params = []): ?object
    {
        $rows = $this->call($procedure, $params);

        return $rows[0] ?? null;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{0: string, 1: list<mixed>}
     */
    private function buildSqlsrvExec(string $procedure, array $params): array
    {
        $parts = [];
        $bindings = [];

        foreach ($params as $name => $value) {
            $parts[] = '@'.$name.' = ?';
            $bindings[] = $value;
        }

        $argList = $parts === [] ? '' : ' '.implode(', ', $parts);

        return ['EXEC dbo.'.$procedure.$argList, $bindings];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function callSqlsrv(string $procedure, array $params): array
    {
        [$sql, $bindings] = $this->buildSqlsrvExec($procedure, $params);

        try {
            /** @var list<object> */
            return DB::select($sql, $bindings);
        } catch (\PDOException $e) {
            // SP sin result set (p.ej. INSERT) → IMSSP "no fields".
            if (str_contains($e->getMessage(), 'no fields')) {
                return [];
            }

            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'no fields')) {
                return [];
            }

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function callMysql(string $procedure, array $params): array
    {
        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        $sql = 'CALL '.$procedure.'('.$placeholders.')';

        /** @var list<object> */
        return DB::select($sql, array_values($params));
    }

    /**
     * Fallback solo tests locales (sin SP desplegados).
     *
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function callSqliteFallback(string $procedure, array $params): array
    {
        return match ($procedure) {
            'pq_sp_user_menu' => DB::select(
                'SELECT m.id, m.parent_id AS parentId, m.codigo AS menuKey, m.titulo AS text,
                        m.ruta AS routeName, m.orden AS "order", m.procedimiento,
                        m.process_type AS processType, m.icon_name AS iconName
                 FROM pq_menus m
                 WHERE m.activo = 1 AND m.enabled = 1
                 ORDER BY m.orden, m.id'
            ),
            'pq_sp_user_empresas_list' => DB::select(
                'SELECT DISTINCT e.id, e.nombre AS nombreEmpresa, e.theme AS theme
                 FROM pq_empresa e
                 INNER JOIN pq_permisos p ON p.empresa_id = e.id
                 WHERE p.user_id = ? AND e.activo = 1
                 ORDER BY e.nombre',
                [(int) $params['user_id']]
            ),
            'pq_sp_user_empresa_allowed' => DB::select(
                'SELECT CASE WHEN EXISTS (
                    SELECT 1 FROM pq_permisos p
                    INNER JOIN pq_empresa e ON e.id = p.empresa_id
                    WHERE p.user_id = ? AND p.empresa_id = ? AND e.activo = 1
                ) THEN 1 ELSE 0 END AS allowed',
                [(int) $params['user_id'], (int) $params['empresa_id']]
            ),
            'pq_sp_user_acceso_total' => DB::select(
                'SELECT 1 AS accesoTotal FROM pq_permisos p
                 INNER JOIN pq_roles r ON r.id = p.rol_id
                 WHERE p.user_id = ? AND r.acceso_total = 1 AND r.activo = 1
                 AND (? IS NULL OR p.empresa_id = ?)
                 LIMIT 1',
                [(int) $params['UserId'], $params['EmpresaId'] ?? null, $params['EmpresaId'] ?? null]
            ),
            'pq_sp_user_preferences_get' => DB::select(
                'SELECT locale, open_in_new_tab, active_llm_credential_id FROM users WHERE id = ?',
                [(int) $params['user_id']]
            ),
            'pq_sp_user_preferences_set' => $this->sqlitePreferencesSet($params),
            'pq_sp_parametros_list' => DB::select(
                'SELECT * FROM pq_parametros_gral WHERE programa = ? ORDER BY caption, clave',
                [(string) $params['programa']]
            ),
            'pq_sp_parametros_get' => DB::select(
                'SELECT * FROM pq_parametros_gral WHERE programa = ? AND clave = ?',
                [(string) $params['programa'], (string) $params['clave']]
            ),
            'pq_sp_parametros_update' => $this->sqliteParametrosUpdate($params),
            'pq_sp_parametros_insert_if_absent' => $this->sqliteParametrosInsertIfAbsent($params),
            'pq_sp_partes_identidad_resolver' => $this->sqlitePartesIdentidadResolver($params),
            'pq_sp_admin_usuarios_list' => DB::select(
                'SELECT id, usuario, name AS nombre, email, activo, inhabilitado FROM users ORDER BY usuario'
            ),
            'pq_sp_admin_usuarios_get' => DB::select(
                'SELECT id, usuario, name AS nombre, email, activo, inhabilitado FROM users WHERE id = ?',
                [(int) $params['id']]
            ),
            'pq_sp_admin_usuarios_create' => $this->sqliteAdminUsuariosCreate($params),
            'pq_sp_admin_usuarios_update' => $this->sqliteAdminUsuariosUpdate($params),
            'pq_sp_admin_usuarios_soft_delete' => $this->sqliteAdminUsuariosSoftDelete($params),
            'pq_sp_admin_empresas_list' => DB::select(
                'SELECT id, nombre, activo, theme FROM pq_empresa ORDER BY nombre'
            ),
            'pq_sp_admin_empresas_get' => DB::select(
                'SELECT id, nombre, activo, theme FROM pq_empresa WHERE id = ?',
                [(int) $params['id']]
            ),
            'pq_sp_admin_empresas_update' => $this->sqliteAdminEmpresasUpdate($params),
            'pq_sp_admin_roles_list' => DB::select(
                'SELECT id, codigo, nombre, acceso_total AS accesoTotal, activo FROM pq_roles ORDER BY codigo'
            ),
            'pq_sp_admin_roles_get' => DB::select(
                'SELECT id, codigo, nombre, acceso_total AS accesoTotal, activo FROM pq_roles WHERE id = ?',
                [(int) $params['id']]
            ),
            'pq_sp_admin_roles_create' => $this->sqliteAdminRolesCreate($params),
            'pq_sp_admin_roles_update' => $this->sqliteAdminRolesUpdate($params),
            'pq_sp_admin_roles_delete' => $this->sqliteAdminRolesDelete($params),
            'pq_sp_user_menu_permisos_efectivos' => $this->sqliteUserMenuPermisosEfectivos($params),
            'pq_sp_admin_menus_arbol_enabled' => DB::select(
                "SELECT m.id AS menuId, m.parent_id AS padreId, m.titulo,
                        CASE WHEN m.process_type = 'A' THEN 1 ELSE 0 END AS esProceso
                 FROM pq_menus m
                 WHERE m.enabled = 1 AND m.activo = 1
                 ORDER BY m.orden, m.id"
            ),
            'pq_sp_admin_roles_atributos_get' => DB::select(
                'SELECT menu_id AS menuId, permiso_alta AS permisoAlta, permiso_baja AS permisoBaja,
                        permiso_modi AS permisoModi, permiso_repo AS permisoRepo
                 FROM pq_rol_atributos
                 WHERE rol_id = ?
                 ORDER BY menu_id',
                [(int) $params['rol_id']]
            ),
            'pq_sp_admin_roles_atributos_delete_all' => $this->sqliteRolAtributosDeleteAll($params),
            'pq_sp_admin_roles_atributos_insert' => $this->sqliteRolAtributosInsert($params),
            'pq_sp_admin_permisos_list' => DB::select(
                'SELECT p.id, p.user_id AS userId, u.usuario AS usuario, u.name AS usuarioNombre,
                        p.empresa_id AS empresaId, e.nombre AS empresaNombre,
                        p.rol_id AS rolId, r.codigo AS rolCodigo, r.nombre AS rolNombre
                 FROM pq_permisos p
                 INNER JOIN users u ON u.id = p.user_id
                 INNER JOIN pq_empresa e ON e.id = p.empresa_id
                 INNER JOIN pq_roles r ON r.id = p.rol_id
                 ORDER BY u.usuario, e.nombre, r.codigo, p.id'
            ),
            'pq_sp_admin_permisos_list_by_user' => DB::select(
                'SELECT p.id, p.user_id AS userId, u.usuario AS usuario, u.name AS usuarioNombre,
                        p.empresa_id AS empresaId, e.nombre AS empresaNombre,
                        p.rol_id AS rolId, r.codigo AS rolCodigo, r.nombre AS rolNombre
                 FROM pq_permisos p
                 INNER JOIN users u ON u.id = p.user_id
                 INNER JOIN pq_empresa e ON e.id = p.empresa_id
                 INNER JOIN pq_roles r ON r.id = p.rol_id
                 WHERE p.user_id = ?
                 ORDER BY p.id',
                [(int) $params['user_id']]
            ),
            'pq_sp_admin_permisos_create' => $this->sqliteAdminPermisosCreate($params),
            'pq_sp_admin_permisos_create_if_absent' => $this->sqliteAdminPermisosCreateIfAbsent($params),
            'pq_sp_admin_permisos_delete' => $this->sqliteAdminPermisosDelete($params),
            'pq_sp_llm_credentials_list' => $this->sqliteLlmCredentialsList($params),
            'pq_sp_llm_credentials_get' => $this->sqliteLlmCredentialsGet($params),
            'pq_sp_llm_credentials_insert' => $this->sqliteLlmCredentialsInsert($params),
            'pq_sp_llm_credentials_update' => $this->sqliteLlmCredentialsUpdate($params),
            'pq_sp_llm_credentials_delete' => $this->sqliteLlmCredentialsDelete($params),
            'pq_sp_llm_active_preference_get' => $this->sqliteLlmActivePreferenceGet($params),
            'pq_sp_llm_active_preference_set' => $this->sqliteLlmActivePreferenceSet($params),
            default => throw new \RuntimeException("SP {$procedure} no disponible en sqlite (tests)."),
        };
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteLlmCredentialsList(array $params): array
    {
        return DB::select(
            'SELECT id, user_id, nombre, proveedor, modelo, secreto_cifrado, base_url,
                    supports_vision, enabled, created_at, updated_at
             FROM pq_llm_credentials
             WHERE user_id = ?
             ORDER BY nombre ASC, id ASC',
            [(int) $params['user_id']]
        );
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteLlmCredentialsGet(array $params): array
    {
        return DB::select(
            'SELECT id, user_id, nombre, proveedor, modelo, secreto_cifrado, base_url,
                    supports_vision, enabled, created_at, updated_at
             FROM pq_llm_credentials
             WHERE id = ? AND user_id = ?',
            [(int) $params['credential_id'], (int) $params['user_id']]
        );
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteLlmCredentialsInsert(array $params): array
    {
        $now = now();
        $id = DB::table('pq_llm_credentials')->insertGetId([
            'user_id' => (int) $params['user_id'],
            'nombre' => (string) $params['nombre'],
            'proveedor' => (string) $params['proveedor'],
            'modelo' => (string) $params['modelo'],
            'secreto_cifrado' => (string) $params['secreto_cifrado'],
            'base_url' => $params['base_url'] ?? null,
            'supports_vision' => (bool) ($params['supports_vision'] ?? false),
            'enabled' => (bool) ($params['enabled'] ?? true),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->sqliteLlmCredentialsGet([
            'credential_id' => $id,
            'user_id' => (int) $params['user_id'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteLlmCredentialsUpdate(array $params): array
    {
        $credentialId = (int) $params['credential_id'];
        $userId = (int) $params['user_id'];
        $updated = DB::table('pq_llm_credentials')
            ->where('id', $credentialId)
            ->where('user_id', $userId)
            ->update([
                'nombre' => (string) $params['nombre'],
                'proveedor' => (string) $params['proveedor'],
                'modelo' => (string) $params['modelo'],
                'secreto_cifrado' => (string) $params['secreto_cifrado'],
                'base_url' => $params['base_url'] ?? null,
                'supports_vision' => (bool) $params['supports_vision'],
                'enabled' => (bool) $params['enabled'],
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            return [];
        }

        return $this->sqliteLlmCredentialsGet([
            'credential_id' => $credentialId,
            'user_id' => $userId,
        ]);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteLlmCredentialsDelete(array $params): array
    {
        $credentialId = (int) $params['credential_id'];
        $userId = (int) $params['user_id'];

        DB::table('users')
            ->where('id', $userId)
            ->where('active_llm_credential_id', $credentialId)
            ->update(['active_llm_credential_id' => null]);

        $deleted = DB::table('pq_llm_credentials')
            ->where('id', $credentialId)
            ->where('user_id', $userId)
            ->delete();

        return [(object) ['deleted_count' => $deleted]];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteLlmActivePreferenceGet(array $params): array
    {
        return DB::select(
            'SELECT active_llm_credential_id FROM users WHERE id = ?',
            [(int) $params['user_id']]
        );
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteLlmActivePreferenceSet(array $params): array
    {
        $userId = (int) $params['user_id'];
        $credentialId = $params['credential_id'] ?? null;
        $normalizedId = null;

        if ($credentialId !== null && $credentialId !== '') {
            $row = DB::table('pq_llm_credentials')
                ->where('id', (int) $credentialId)
                ->where('user_id', $userId)
                ->where('enabled', 1)
                ->first();
            if ($row !== null) {
                $normalizedId = (int) $row->id;
            }
        }

        DB::table('users')->where('id', $userId)->update([
            'active_llm_credential_id' => $normalizedId,
        ]);

        return [(object) ['active_llm_credential_id' => $normalizedId]];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteAdminUsuariosCreate(array $params): array
    {
        $id = DB::table('users')->insertGetId([
            'name' => (string) $params['nombre'],
            'usuario' => (string) $params['usuario'],
            'email' => (string) $params['email'],
            'password' => (string) $params['password_hash'],
            'first_login' => true,
            'supervisor' => false,
            'activo' => (bool) ($params['activo'] ?? true),
            'inhabilitado' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::select(
            'SELECT id, usuario, name AS nombre, email, activo, inhabilitado FROM users WHERE id = ?',
            [$id]
        );
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteAdminUsuariosUpdate(array $params): array
    {
        $id = (int) $params['id'];
        $patch = ['updated_at' => now()];

        if (array_key_exists('usuario', $params) && $params['usuario'] !== null) {
            $patch['usuario'] = (string) $params['usuario'];
        }
        if (array_key_exists('nombre', $params) && $params['nombre'] !== null) {
            $patch['name'] = (string) $params['nombre'];
        }
        if (array_key_exists('email', $params) && $params['email'] !== null) {
            $patch['email'] = (string) $params['email'];
        }
        if (array_key_exists('activo', $params) && $params['activo'] !== null) {
            $patch['activo'] = (bool) $params['activo'];
        }
        if (array_key_exists('inhabilitado', $params) && $params['inhabilitado'] !== null) {
            $patch['inhabilitado'] = (bool) $params['inhabilitado'];
        }

        DB::table('users')->where('id', $id)->update($patch);

        return DB::select(
            'SELECT id, usuario, name AS nombre, email, activo, inhabilitado FROM users WHERE id = ?',
            [$id]
        );
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteAdminUsuariosSoftDelete(array $params): array
    {
        $updated = DB::table('users')->where('id', (int) $params['id'])->update([
            'inhabilitado' => true,
            'activo' => false,
            'updated_at' => now(),
        ]);

        return [(object) ['updated_rows' => $updated]];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteAdminEmpresasUpdate(array $params): array
    {
        $id = (int) $params['id'];
        $patch = ['updated_at' => now()];

        if (array_key_exists('nombre', $params) && $params['nombre'] !== null) {
            $patch['nombre'] = (string) $params['nombre'];
        }
        if (array_key_exists('activo', $params) && $params['activo'] !== null) {
            $patch['activo'] = (bool) $params['activo'];
        }
        if (array_key_exists('theme', $params) && $params['theme'] !== null) {
            $patch['theme'] = (string) $params['theme'];
        }

        DB::table('pq_empresa')->where('id', $id)->update($patch);

        return DB::select('SELECT id, nombre, activo, theme FROM pq_empresa WHERE id = ?', [$id]);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteRolAtributosDeleteAll(array $params): array
    {
        $deleted = DB::table('pq_rol_atributos')->where('rol_id', (int) $params['rol_id'])->delete();

        return [(object) ['updated_rows' => $deleted]];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteRolAtributosInsert(array $params): array
    {
        DB::table('pq_rol_atributos')->insert([
            'rol_id' => (int) $params['rol_id'],
            'menu_id' => (int) $params['menu_id'],
            'permiso_alta' => (bool) ($params['permiso_alta'] ?? false),
            'permiso_baja' => (bool) ($params['permiso_baja'] ?? false),
            'permiso_modi' => (bool) ($params['permiso_modi'] ?? false),
            'permiso_repo' => (bool) ($params['permiso_repo'] ?? false),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteAdminRolesCreate(array $params): array
    {
        $id = DB::table('pq_roles')->insertGetId([
            'codigo' => (string) $params['codigo'],
            'nombre' => (string) $params['nombre'],
            'acceso_total' => (bool) ($params['acceso_total'] ?? false),
            'activo' => (bool) ($params['activo'] ?? true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::select(
            'SELECT id, codigo, nombre, acceso_total AS accesoTotal, activo FROM pq_roles WHERE id = ?',
            [$id]
        );
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteAdminRolesUpdate(array $params): array
    {
        $id = (int) $params['id'];
        $patch = ['updated_at' => now()];

        if (array_key_exists('codigo', $params) && $params['codigo'] !== null) {
            $patch['codigo'] = (string) $params['codigo'];
        }
        if (array_key_exists('nombre', $params) && $params['nombre'] !== null) {
            $patch['nombre'] = (string) $params['nombre'];
        }
        if (array_key_exists('acceso_total', $params) && $params['acceso_total'] !== null) {
            $patch['acceso_total'] = (bool) $params['acceso_total'];
        }
        if (array_key_exists('activo', $params) && $params['activo'] !== null) {
            $patch['activo'] = (bool) $params['activo'];
        }

        DB::table('pq_roles')->where('id', $id)->update($patch);

        return DB::select(
            'SELECT id, codigo, nombre, acceso_total AS accesoTotal, activo FROM pq_roles WHERE id = ?',
            [$id]
        );
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteAdminRolesDelete(array $params): array
    {
        $id = (int) $params['id'];

        $exists = DB::table('pq_roles')->where('id', $id)->exists();
        if (! $exists) {
            return [(object) ['deleted' => 0, 'outcome' => 'not_found']];
        }

        $hasPermisos = DB::table('pq_permisos')->where('rol_id', $id)->exists();
        if ($hasPermisos) {
            return [(object) ['deleted' => 0, 'outcome' => 'has_permisos']];
        }

        DB::table('pq_rol_atributos')->where('rol_id', $id)->delete();
        DB::table('pq_roles')->where('id', $id)->delete();

        return [(object) ['deleted' => 1, 'outcome' => 'ok']];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteUserMenuPermisosEfectivos(array $params): array
    {
        return DB::select(
            'SELECT a.menu_id AS menuId,
                    MAX(CASE WHEN a.permiso_alta THEN 1 ELSE 0 END) AS permisoAlta,
                    MAX(CASE WHEN a.permiso_baja THEN 1 ELSE 0 END) AS permisoBaja,
                    MAX(CASE WHEN a.permiso_modi THEN 1 ELSE 0 END) AS permisoModi,
                    MAX(CASE WHEN a.permiso_repo THEN 1 ELSE 0 END) AS permisoRepo
             FROM pq_permisos p
             INNER JOIN pq_rol_atributos a ON a.rol_id = p.rol_id
             WHERE p.user_id = ? AND p.empresa_id = ?
             GROUP BY a.menu_id',
            [(int) $params['user_id'], (int) $params['empresa_id']]
        );
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteAdminPermisosCreate(array $params): array
    {
        $id = DB::table('pq_permisos')->insertGetId([
            'user_id' => (int) $params['user_id'],
            'empresa_id' => (int) $params['empresa_id'],
            'rol_id' => (int) $params['rol_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::select(
            'SELECT p.id, p.user_id AS userId, u.usuario AS usuario, u.name AS usuarioNombre,
                    p.empresa_id AS empresaId, e.nombre AS empresaNombre,
                    p.rol_id AS rolId, r.codigo AS rolCodigo, r.nombre AS rolNombre
             FROM pq_permisos p
             INNER JOIN users u ON u.id = p.user_id
             INNER JOIN pq_empresa e ON e.id = p.empresa_id
             INNER JOIN pq_roles r ON r.id = p.rol_id
             WHERE p.id = ?',
            [$id]
        );
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteAdminPermisosCreateIfAbsent(array $params): array
    {
        $exists = DB::table('pq_permisos')
            ->where('user_id', (int) $params['user_id'])
            ->where('empresa_id', (int) $params['empresa_id'])
            ->where('rol_id', (int) $params['rol_id'])
            ->exists();

        if ($exists) {
            return [(object) ['created' => 0]];
        }

        DB::table('pq_permisos')->insert([
            'user_id' => (int) $params['user_id'],
            'empresa_id' => (int) $params['empresa_id'],
            'rol_id' => (int) $params['rol_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [(object) ['created' => 1]];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteAdminPermisosDelete(array $params): array
    {
        $deleted = DB::table('pq_permisos')->where('id', (int) $params['id'])->delete();

        return [(object) ['updated_rows' => $deleted]];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqlitePartesIdentidadResolver(array $params): array
    {
        $userId = (int) $params['p_user_id'];

        $asistente = DB::table('PQ_PARTES_USUARIOS')
            ->where('user_id', $userId)
            ->where('activo', 1)
            ->where('inhabilitado', 0)
            ->first();

        $cliente = DB::table('PQ_PARTES_CLIENTES')
            ->where('user_id', $userId)
            ->where('activo', 1)
            ->where('inhabilitado', 0)
            ->first();

        if ($asistente !== null && $cliente !== null) {
            return [(object) [
                'codigo_resultado' => 2,
                'tipo_funcional' => null,
                'asistente_id' => null,
                'cliente_id' => null,
                'es_supervisor' => 0,
                'code' => null,
                'nombre' => null,
                'email' => null,
            ]];
        }

        if ($asistente !== null) {
            return [(object) [
                'codigo_resultado' => 0,
                'tipo_funcional' => 'asistente',
                'asistente_id' => (int) $asistente->id,
                'cliente_id' => null,
                'es_supervisor' => (int) $asistente->supervisor,
                'code' => $asistente->code,
                'nombre' => $asistente->nombre,
                'email' => $asistente->email,
            ]];
        }

        if ($cliente !== null) {
            return [(object) [
                'codigo_resultado' => 0,
                'tipo_funcional' => 'cliente',
                'asistente_id' => null,
                'cliente_id' => (int) $cliente->id,
                'es_supervisor' => 0,
                'code' => $cliente->code,
                'nombre' => $cliente->nombre,
                'email' => $cliente->email,
            ]];
        }

        return [(object) [
            'codigo_resultado' => 1,
            'tipo_funcional' => null,
            'asistente_id' => null,
            'cliente_id' => null,
            'es_supervisor' => 0,
            'code' => null,
            'nombre' => null,
            'email' => null,
        ]];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqlitePreferencesSet(array $params): array
    {
        $userId = (int) $params['user_id'];
        $updates = [];

        if (array_key_exists('locale', $params) && $params['locale'] !== null) {
            $updates['locale'] = $params['locale'];
        }

        if (array_key_exists('open_in_new_tab', $params) && $params['open_in_new_tab'] !== null) {
            $updates['open_in_new_tab'] = (int) $params['open_in_new_tab'];
        }

        if ($updates !== []) {
            DB::table('users')->where('id', $userId)->update($updates);
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteParametrosUpdate(array $params): array
    {
        $programa = (string) $params['programa'];
        $clave = (string) $params['clave'];
        $row = DB::table('pq_parametros_gral')->where(compact('programa', 'clave'))->first();

        if ($row === null) {
            return [(object) ['updated_rows' => 0]];
        }

        $patch = ['updated_at' => now()];
        foreach (['valor_string', 'valor_texto', 'valor_int', 'valor_decimal', 'valor_bool', 'valor_fecha'] as $column) {
            if (array_key_exists($column, $params) && $params[$column] !== null) {
                $patch[$column] = $params[$column];
            }
        }

        $updated = DB::table('pq_parametros_gral')->where(compact('programa', 'clave'))->update($patch);

        return [(object) ['updated_rows' => $updated]];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private function sqliteParametrosInsertIfAbsent(array $params): array
    {
        $programa = (string) $params['programa'];
        $clave = (string) $params['clave'];
        $exists = DB::table('pq_parametros_gral')->where(compact('programa', 'clave'))->exists();

        if ($exists) {
            DB::table('pq_parametros_gral')->where(compact('programa', 'clave'))->update([
                'caption' => $params['caption'] ?? DB::raw('caption'),
                'tooltip' => $params['tooltip'] ?? DB::raw('tooltip'),
                'meta_json' => $params['meta_json'] ?? DB::raw('meta_json'),
                'updated_at' => now(),
            ]);

            return [];
        }

        DB::table('pq_parametros_gral')->insert([
            'programa' => $programa,
            'clave' => $clave,
            'tipo_valor' => $params['tipo_valor'],
            'valor_string' => $params['valor_string'] ?? null,
            'valor_texto' => $params['valor_texto'] ?? null,
            'valor_int' => $params['valor_int'] ?? null,
            'valor_decimal' => $params['valor_decimal'] ?? null,
            'valor_bool' => $params['valor_bool'] ?? null,
            'valor_fecha' => $params['valor_fecha'] ?? null,
            'precision_fecha' => $params['precision_fecha'] ?? null,
            'caption' => $params['caption'] ?? $clave,
            'tooltip' => $params['tooltip'] ?? null,
            'meta_json' => $params['meta_json'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [];
    }
}
