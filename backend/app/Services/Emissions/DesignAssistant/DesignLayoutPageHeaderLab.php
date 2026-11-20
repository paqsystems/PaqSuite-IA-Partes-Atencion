<?php

namespace App\Services\Emissions\DesignAssistant;

/**
 * Contexto lab oleada 5 — defaults de diseño (CHK-06 Empresa demo).
 */
final class DesignLayoutPageHeaderLab
{
    public const DEMO_EMPRESA_TEXT = 'Empresa demo';

    public static function resolveTenantCliente(): ?string
    {
        if (! function_exists('app')) {
            return null;
        }

        try {
            $application = app();
            if (! method_exists($application, 'bound') || ! $application->bound('request')) {
                return null;
            }
            $header = request()->header('X-Paq-Cliente');

            return is_string($header) ? trim($header) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * TR-015 §3: "Empresa demo" solo en APP_ENV=local, tenant DEV o PHPUnit (testing).
     */
    public static function allowsDemoEmpresaDefault(?string $tenantCliente = null): bool
    {
        $appEnv = strtolower(trim((string) (
            $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? getenv('APP_ENV') ?: 'production'
        )));

        if (in_array($appEnv, ['local', 'testing'], true)) {
            return true;
        }

        $tenant = strtoupper(trim((string) ($tenantCliente ?? self::resolveTenantCliente() ?? '')));

        return $tenant === 'DEV';
    }
}