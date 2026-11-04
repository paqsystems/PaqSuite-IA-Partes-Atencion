<?php

namespace App\Services\Partes;

final class PartesMasivoAccessUrlBuilder
{
    public function buildPortalUrl(string $tenantCode): string
    {
        $tenant = strtolower(trim($tenantCode));
        if ($tenant === '') {
            $tenant = 'demo';
        }

        return 'https://'.$tenant.'.partesatencion.paqsystems.com';
    }

    public function buildForgotPasswordUrl(string $tenantCode, string $locale): string
    {
        $cliente = strtoupper(trim($tenantCode));
        $query = http_build_query([
            'cliente' => $cliente !== '' ? $cliente : 'DEMO',
            'locale' => PartesMasivoLocale::normalize($locale),
        ]);

        return $this->buildPortalUrl($tenantCode).'/forgot-password?'.$query;
    }
}
