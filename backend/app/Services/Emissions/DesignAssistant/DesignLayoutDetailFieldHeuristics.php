<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * Heurísticas CHK-01: agregar columna/campo en banda detalle.
 */
final class DesignLayoutDetailFieldHeuristics
{
    /**
     * @return list<array{action: string, payload: array<string, mixed>, resultado: string}>
     */
    public static function tryMatch(string $message): array
    {
        $text = mb_strtolower(trim($message), 'UTF-8');

        if (preg_match('/agreg(a|á|ar|ue)|incorpor(a|á|ar|e)|sum(a|á|ar|e)|inclu[ií]/u', $text) !== 1) {
            return [];
        }

        if (preg_match('/(columna|campo).*(detalle|detall)/u', $text) !== 1
            && preg_match('/(detalle|detall).*(columna|campo)/u', $text) !== 1
            && preg_match('/en\s+el\s+detalle/u', $text) !== 1) {
            return [];
        }

        // CHK-02: texto fijo, no campo de dataset
        if (preg_match('/texto\s+fijo|que\s+diga\b/u', $text) === 1) {
            return [];
        }

        $field = DesignLayoutSemanticFieldResolver::resolveFromMessage($message);
        if ($field === null || ! DesignLayoutWhitelist::isSchemaField($field)) {
            return [];
        }

        return [[
            'action' => 'addDetailField',
            'payload' => ['field' => $field],
            'resultado' => 'ok',
        ]];
    }
}