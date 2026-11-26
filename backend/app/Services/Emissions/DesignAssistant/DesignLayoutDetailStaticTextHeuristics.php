<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * Heurísticas CHK-02: texto literal (no binding) en banda detalle.
 */
final class DesignLayoutDetailStaticTextHeuristics
{
    /**
     * @return list<array{action: string, payload: array<string, mixed>, resultado: string}>
     */
    public static function tryMatch(string $message): array
    {
        $text = mb_strtolower(trim($message), 'UTF-8');

        if (preg_match('/texto\s+fijo|texto\s+literal|etiqueta\s+fija/u', $text) !== 1
            && preg_match('/que\s+diga/u', $text) !== 1) {
            return [];
        }

        if (preg_match('/detalle|detallada/u', $text) !== 1) {
            return [];
        }

        $literal = self::extractLiteral($message);
        if ($literal === null || $literal === '') {
            return [];
        }

        if (mb_strlen($literal) > 200) {
            return [];
        }

        return [[
            'action' => 'addDetailStaticText',
            'payload' => ['text' => $literal],
            'resultado' => 'ok',
        ]];
    }

    private static function extractLiteral(string $message): ?string
    {
        $patterns = [
            '/que\s+diga\s+[«"\']([^«"\'\n]+?)[»"\']\s*(?:en\s+el\s+)?detalle/iu',
            '/que\s+diga\s+(.+?)\s+en\s+el\s+detalle/iu',
            '/texto\s+fijo\s+(?:que\s+)?diga\s+[«"\']([^«"\'\n]+?)[»"\']/iu',
            '/texto\s+fijo\s+(?:que\s+)?diga\s+(\S+)/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($message), $m) !== 1) {
                continue;
            }
            $value = trim((string) ($m[1] ?? ''), " \t\n\r\0\x0B\"'«»");
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}