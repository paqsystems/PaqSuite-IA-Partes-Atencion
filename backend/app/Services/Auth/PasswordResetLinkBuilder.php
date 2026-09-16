<?php

namespace App\Services\Auth;

final class PasswordResetLinkBuilder
{
    private const RESERVED_LABELS = [
        'www',
        'api',
        'backend',
        'backenddev',
        'frontend',
        'mail',
    ];

    private const CANONICAL_SUFFIXES = [
        '.partesatencion.paqsystems.com',
        '.partesatenciones.paqsystems.com',
    ];

    public function build(string $token, string $locale, ?string $cliente = null): string
    {
        $base = rtrim($this->resolveSpaBaseUrl(), '/');
        $query = [
            'token' => $token,
            'locale' => $locale,
        ];

        $clienteCode = strtoupper(trim((string) $cliente));
        if ($clienteCode !== '') {
            $query['cliente'] = $clienteCode;
        }

        return $base.'/reset-password?'.http_build_query($query);
    }

    public function resolveSpaBaseUrl(): string
    {
        $explicit = rtrim((string) config('app.frontend_spa_url', ''), '/');
        if ($explicit !== '') {
            return $explicit;
        }

        $frontendUrl = rtrim((string) config('app.frontend_url', ''), '/');
        $host = strtolower((string) parse_url($frontendUrl, PHP_URL_HOST));
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        $canonicalLabel = $this->canonicalClienteLabel($host);
        if ($canonicalLabel === null) {
            return $frontendUrl !== '' ? $frontendUrl : 'http://localhost';
        }

        if ($canonicalLabel === 'desarrollo') {
            return rtrim((string) config(
                'app.frontend_spa_url_dev',
                'https://partesatencionpaqsystemsdev.vercel.app'
            ), '/');
        }

        return rtrim((string) config(
            'app.frontend_spa_url_prod',
            'https://partesatencionpaqsystems.vercel.app'
        ), '/');
    }

    private function canonicalClienteLabel(string $host): ?string
    {
        foreach (self::CANONICAL_SUFFIXES as $suffix) {
            if (! str_ends_with($host, $suffix)) {
                continue;
            }

            $label = substr($host, 0, -strlen($suffix));
            if ($label === '' || str_contains($label, '.') || in_array($label, self::RESERVED_LABELS, true)) {
                return null;
            }

            return $label;
        }

        return null;
    }
}
