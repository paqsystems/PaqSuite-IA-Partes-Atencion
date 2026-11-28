<?php

namespace App\Services\Emissions\DesignAssistant;

use PaqSuite\LaravelCore\Emissions\Design\ChkCatalogLoader;

/**
 * Resuelve la ruta de la matriz CHK (package laravel-core o fallbacks lab).
 */
final class DesignLayoutChkCatalogBridge
{
    public static function matrixPath(): string
    {
        $env = getenv('EMISSION_DESIGN_CHK_MATRIX_PATH');
        if (is_string($env) && $env !== '') {
            return $env;
        }

        if (class_exists(ChkCatalogLoader::class)) {
            $packaged = ChkCatalogLoader::defaultResourcePath();
            if (is_readable($packaged)) {
                return $packaged;
            }
        }

        $vendor = dirname(__DIR__, 4).'/vendor/paqsuite/laravel-core/resources/emissions/emission-design-chk-matriz.json';
        if (is_readable($vendor)) {
            return $vendor;
        }

        $frameworkDocs = 'C:/Programacion/PaqSuite-IA-FRAMEWORK/docs/02-producto/15-reportes-emisiones-ia-checklist-matriz.json';
        if (is_readable($frameworkDocs)) {
            return $frameworkDocs;
        }

        return $vendor;
    }

    public static function loader(): ChkCatalogLoader
    {
        return new ChkCatalogLoader(self::isPackagedReadable() ? null : self::matrixPath());
    }

    private static function isPackagedReadable(): bool
    {
        if (! class_exists(ChkCatalogLoader::class)) {
            return false;
        }

        return is_readable(ChkCatalogLoader::defaultResourcePath());
    }
}