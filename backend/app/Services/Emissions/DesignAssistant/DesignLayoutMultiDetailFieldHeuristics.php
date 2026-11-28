<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * CHK-22: varias columnas/campos en banda detalle (N× addDetailField).
 */
final class DesignLayoutMultiDetailFieldHeuristics
{
    /**
     * @return list<array{action: string, payload: array<string, mixed>, resultado: string}>
     */
    public static function tryMatch(string $message, string $processCode = DesignLayoutWhitelist::PROCESS_CODE): array
    {
        $text = mb_strtolower(trim($message), 'UTF-8');

        if (preg_match('/campo\s+calculad/u', $text) === 1) {
            return [];
        }

        $hasColumnContext = preg_match('/\b(columnas?)\b/u', $text) === 1
            || preg_match('/\bcampos\b/u', $text) === 1
            || preg_match('/arma.*detalle/u', $text) === 1
            || preg_match('/detalle\s+en\s+columnas/u', $text) === 1;

        if (! $hasColumnContext) {
            return [];
        }

        $fields = DesignLayoutSemanticFieldResolver::resolveFieldsFromList($message, $processCode);
        if (count($fields) < 2) {
            return [];
        }

        $actions = [];
        foreach ($fields as $field) {
            if (! DesignLayoutWhitelist::isSchemaField($field)) {
                continue;
            }
            $actions[] = [
                'action' => 'addDetailField',
                'payload' => ['field' => $field],
                'resultado' => 'ok',
            ];
        }

        return $actions;
    }
}