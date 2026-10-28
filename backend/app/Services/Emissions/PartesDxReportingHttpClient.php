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

        $response = Http::timeout($timeout)
            ->withHeaders($headers)
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
}
