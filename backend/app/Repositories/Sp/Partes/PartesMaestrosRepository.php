<?php

namespace App\Repositories\Sp\Partes;

use App\Repositories\Sp\SpCaller;
use App\Services\Partes\PartesMaestrosException;

final class PartesMaestrosRepository
{
    public function __construct(private readonly SpCaller $spCaller)
    {
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function list(string $procedure, array $params = []): array
    {
        $rows = $this->spCaller->call($procedure, $params);
        $total = 0;
        $items = [];
        foreach ($rows as $row) {
            $arr = $this->rowToCamel((array) $row);
            if (isset($arr['_total'])) {
                $total = (int) $arr['_total'];
                unset($arr['_total']);
            }
            $items[] = $arr;
        }
        if ($total === 0) {
            $total = count($items);
        }

        return ['items' => $items, 'total' => $total];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function getOne(string $procedure, array $params): array
    {
        $rows = $this->spCaller->call($procedure, $params);
        if ($rows === []) {
            throw new PartesMaestrosException('partes.maestros.notFound', 404);
        }

        return $this->rowToCamel((array) $rows[0]);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<array<string, mixed>>
     */
    public function call(string $procedure, array $params = []): array
    {
        $rows = $this->spCaller->call($procedure, $params);

        return array_map(fn ($row) => $this->rowToCamel((array) $row), $rows);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function rowToCamel(array $row): array
    {
        $out = [];
        foreach ($row as $key => $value) {
            if ($key === '_total') {
                $out['_total'] = $value;
                continue;
            }
            $camel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', (string) $key))));
            if (is_numeric($value) && ! str_contains((string) $key, 'code') && ! str_contains((string) $key, 'nombre')
                && ! str_contains((string) $key, 'email') && ! str_contains((string) $key, 'descripcion')
                && ! str_contains((string) $key, 'tipo_funcional')) {
                // keep bools as bool for bit columns
            }
            if (in_array($key, ['supervisor', 'activo', 'inhabilitado', 'is_generico', 'is_default', 'isGenerico', 'isDefault'], true)) {
                $value = (bool) $value;
            }
            $out[$camel] = $value;
        }

        return $out;
    }
}
