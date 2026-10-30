<?php

namespace App\Services\Emissions;

use Illuminate\Support\Facades\Http;

/**
 * Cliente HTTP para POST /render del sidecar DX (contrato RemoteDxReportingEngine).
 */
final class PartesDxReportingHttpClient
{
    /**
     * @param  array<string, string>  $headers
     * @param  array<string, mixed>  $payload
     * @return array{status: int, body: array<string, mixed>}
     */
    public function __invoke(string $url, array $headers, array $payload): array
    {
        $timeout = (int) ($payload['timeoutSeconds'] ?? 30);
        if ($timeout < 1) {
            $timeout = 30;
        }

        $payload = self::ensureRepxLayout($payload);

        $response = Http::timeout($timeout)
            ->withHeaders($headers)
            ->asJson()
            ->post($url, $payload);

        $body = $response->json();
        if (! is_array($body)) {
            $body = [];
        }

        return [
            'status' => $response->status(),
            'body' => $body,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function ensureRepxLayout(array $payload): array
    {
        $layout = $payload['layoutDefinition'] ?? null;
        if (is_string($layout) && str_contains($layout, 'XtraReportsLayoutSerializer')) {
            $payload['layoutMime'] = is_string($payload['layoutMime'] ?? null) && $payload['layoutMime'] !== ''
                ? $payload['layoutMime']
                : 'application/xml';

            return $payload;
        }

        $path = resource_path('emissions/partes_consultaDetallada_principal.repx');
        if (! is_file($path)) {
            return $payload;
        }

        $xml = file_get_contents($path);
        if (! is_string($xml) || ! str_contains($xml, 'XtraReportsLayoutSerializer')) {
            return $payload;
        }

        $payload['layoutDefinition'] = $xml;
        $payload['layoutMime'] = 'application/xml';

        return $payload;
    }
}
