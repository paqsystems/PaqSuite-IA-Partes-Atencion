<?php

namespace App\Support\Emissions;

/**
 * Proyección GEN-37 → formato Field List / Prompt-to-Report del sidecar DX (lab).
 * SoT: database/emission-designer-schemas/{processCode}.json — no editar exports a mano.
 */
final class EmissionDesignerSchemaDxSidecarExport
{
    /**
     * Rutas relativas a base_path() por processCode (alias reportUrl / processCode sidecar).
     *
     * @return array<string, list<string>>
     */
    public static function aliasTargetsByProcessCode(): array
    {
        return [
            'partes.informes.consultaDetallada' => [
                'scripts/dx-reporting/schemas/partes.informes.consultaDetallada.json',
                'scripts/dx-reporting/schemas/partes.consultaDetallada.principal.json',
                'scripts/dx-reporting/schemas/partes_consultaDetallada_principal.json',
            ],
            'partes.informes.paqueteHoras' => [
                'resources/emissions/schemas/partes.informes.paqueteHoras.json',
                'resources/emissions/schemas/partes_paqueteHoras_principal.json',
            ],
        ];
    }

    public static function canonicalPath(string $processCode): string
    {
        return base_path('database/emission-designer-schemas/'.$processCode.'.json');
    }

    /**
     * @return array{name: string, columns: list<array<string, mixed>>}
     */
    public static function toSidecarPayloadFromCanonical(array $canonical): array
    {
        $displayName = trim((string) ($canonical['datasetDisplayName'] ?? ''));
        if ($displayName === '') {
            throw new \InvalidArgumentException('Falta datasetDisplayName en schema canónico.');
        }

        $columns = [];
        foreach ($canonical['fields'] ?? [] as $field) {
            if (! is_array($field)) {
                continue;
            }
            $name = trim((string) ($field['name'] ?? ''));
            $type = trim((string) ($field['type'] ?? ''));
            if ($name === '' || $type === '') {
                continue;
            }

            $column = [
                'name' => $name,
                'type' => $type,
            ];

            $label = trim((string) ($field['label'] ?? ''));
            if ($label !== '') {
                $column['label'] = $label;
            }

            $description = trim((string) ($field['description'] ?? ''));
            if ($description !== '') {
                $column['description'] = $description;
            }

            if (is_array($field['synonyms'] ?? null) && $field['synonyms'] !== []) {
                $synonyms = [];
                foreach ($field['synonyms'] as $syn) {
                    if (is_string($syn) && trim($syn) !== '') {
                        $synonyms[] = trim($syn);
                    }
                }
                if ($synonyms !== []) {
                    $column['synonyms'] = $synonyms;
                }
            }

            $columns[] = $column;
        }

        if ($columns === []) {
            throw new \InvalidArgumentException('Schema canónico sin fields.');
        }

        return [
            'name' => $displayName,
            'columns' => $columns,
        ];
    }

    public static function encodeSidecarJson(array $payload): string
    {
        $encoded = json_encode(
            $payload,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );

        return $encoded."\n";
    }

    /**
     * @return list<string> rutas absolutas escritas
     */
    public static function exportAllAliases(): array
    {
        $written = [];
        foreach (self::aliasTargetsByProcessCode() as $processCode => $relPaths) {
            $canonical = self::readCanonical($processCode);
            $payload = self::toSidecarPayloadFromCanonical($canonical);
            $json = self::encodeSidecarJson($payload);

            foreach ($relPaths as $rel) {
                $abs = base_path($rel);
                $dir = dirname($abs);
                if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
                    throw new \RuntimeException('No se pudo crear '.$dir);
                }
                if (file_put_contents($abs, $json) === false) {
                    throw new \RuntimeException('No se pudo escribir '.$abs);
                }
                $written[] = $abs;
            }
        }

        return $written;
    }

    /**
     * @return array<string, mixed>
     */
    public static function readCanonical(string $processCode): array
    {
        $path = self::canonicalPath($processCode);
        if (! is_readable($path)) {
            throw new \RuntimeException('Schema canónico no legible: '.$path);
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('JSON canónico inválido: '.$path);
        }

        return $decoded;
    }
}