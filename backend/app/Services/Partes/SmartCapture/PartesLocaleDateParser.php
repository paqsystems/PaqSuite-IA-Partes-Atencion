<?php

namespace App\Services\Partes\SmartCapture;

use App\Services\Partes\PartesMasivoLocale;
use Carbon\Carbon;

/**
 * Interpreta fechas libres según el locale i18n (no siempre d/m/Y latino).
 * Persistencia canónica: Y-m-d.
 */
final class PartesLocaleDateParser
{
    /**
     * Locales con orden mes/día/año en fechas con barras (p. ej. 07/11/2026 = 11 de julio).
     *
     * @var list<string>
     */
    private const MDY_LOCALES = ['en'];

    public static function toIso(string $raw, ?string $locale = 'es'): ?string
    {
        $value = trim($raw);
        if ($value === '') {
            return null;
        }
        if (self::isValidIsoDate($value)) {
            return $value;
        }

        $code = PartesMasivoLocale::normalize($locale);
        if (preg_match('/^(\d{1,2})[\/.\-](\d{1,2})[\/.\-](\d{2,4})$/', $value, $parts) === 1) {
            $first = (int) $parts[1];
            $second = (int) $parts[2];
            $year = (int) $parts[3];
            if ($year < 100) {
                $year += 2000;
            }

            [$day, $month] = self::resolveDayMonth($first, $second, $code);
            if ($day !== null && checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }

            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{0: int|null, 1: int}
     */
    private static function resolveDayMonth(int $first, int $second, string $locale): array
    {
        if ($first > 12 && $second <= 12) {
            return [$first, $second];
        }
        if ($second > 12 && $first <= 12) {
            return [$second, $first];
        }

        if (in_array($locale, self::MDY_LOCALES, true)) {
            return [$second, $first];
        }

        return [$first, $second];
    }

    private static function isValidIsoDate(string $value): bool
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return false;
        }
        try {
            $parsed = Carbon::createFromFormat('Y-m-d', $value);

            return $parsed !== false && $parsed->format('Y-m-d') === $value;
        } catch (\Throwable) {
            return false;
        }
    }
}
