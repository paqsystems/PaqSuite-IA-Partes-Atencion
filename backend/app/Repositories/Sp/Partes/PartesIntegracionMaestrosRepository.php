<?php

namespace App\Repositories\Sp\Partes;

use App\Repositories\Sp\SpCaller;

final class PartesIntegracionMaestrosRepository
{
    public function __construct(private readonly SpCaller $spCaller)
    {
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<array<string, mixed>>
     */
    public function process(string $procedure, array $params): array
    {
        $rows = $this->spCaller->call($procedure, $params);

        return array_map(
            fn (object $row): array => $this->rowToCamel((array) $row),
            $rows
        );
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function rowToCamel(array $row): array
    {
        $result = [];
        foreach ($row as $key => $value) {
            $camel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', (string) $key))));
            if (in_array($key, ['dry_run', 'errores_truncados'], true)) {
                $value = (bool) $value;
            }
            $result[$camel] = $value;
        }

        return $result;
    }
}
