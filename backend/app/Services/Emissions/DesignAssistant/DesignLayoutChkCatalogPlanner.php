<?php

namespace App\Services\Emissions\DesignAssistant;

use PaqSuite\LaravelCore\Emissions\Design\ChkCatalogLoader;

/**
 * Capa catálogo GEN-15: mapea mensajes a actionIdLegacy documentados en la matriz v5.
 */
final class DesignLayoutChkCatalogPlanner
{
    /**
     * @return list<array{action: string, payload: array<string, mixed>, resultado: string}>
     */
    public static function tryMatch(string $message, string $processCode = DesignLayoutWhitelist::PROCESS_CODE): array
    {
        if (! class_exists(ChkCatalogLoader::class)) {
            return [];
        }

        try {
            $loader = DesignLayoutChkCatalogBridge::loader();
        } catch (\Throwable) {
            return [];
        }

        $chk22 = $loader->find('CHK-22');
        if (is_array($chk22) && (string) ($chk22['estadoCatalogo'] ?? '') === 'activo') {
            $multi = DesignLayoutMultiDetailFieldHeuristics::tryMatch($message, $processCode);
            if ($multi !== []) {
                return $multi;
            }
        }

        return [];
    }
}