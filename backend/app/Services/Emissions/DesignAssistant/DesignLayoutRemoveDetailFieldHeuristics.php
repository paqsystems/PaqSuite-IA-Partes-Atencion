<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * CHK-28: eliminar columna/campo del detalle (v1.3 solo observacion).
 */
final class DesignLayoutRemoveDetailFieldHeuristics
{
    /**
     * @return list<array{action: string, payload: array<string, mixed>, resultado: string}>
     */
    public static function tryMatch(string $message): array
    {
        $text = mb_strtolower(trim($message), 'UTF-8');

        if (preg_match('/\b(agreg|incorpor|sum[aá]|inclu[ií])/u', $text) === 1) {
            return [];
        }

        if (preg_match('/\b(sac[aá]|quit[aá]|elimin[aá]|sacar|quitar|eliminar|borrar|borrá|borra\b|suprimi)/u', $text) !== 1) {
            return [];
        }

        $detailContext = preg_match('/\b(columna|campo)\b/u', $text) === 1
            && preg_match('/\b(detalle|detall)\b/u', $text) === 1;
        $observacionMention = preg_match('/observaci[oó]n/u', $text) === 1;

        if (! $detailContext && ! ($observacionMention && preg_match('/\b(detalle|detall|columna|campo)\b/u', $text) === 1)) {
            return [];
        }

        $field = DesignLayoutSemanticFieldResolver::resolveFromMessage($message);
        if ($field !== 'observacion') {
            return [];
        }

        return [[
            'action' => 'removeDetailField',
            'payload' => ['field' => 'observacion'],
            'resultado' => 'ok',
        ]];
    }
}