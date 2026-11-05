<?php

declare(strict_types=1);

namespace App\Support\Emissions;

/**
 * Normaliza report_code del catálogo al Url que persiste el sidecar (FileReportStorage.SafeUrl).
 */
final class EmissionReportDxStorageUrl
{
    public static function fromCatalogCode(string $code): string
    {
        $trimmed = trim(str_replace('\\', '/', $code));
        if ($trimmed === '') {
            return 'report';
        }

        $lastSlash = strrpos($trimmed, '/');
        $last = $lastSlash === false ? $trimmed : substr($trimmed, $lastSlash + 1);

        $chars = preg_split('//u', $last, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $safe = '';
        foreach ($chars as $ch) {
            if (preg_match('/\p{L}|\p{N}/u', $ch) === 1 || in_array($ch, ['-', '_', ':'], true)) {
                $safe .= $ch;
            } else {
                $safe .= '-';
            }
        }

        $safe = trim($safe);

        return $safe !== '' ? $safe : 'report';
    }
}
