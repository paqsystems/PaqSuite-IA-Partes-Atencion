<?php

namespace App\Repositories\Sp;

use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Parametros\ParametroValorCodec;
use PaqSuite\LaravelCore\Support\ParametroMetaUpsert;

final class SpParametroRepository implements \PaqSuite\LaravelCore\Parametros\Contracts\ParametroRepository
{
    public function __construct(
        private readonly SpCaller $spCaller
    ) {
    }

    public function listByPrograma(string $programa): array
    {
        $rows = $this->spCaller->call('pq_sp_parametros_list', [
            'programa' => $programa,
        ]);

        return array_map(fn (object $row): array => $this->mapRow($row), $rows);
    }

    public function get(string $programa, string $clave): ?array
    {
        $rows = $this->spCaller->call('pq_sp_parametros_get', [
            'programa' => $programa,
            'clave' => $clave,
        ]);

        if ($rows === []) {
            return null;
        }

         return $this->mapRow($rows[0]);
    }

    public function updateValor(string $programa, string $clave, mixed $valor): bool
    {
        $existing = $this->get($programa, $clave);
        if ($existing === null) {
            return false;
        }

        $encoded = $this->encodeValor((string) $existing['tipoValor'], $valor);
        $rows = $this->spCaller->call('pq_sp_parametros_update', array_merge([
            'programa' => $programa,
            'clave' => $clave,
        ], $encoded));

        $updated = (int) ($rows[0]->updated_rows ?? 0);

        return $updated > 0;
    }

    public function insertIfAbsent(
        string $programa,
        string $clave,
        string $tipoValor,
        mixed $valor,
        array $meta = [],
        ?string $precisionFecha = null,
    ): void {
        $encoded = $this->encodeValor($tipoValor, $valor);
        $applied = ParametroMetaUpsert::apply(['caption' => null, 'tooltip' => null], $meta);

        $this->spCaller->execute('pq_sp_parametros_insert_if_absent', array_merge([
            'programa' => $programa,
            'clave' => $clave,
            'tipo_valor' => $tipoValor,
            'precision_fecha' => $tipoValor === 'F' ? ($precisionFecha ?? 'date') : null,
            'caption' => $applied['caption'] ?? $clave,
            'tooltip' => $applied['tooltip'] ?? null,
            'meta_json' => $this->encodeMetaFromSeed($meta),
        ], $encoded));
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRow(object $row): array
    {
        $tipoValor = (string) ($row->tipo_valor ?? $row->tipoValor ?? 'S');
        $valorFecha = $row->valor_fecha ?? null;
        $valorFechaStr = $valorFecha instanceof DateTimeInterface
            ? $valorFecha->format('Y-m-d H:i:s')
            : ($valorFecha !== null ? (string) $valorFecha : null);

        $valor = ParametroValorCodec::decodeStored(
            $tipoValor,
            $row->valor_string ?? null,
            $row->valor_texto ?? null,
            isset($row->valor_int) ? (int) $row->valor_int : null,
            isset($row->valor_decimal) ? (string) $row->valor_decimal : null,
            isset($row->valor_bool) ? (bool) $row->valor_bool : null,
            $valorFechaStr,
            $row->precision_fecha ?? null,
        );

        return [
            'programa' => (string) ($row->programa ?? ''),
            'clave' => (string) ($row->clave ?? ''),
            'tipoValor' => $tipoValor,
            'valor' => $valor,
            'caption' => (string) ($row->caption ?? $row->clave ?? ''),
            'tooltip' => isset($row->tooltip) ? (string) $row->tooltip : null,
            'precisionFecha' => isset($row->precision_fecha) ? (string) $row->precision_fecha : null,
            'meta' => $this->decodeMeta(isset($row->meta_json) ? (string) $row->meta_json : null),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function encodeValor(string $tipoValor, mixed $valor): array
    {
        return match ($tipoValor) {
            'S' => ['valor_string' => $valor === null ? null : (string) $valor],
            'T' => ['valor_texto' => $valor === null ? null : (string) $valor],
            'I' => ['valor_int' => $valor === null ? null : (int) $valor],
            'D' => ['valor_decimal' => $valor === null ? null : (string) $valor],
            'B' => ['valor_bool' => $valor === null ? null : (int) (bool) $valor],
            'F' => ['valor_fecha' => $valor instanceof DateTimeInterface
                ? $valor->format('Y-m-d H:i:s')
                : ($valor === null ? null : (string) $valor)],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function encodeMetaFromSeed(array $meta): ?string
    {
        $out = [];
        if (isset($meta['enum']) && is_array($meta['enum'])) {
            $out['enum'] = array_values(array_map('strval', $meta['enum']));
        }

        return $out === [] ? null : json_encode($out, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array{enum?: list<string>}|null
     */
    private function decodeMeta(?string $metaJson): ?array
    {
        if ($metaJson === null || $metaJson === '') {
            return null;
        }

        $decoded = json_decode($metaJson, true);
        if (! is_array($decoded) || $decoded === []) {
            return null;
        }

        $out = [];
        if (isset($decoded['enum']) && is_array($decoded['enum'])) {
            $out['enum'] = array_values(array_map('strval', $decoded['enum']));
        }

        return $out === [] ? null : $out;
    }
}
