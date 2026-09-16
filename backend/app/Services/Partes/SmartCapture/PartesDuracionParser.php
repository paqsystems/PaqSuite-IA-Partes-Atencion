<?php

namespace App\Services\Partes\SmartCapture;

/**
 * Reloj h:mm (1:25 → 85) ≠ horas decimales (1.25 h → 75).
 */
final class PartesDuracionParser
{
    public static function toMinutos(mixed $raw): ?int
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (is_int($raw) || (is_float($raw) && floor($raw) === $raw)) {
            $minutos = (int) $raw;

            return $minutos > 0 && $minutos <= 1440 ? $minutos : null;
        }
        if (is_float($raw) || (is_numeric($raw) && str_contains((string) $raw, '.'))) {
            $minutos = (int) round(((float) $raw) * 60);

            return $minutos > 0 && $minutos <= 1440 ? $minutos : null;
        }

        $trimmed = trim(str_replace(',', '.', (string) $raw));
        if (preg_match('/^(\d{1,2}):([0-5]\d)$/', $trimmed, $clock) === 1) {
            $minutos = ((int) $clock[1]) * 60 + (int) $clock[2];

            return $minutos > 0 && $minutos <= 1440 ? $minutos : null;
        }
        if (preg_match('/^(\d+(?:\.\d+)?)\s*(?:h|hs|hrs|hora|horas)?$/i', $trimmed, $hours) !== 1) {
            return null;
        }
        $amount = (float) $hours[1];
        if ($amount <= 0) {
            return null;
        }
        if (str_contains($trimmed, '.') || preg_match('/h/i', $trimmed) === 1) {
            $minutos = (int) round($amount * 60);

            return $minutos > 0 && $minutos <= 1440 ? $minutos : null;
        }
        $minutos = (int) $amount;

        return $minutos > 0 && $minutos <= 1440 ? $minutos : null;
    }

    public static function isValidTramo(int $minutos, int $tramoMinutos): bool
    {
        $tramo = $tramoMinutos > 0 ? $tramoMinutos : 15;

        return $minutos > 0 && $minutos <= 1440 && $minutos % $tramo === 0;
    }
}
