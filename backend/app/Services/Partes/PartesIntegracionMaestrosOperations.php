<?php

namespace App\Services\Partes;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Fallback sqlite para los contratos SP de integración de maestros.
 * SQL Server ejecuta los procedimientos equivalentes desde SpCaller.
 */
final class PartesIntegracionMaestrosOperations
{
    private const MAX_ERRORS = 50;

    /**
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    public static function dispatch(string $procedure, array $params): array
    {
        $resource = match ($procedure) {
            'pq_sp_partes_integracion_maestros_clientes' => 'clientes',
            'pq_sp_partes_integracion_maestros_asistentes' => 'asistentes',
            'pq_sp_partes_integracion_maestros_tipos_cliente' => 'tipos-cliente',
            'pq_sp_partes_integracion_maestros_tipos_tarea' => 'tipos-tarea',
            'pq_sp_partes_integracion_maestros_asignaciones' => 'asignaciones-cliente-tipo',
            default => throw new RuntimeException("SP {$procedure} sin Operations"),
        };

        $items = json_decode((string) ($params['itemsJson'] ?? '[]'), true);
        if (! is_array($items)) {
            self::fail('integracion.maestro.jsonInvalido');
        }

        return match ($resource) {
            'clientes' => self::clientes($items, $params),
            'asistentes' => self::asistentes($items, $params),
            'tipos-cliente' => self::tiposCliente($items, $params),
            'tipos-tarea' => self::tiposTarea($items, $params),
            'asignaciones-cliente-tipo' => self::asignaciones($items, $params),
        };
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private static function clientes(array $items, array $params): array
    {
        $seen = [];
        $results = [];
        $errors = [];
        $dryRun = self::dryRun($params);

        foreach ($items as $index => $item) {
            $item = self::normalizeItem($item);
            $code = self::stringValue($item['code'] ?? null);
            if ($code === '' || isset($seen[$code])) {
                self::reject($results, $errors, $index, $code, isset($seen[$code]) ? 'integracion.maestro.duplicado' : 'integracion.maestro.itemInvalido');
                $seen[$code] = true;
                continue;
            }
            $seen[$code] = true;

            try {
                $typeCode = self::nestedCode($item['tipoCliente'] ?? null);
                if ($self = DB::table('PQ_PARTES_TIPOS_CLIENTE')->where('code', $typeCode)->where('activo', 1)->where('inhabilitado', 0)->first()) {
                    $existing = DB::table('PQ_PARTES_CLIENTES')->where('code', $code)->first();
                    $patch = [
                        'nombre' => (string) ($item['nombre'] ?? ''),
                        'tipo_cliente_id' => $self->id,
                    ];
                    self::optionalPatch($patch, $item, 'email', 'email');
                    self::optionalPatch($patch, $item, 'erpCliente', 'erp_cliente');
                    self::optionalPatch($patch, $item, 'erpArticulo', 'erp_articulo');
                    self::validateClient($item, $typeCode, $patch);
                    $action = self::upsertRow('PQ_PARTES_CLIENTES', $existing, $code, $patch, $dryRun);
                    self::appendResult($results, $index, $code, $action);
                } else {
                    self::reject($results, $errors, $index, $code, 'integracion.maestro.referenciaInvalida');
                }
            } catch (PartesIntegracionMaestrosException $exception) {
                self::reject($results, $errors, $index, $code, $exception->respuesta);
            }
        }

        self::disableAbsent('PQ_PARTES_CLIENTES', 'code', $seen, $results, $errors, $dryRun, 'clientes');

        return self::result('clientes', $items, $results, $errors, $dryRun);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private static function asistentes(array $items, array $params): array
    {
        $seen = [];
        $results = [];
        $errors = [];
        $dryRun = self::dryRun($params);

        foreach ($items as $index => $item) {
            $item = self::normalizeItem($item);
            $code = self::stringValue($item['code'] ?? null);
            if ($code === '' || isset($seen[$code])) {
                self::reject($results, $errors, $index, $code, isset($seen[$code]) ? 'integracion.maestro.duplicado' : 'integracion.maestro.itemInvalido');
                $seen[$code] = true;
                continue;
            }
            $seen[$code] = true;

            try {
                $userCode = self::nestedCode($item['usuario'] ?? null);
                if (self::stringValue($item['nombre'] ?? null) === '' || $userCode === '') {
                    self::fail('integracion.maestro.itemInvalido');
                }
                $user = DB::table('users')->where('usuario', $userCode)->where('activo', 1)->where('inhabilitado', 0)->first();
                $existing = DB::table('PQ_PARTES_USUARIOS')->where('code', $code)->first();
                if ($user === null || ($existing !== null && (int) $existing->user_id !== (int) $user->id)) {
                    self::fail('integracion.maestro.usuarioNoVinculable');
                }
                if ($existing === null && (
                    DB::table('PQ_PARTES_USUARIOS')->where('user_id', $user->id)->exists()
                    || DB::table('PQ_PARTES_CLIENTES')->where('user_id', $user->id)->exists()
                )) {
                    self::fail('integracion.maestro.usuarioNoVinculable');
                }

                $patch = [
                    'user_id' => $user->id,
                    'nombre' => (string) ($item['nombre'] ?? ''),
                ];
                self::optionalPatch($patch, $item, 'email', 'email');
                if (array_key_exists('supervisor', $item)) {
                    $patch['supervisor'] = (bool) $item['supervisor'];
                } elseif ($existing === null) {
                    $patch['supervisor'] = false;
                }
                $action = self::upsertRow('PQ_PARTES_USUARIOS', $existing, $code, $patch, $dryRun);
                self::appendResult($results, $index, $code, $action);
            } catch (PartesIntegracionMaestrosException $exception) {
                self::reject($results, $errors, $index, $code, $exception->respuesta);
            }
        }

        self::disableAbsent('PQ_PARTES_USUARIOS', 'code', $seen, $results, $errors, $dryRun, 'asistentes');

        return self::result('asistentes', $items, $results, $errors, $dryRun);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private static function tiposCliente(array $items, array $params): array
    {
        return self::simpleCatalog(
            $items,
            $params,
            'tipos-cliente',
            'PQ_PARTES_TIPOS_CLIENTE',
            fn (array $item): array => ['descripcion' => (string) ($item['descripcion'] ?? '')],
        );
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private static function tiposTarea(array $items, array $params): array
    {
        $defaultIndex = null;
        foreach ($items as $index => $item) {
            if (($item['isDefault'] ?? false) === true) {
                $defaultIndex = $index;
            }
        }

        $result = self::simpleCatalog(
            $items,
            $params,
            'tipos-tarea',
            'PQ_PARTES_TIPOS_TAREA',
            function (array $item) use (&$defaultIndex): array {
                $isDefault = array_key_exists('isDefault', $item) ? (bool) $item['isDefault'] : null;
                $isGenerico = array_key_exists('isGenerico', $item) ? (bool) $item['isGenerico'] : null;
                if ($isDefault === true) {
                    $isGenerico = true;
                }

                return array_filter([
                    'descripcion' => (string) ($item['descripcion'] ?? ''),
                    'is_generico' => $isGenerico,
                    'is_default' => $isDefault,
                ], static fn (mixed $value): bool => $value !== null);
            },
            $defaultIndex,
        );

        return $result;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, mixed>  $params
     * @return list<object>
     */
    private static function asignaciones(array $items, array $params): array
    {
        $seenClients = [];
        $requested = [];
        $results = [];
        $errors = [];
        $dryRun = self::dryRun($params);

        foreach ($items as $index => $item) {
            $item = self::normalizeItem($item);
            $clientCode = self::stringValue($item['code'] ?? null);
            if ($clientCode === '' || isset($seenClients[$clientCode]) || ! array_key_exists('tiposTarea', $item) || ! is_array($item['tiposTarea'])) {
                self::reject($results, $errors, $index, $clientCode, isset($seenClients[$clientCode]) ? 'integracion.maestro.duplicado' : 'integracion.maestro.itemInvalido');
                $seenClients[$clientCode] = true;
                continue;
            }
            $seenClients[$clientCode] = true;
            $client = DB::table('PQ_PARTES_CLIENTES')->where('code', $clientCode)->where('activo', 1)->where('inhabilitado', 0)->first();
            if ($client === null) {
                self::reject($results, $errors, $index, $clientCode, 'integracion.maestro.referenciaInvalida');
                continue;
            }

            $requested[$client->id] = [];
            foreach ($item['tiposTarea'] as $typeItem) {
                $typeCode = self::nestedCode($typeItem);
                $type = DB::table('PQ_PARTES_TIPOS_TAREA')->where('code', $typeCode)->where('activo', 1)->where('inhabilitado', 0)->first();
                if ($type === null || (bool) $type->is_generico) {
                    $errors[] = self::error($index, $clientCode, 'integracion.maestro.referenciaInvalida');
                    continue;
                }
                $requested[$client->id][(int) $type->id] = true;
            }
            if (count($errors) > self::MAX_ERRORS) {
                break;
            }
        }

        $existing = DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')->get();
        foreach ($existing as $row) {
            $wanted = isset($requested[(int) $row->cliente_id][(int) $row->tipo_tarea_id]);
            if ($wanted) {
                self::appendResult($results, null, self::assignmentKey($row), 'sinCambios');
                continue;
            }
            $clientCode = (string) DB::table('PQ_PARTES_CLIENTES')->where('id', $row->cliente_id)->value('code');
            $typeCode = (string) DB::table('PQ_PARTES_TIPOS_TAREA')->where('id', $row->tipo_tarea_id)->value('code');
            if (! $dryRun && self::hasStatusColumns('PQ_PARTES_CLIENTE_TIPO_TAREA')) {
                DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')->where('id', $row->id)->update([
                    'activo' => false,
                    'inhabilitado' => true,
                    'updated_at' => now(),
                ]);
            }
            self::appendResult($results, null, $clientCode.'|'.$typeCode, 'inhabilitado');
        }

        foreach ($requested as $clientId => $types) {
            foreach (array_keys($types) as $typeId) {
                $current = DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')
                    ->where('cliente_id', $clientId)
                    ->where('tipo_tarea_id', $typeId)
                    ->first();
                $clientCode = (string) DB::table('PQ_PARTES_CLIENTES')->where('id', $clientId)->value('code');
                $typeCode = (string) DB::table('PQ_PARTES_TIPOS_TAREA')->where('id', $typeId)->value('code');
                if ($current === null) {
                    if (! $dryRun) {
                        DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')->insert([
                            'cliente_id' => $clientId,
                            'tipo_tarea_id' => $typeId,
                            'activo' => true,
                            'inhabilitado' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    self::appendResult($results, null, $clientCode.'|'.$typeCode, 'alta');
                } elseif (self::isInactive($current)) {
                    if (! $dryRun) {
                        DB::table('PQ_PARTES_CLIENTE_TIPO_TAREA')->where('id', $current->id)->update([
                            'activo' => true,
                            'inhabilitado' => false,
                            'updated_at' => now(),
                        ]);
                    }
                    self::appendResult($results, null, $clientCode.'|'.$typeCode, 'modificacion');
                }
            }
        }

        return self::result('asignaciones-cliente-tipo', $items, $results, $errors, $dryRun);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, mixed>  $params
     * @param  callable(array<string, mixed>): array<string, mixed>  $fields
     * @return list<object>
     */
    private static function simpleCatalog(
        array $items,
        array $params,
        string $resource,
        string $table,
        callable $fields,
        ?int $defaultIndex = null,
    ): array {
        $seen = [];
        $results = [];
        $errors = [];
        $dryRun = self::dryRun($params);

        foreach ($items as $index => $item) {
            $item = self::normalizeItem($item);
            $code = self::stringValue($item['code'] ?? null);
            if ($code === '' || isset($seen[$code])) {
                self::reject($results, $errors, $index, $code, isset($seen[$code]) ? 'integracion.maestro.duplicado' : 'integracion.maestro.itemInvalido');
                $seen[$code] = true;
                continue;
            }
            $seen[$code] = true;
            try {
                $payload = $fields($item);
                if (($payload['descripcion'] ?? '') === '') {
                    self::fail('integracion.maestro.itemInvalido');
                }
                $existing = DB::table($table)->where('code', $code)->first();
                if ($resource === 'tipos-tarea') {
                    if (! array_key_exists('is_generico', $payload) && $existing === null) {
                        $payload['is_generico'] = true;
                    }
                    if (! array_key_exists('is_default', $payload) && $existing === null) {
                        $payload['is_default'] = false;
                    }
                    if ($defaultIndex !== null && $index === $defaultIndex) {
                        $payload['is_default'] = true;
                        $payload['is_generico'] = true;
                    }
                    if (($payload['is_default'] ?? false) === true) {
                        $payload['is_generico'] = true;
                    }
                }
                $action = self::upsertRow($table, $existing, $code, $payload, $dryRun);
                if ($resource === 'tipos-tarea' && ($payload['is_default'] ?? false) === true && ! $dryRun) {
                    DB::table($table)->where('is_default', true)->where('code', '<>', $code)->update([
                        'is_default' => false,
                        'updated_at' => now(),
                    ]);
                }
                self::appendResult($results, $index, $code, $action);
            } catch (PartesIntegracionMaestrosException $exception) {
                self::reject($results, $errors, $index, $code, $exception->respuesta);
            }
        }

        self::disableAbsent($table, 'code', $seen, $results, $errors, $dryRun, $resource);

        return self::result($resource, $items, $results, $errors, $dryRun);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private static function normalizeItem(array $item): array
    {
        foreach ($item as $key => $value) {
            if (is_string($value)) {
                $item[$key] = trim($value);
            }
        }

        return $item;
    }

    private static function stringValue(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private static function nestedCode(mixed $value): string
    {
        return is_array($value) ? self::stringValue($value['code'] ?? null) : '';
    }

    /**
     * @param  array<string, mixed>  $patch
     * @param  array<string, mixed>  $item
     */
    private static function optionalPatch(array &$patch, array $item, string $inputKey, string $column): void
    {
        if (array_key_exists($inputKey, $item)) {
            $value = $item[$inputKey];
            $patch[$column] = $value === null || $value === '' ? null : $value;
        }
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $patch
     */
    private static function validateClient(array $item, string $typeCode, array $patch): void
    {
        if (self::stringValue($item['nombre'] ?? null) === '' || $typeCode === '') {
            self::fail('integracion.maestro.itemInvalido');
        }
        foreach (['erpCliente', 'erpArticulo'] as $key) {
            if (array_key_exists($key, $item) && $item[$key] !== null && mb_strlen((string) $item[$key]) > 15) {
                self::fail('integracion.maestro.itemInvalido');
            }
        }
    }

    private static function upsertRow(string $table, ?object $existing, string $code, array $patch, bool $dryRun): string
    {
        $now = now();
        $patch['activo'] = true;
        $patch['inhabilitado'] = false;
        $patch['updated_at'] = $now;
        if ($existing === null) {
            $patch['code'] = $code;
            $patch['created_at'] = $now;
            if (! $dryRun) {
                DB::table($table)->insert($patch);
            }

            return 'alta';
        }

        $changes = [];
        foreach ($patch as $column => $value) {
            if (in_array($column, ['updated_at', 'created_at'], true)) {
                continue;
            }
            if (($existing->{$column} ?? null) !== $value) {
                $changes[$column] = $value;
            }
        }
        if ($changes === []) {
            return 'sinCambios';
        }
        if (! $dryRun) {
            DB::table($table)->where('id', $existing->id)->update($patch);
        }

        return 'modificacion';
    }

    /**
     * @param  array<string, bool>  $seen
     * @param  list<array<string, mixed>>  $results
     * @param  list<array<string, mixed>>  $errors
     */
    private static function disableAbsent(
        string $table,
        string $codeColumn,
        array $seen,
        array &$results,
        array &$errors,
        bool $dryRun,
        string $resource,
    ): void {
        $rows = DB::table($table)->get();
        foreach ($rows as $row) {
            $code = (string) $row->{$codeColumn};
            if (isset($seen[$code]) || self::isInactive($row)) {
                continue;
            }
            if ($resource === 'tipos-tarea' && (bool) ($row->is_default ?? false)) {
                $errors[] = self::error(null, $code, 'integracion.maestro.bajaNoPermitida');
                continue;
            }
            if (! $dryRun) {
                $update = ['activo' => false, 'inhabilitado' => true, 'updated_at' => now()];
                DB::table($table)->where('id', $row->id)->update($update);
            }
            self::appendResult($results, null, $code, 'inhabilitado');
        }
    }

    private static function hasStatusColumns(string $table): bool
    {
        return DB::getSchemaBuilder()->hasColumn($table, 'activo')
            && DB::getSchemaBuilder()->hasColumn($table, 'inhabilitado');
    }

    private static function isInactive(object $row): bool
    {
        return (property_exists($row, 'activo') && ! (bool) $row->activo)
            || (property_exists($row, 'inhabilitado') && (bool) $row->inhabilitado);
    }

    private static function assignmentKey(object $row): string
    {
        $clientCode = (string) DB::table('PQ_PARTES_CLIENTES')->where('id', $row->cliente_id)->value('code');
        $typeCode = (string) DB::table('PQ_PARTES_TIPOS_TAREA')->where('id', $row->tipo_tarea_id)->value('code');

        return $clientCode.'|'.$typeCode;
    }

    /**
     * @param  list<array<string, mixed>>  $results
     * @param  list<array<string, mixed>>  $errors
     */
    private static function reject(array &$results, array &$errors, ?int $index, string $code, string $reason): void
    {
        self::appendResult($results, $index, $code, 'rechazado', $reason);
        if (count($errors) < self::MAX_ERRORS) {
            $errors[] = self::error($index, $code, $reason);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $results
     */
    private static function appendResult(array &$results, ?int $index, string $code, string $action, ?string $reason = null): void
    {
        $results[] = array_filter([
            'indice' => $index,
            'clave' => $code,
            'accion' => $action,
            'codigo' => $reason,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  list<array<string, mixed>>  $errors
     * @return array<string, mixed>
     */
    private static function error(?int $index, string $code, string $reason): array
    {
        return ['indice' => $index, 'clave' => $code, 'codigo' => $reason];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  list<array<string, mixed>>  $results
     * @param  list<array<string, mixed>>  $errors
     * @return list<object>
     */
    private static function result(string $resource, array $items, array $results, array $errors, bool $dryRun): array
    {
        $applied = count(array_filter($results, static fn (array $row): bool => in_array($row['accion'] ?? null, ['alta', 'modificacion', 'inhabilitado'], true)));
        $omitted = count(array_filter($results, static fn (array $row): bool => ($row['accion'] ?? null) === 'sinCambios'));
        $rejected = count(array_filter($results, static fn (array $row): bool => ($row['accion'] ?? null) === 'rechazado'));

        return array_merge([self::object([
            'tipoRegistro' => 'RESUMEN',
            'recurso' => $resource,
            'dryRun' => $dryRun,
            'recibidos' => count($items),
            'aplicados' => $applied,
            'omitidos' => $omitted,
            'rechazados' => $rejected,
            'erroresTruncados' => count($errors) >= self::MAX_ERRORS,
        ])], array_map(
            static fn (array $row): object => self::object(array_merge(['tipoRegistro' => 'ITEM'], $row)),
            $results
        ));
    }

    private static function dryRun(array $params): bool
    {
        return (bool) ($params['dryRun'] ?? false);
    }

    private static function object(array $values): object
    {
        return (object) $values;
    }

    private static function fail(string $respuesta): never
    {
        throw new PartesIntegracionMaestrosException($respuesta);
    }
}
