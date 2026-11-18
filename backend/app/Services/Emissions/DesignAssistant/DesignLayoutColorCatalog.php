<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * Resuelve nombres de color en español → hex (solo planner host; sidecar acepta hex/rgb).
 */
final class DesignLayoutColorCatalog
{
    /** @var array<string, string> */
    private const MAP = [
        'azul marino' => '#001f3f',
        'azulmarino' => '#001f3f',
        'navy' => '#001f3f',
        'rojo' => '#cc0000',
        'verde' => '#008000',
        'negro' => '#000000',
        'blanco' => '#ffffff',
        'gris' => '#808080',
        'azul' => '#0000ff',
    ];

    public static function resolve(?string $phrase): ?string
    {
        if ($phrase === null) {
            return null;
        }

        $key = mb_strtolower(trim($phrase), 'UTF-8');
        $key = preg_replace('/\s+/u', ' ', $key) ?? $key;
        if ($key === '') {
            return null;
        }

        if (isset(self::MAP[$key])) {
            return self::MAP[$key];
        }

        if (preg_match('/^#[0-9a-f]{6}$/i', $key) === 1) {
            return strtolower($key);
        }

        return null;
    }

    /**
     * Busca un color nombrado en el mensaje del usuario.
     */
    public static function extractFromMessage(string $message): ?string
    {
        $lower = mb_strtolower($message, 'UTF-8');
        $keys = array_keys(self::MAP);
        usort($keys, static fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        foreach ($keys as $name) {
            if (mb_strpos($lower, $name, 0, 'UTF-8') !== false) {
                return self::MAP[$name];
            }
        }

        if (preg_match('/#([0-9a-f]{6})\b/i', $message, $m) === 1) {
            return '#'.strtolower($m[1]);
        }

        return null;
    }
}