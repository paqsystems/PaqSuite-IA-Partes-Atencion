<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * Pedidos de eliminación (CHK-28..32): sin whitelist v1 → no mapear acciones.
 */
final class DesignLayoutRemovalIntentHeuristics
{
    public static function matches(string $message): bool
    {
        $text = mb_strtolower(trim($message), 'UTF-8');

        if (preg_match('/\b(agreg|incorpor|sum[aá]|inclu[ií])/u', $text) === 1) {
            return false;
        }

        if (preg_match('/\b(sac[aá]|quit[aá]|elimin[aá]|sacar|quitar|eliminar|borrar|borrá|borra\b|suprimi)/u', $text) !== 1) {
            return false;
        }

        return preg_match(
            '/\b(agrupaci[oó]n|columna|campo\s+calculado|calculad[oa]|texto|t[ií]tulo|gr[aá]fico|control|cuadro\s+de\s+texto)\b/u',
            $text,
        ) === 1;
    }
}