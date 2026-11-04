<?php

namespace App\Services\Partes;

final class PartesMasivoLocale
{
    /** @var list<string> */
    private const SUPPORTED = ['es', 'en', 'pt', 'fr', 'it'];

    public static function normalize(?string $locale): string
    {
        $value = strtolower(trim((string) $locale));
        $base = strlen($value) >= 2 ? substr($value, 0, 2) : $value;

        return in_array($base, self::SUPPORTED, true) ? $base : 'es';
    }
}
