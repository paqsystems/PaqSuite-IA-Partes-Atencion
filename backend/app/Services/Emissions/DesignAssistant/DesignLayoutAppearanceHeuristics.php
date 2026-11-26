<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * Mapeo heurístico de frases de formato → setControlAppearance (oleada 3).
 */
final class DesignLayoutAppearanceHeuristics
{
    /**
     * @return list<array{action: string, payload: array<string, mixed>, resultado: string}>
     */
    public static function tryMatch(string $message): array
    {
        $text = mb_strtolower(trim($message), 'UTF-8');

        $fieldAppearance = self::fieldStyle($message, $text);
        if ($fieldAppearance !== null) {
            return [$fieldAppearance];
        }

        $titleAppearance = self::titleStyle($text);
        if ($titleAppearance !== null) {
            return [$titleAppearance];
        }

        return [];
    }

    /**
     * @return array{action: string, payload: array<string, mixed>, resultado: string}|null
     */
    private static function fieldStyle(string $message, string $text): ?array
    {
        if (! self::mentionsAppearance($text)) {
            return null;
        }

        if (! preg_match('/asign|format|estil|fuente|negrita|subrayad|it[aá]lica|cursiva|times|color|azul|\d+\s*pt/u', $text)) {
            return null;
        }

        $fieldName = null;
        if (preg_match('/c[oó]digo de cliente|codigo de cliente/u', $text) === 1
            || preg_match('/\bcliente\s*code\b/u', $text) === 1) {
            $fieldName = 'clienteCode';
        } elseif (preg_match('/nombre de cliente/u', $text) === 1) {
            $fieldName = 'clienteNombre';
        } elseif (preg_match('/(?:campo|columna)\s+(\w+)/u', $text, $m) === 1) {
            $candidate = trim((string) ($m[1] ?? ''));
            if (DesignLayoutWhitelist::isSchemaField($candidate)) {
                $fieldName = $candidate;
            }
        }

        if ($fieldName === null) {
            return null;
        }

        $payload = self::buildAppearancePayload($message, $text, [
            'target' => [
                'kind' => 'field',
                'fieldName' => $fieldName,
            ],
        ]);

        if (! self::hasAppearanceBesidesTarget($payload)) {
            return null;
        }

        return self::action('setControlAppearance', $payload);
    }

    /**
     * @return array{action: string, payload: array<string, mixed>, resultado: string}|null
     */
    private static function titleStyle(string $text): ?array
    {
        if (! preg_match('/t[ií]tulo/u', $text)) {
            return null;
        }

        if (! self::mentionsAppearance($text)) {
            return null;
        }

        if (preg_match('/(?:cambi[aá]|pon[eé]|setea).{0,40}t[ií]tulo\s+(?:a|como|:)\s*\S/u', $text) === 1) {
            return null;
        }

        $payload = self::buildAppearancePayload('', $text, [
            'target' => [
                'kind' => 'reportTitle',
            ],
        ]);

        if (! self::hasAppearanceBesidesTarget($payload)) {
            return null;
        }

        return self::action('setControlAppearance', $payload);
    }

    private static function mentionsAppearance(string $text): bool
    {
        return preg_match(
            '/negrita|cursiva|it[aá]lica|subrayad|centrad|justific|times|fuente|tama[nñ]o|\d+\s*pt|color|azul|rojo|verde/u',
            $text,
        ) === 1;
    }

    /**
     * @param  array<string, mixed>  $base
     * @return array<string, mixed>
     */
    private static function buildAppearancePayload(string $message, string $text, array $base): array
    {
        $payload = $base;

        if (preg_match('/times\s+new\s+rom(?:an|ano)?/ui', $text) === 1) {
            $payload['fontFamily'] = 'Times New Roman';
        } elseif (preg_match('/\b(arial|calibri|courier)\b/ui', $text, $f) === 1) {
            $payload['fontFamily'] = ucwords(mb_strtolower($f[1], 'UTF-8'));
        }

        if (preg_match('/(\d+)\s*(?:pt|puntos?)/u', $text, $m) === 1) {
            $payload['fontSizePt'] = (int) $m[1];
        } elseif (preg_match('/times\s+new\s+rom(?:an|ano)?\s+(\d{1,2})\b/ui', $text, $m) === 1) {
            $payload['fontSizePt'] = (int) $m[1];
        } elseif (preg_match('/,\s*(\d{1,2})\s*,/u', $text, $m) === 1) {
            $payload['fontSizePt'] = (int) $m[1];
        }

        if (preg_match('/negrita/u', $text) === 1) {
            $payload['bold'] = true;
        }

        if (preg_match('/it[aá]lica|cursiva/u', $text) === 1) {
            $payload['italic'] = true;
        }

        if (preg_match('/subrayad/u', $text) === 1) {
            $payload['underline'] = true;
        }

        if (preg_match('/centrad/u', $text) === 1) {
            $payload['textAlign'] = 'center';
            $payload['verticalAlign'] = 'middle';
        } elseif (preg_match('/justificad/u', $text) === 1) {
            $payload['textAlign'] = 'justify';
        } elseif (preg_match('/(?:a la )?izquierda/u', $text) === 1) {
            $payload['textAlign'] = 'left';
        } elseif (preg_match('/(?:a la )?derecha/u', $text) === 1) {
            $payload['textAlign'] = 'right';
        }

        $color = DesignLayoutColorCatalog::extractFromMessage($message !== '' ? $message : $text);
        if ($color !== null) {
            $payload['foreColor'] = $color;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function hasAppearanceBesidesTarget(array $payload): bool
    {
        foreach ($payload as $key => $value) {
            if ($key !== 'target' && $value !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{action: string, payload: array<string, mixed>, resultado: string}
     */
    private static function action(string $action, array $payload): array
    {
        return [
            'action' => $action,
            'payload' => $payload,
            'resultado' => 'ok',
        ];
    }
}