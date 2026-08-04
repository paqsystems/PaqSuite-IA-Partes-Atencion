<?php

namespace App\Repositories\Sp\Partes;

use App\Repositories\Sp\SpCaller;
use App\Services\Partes\PartesTareaException;
use App\Services\Partes\PartesTareaOperations;

final class PartesTareaRepository
{
    public function __construct(private readonly SpCaller $spCaller)
    {
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function list(array $params): array
    {
        $rows = $this->spCaller->call('pq_sp_partes_tarea_list', $params);
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
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function listIds(array $params): array
    {
        $rows = $this->spCaller->call('pq_sp_partes_tarea_list_ids', $params);
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
            throw new PartesTareaException('partes.tarea.notFound', 404);
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

    public function tramoMinutos(): int
    {
        return PartesTareaOperations::resolveTramoMinutos();
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
            if ($key === 'row_version' || $key === 'rowVersion') {
                $out['rowVersion'] = is_string($value)
                    ? strtoupper($value)
                    : PartesTareaOperations::encodeRowVersion($value);
                continue;
            }
            $camel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', (string) $key))));
            if (in_array($key, ['sin_cargo', 'presencial', 'cerrado', 'es_tarea', 'sinCargo', 'presencial', 'cerrado', 'esTarea'], true)
                || in_array($camel, ['sinCargo', 'presencial', 'cerrado', 'esTarea'], true)) {
                $value = (bool) $value;
            }
            $out[$camel] = $value;
        }

        return $out;
    }
}
