<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * Lee el catálogo GEN-37 del artefacto canónico del diseñador (mismo JSON GEN-15).
 * No inventa un diccionario paralelo.
 */
final class DesignSemanticCatalog
{
    /**
     * @return list<array{name: string, label: string, description: string, synonyms: list<string>}>
     */
    public static function fieldsForProcess(string $processCode): array
    {
        $path = dirname(__DIR__, 4).'/database/emission-designer-schemas/'.$processCode.'.json';
        if (! is_readable($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded) || ! is_array($decoded['fields'] ?? null)) {
            return [];
        }

        $out = [];
        foreach ($decoded['fields'] as $field) {
            if (! is_array($field)) {
                continue;
            }
            $name = trim((string) ($field['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $synonyms = [];
            if (is_array($field['synonyms'] ?? null)) {
                foreach ($field['synonyms'] as $syn) {
                    if (is_string($syn) && trim($syn) !== '') {
                        $synonyms[] = trim($syn);
                    }
                }
            }
            $out[] = [
                'name' => $name,
                'label' => trim((string) ($field['label'] ?? $name)),
                'description' => trim((string) ($field['description'] ?? '')),
                'synonyms' => $synonyms,
            ];
        }

        return $out;
    }

    /**
     * Texto para system prompt LLM (name + label + synonyms).
     */
    public static function promptVocabulary(string $processCode): string
    {
        $lines = [];
        foreach (self::fieldsForProcess($processCode) as $field) {
            $parts = [$field['name'], 'label='.$field['label']];
            if ($field['description'] !== '') {
                $parts[] = 'desc='.$field['description'];
            }
            if ($field['synonyms'] !== []) {
                $parts[] = 'synonyms='.implode('|', $field['synonyms']);
            }
            $lines[] = '- '.implode('; ', $parts);
        }

        return implode("\n", $lines);
    }
}
