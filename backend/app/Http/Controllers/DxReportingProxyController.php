<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

/**
 * Reverse proxy del diseñador DX (DXXRD*) hacia el sidecar ASP.NET en loopback.
 * No es acceso de negocio: no hay SQL. Evita CORS y cookies cross-origin
 * cuando Vercel reescribe /DXXRD al origin de Forge.
 */
class DxReportingProxyController extends Controller
{
    private const PREFIXES = ['DXXRD', 'DXXRDV', 'DXXQB'];

    private const HOP_BY_HOP = [
        'connection',
        'keep-alive',
        'proxy-authenticate',
        'proxy-authorization',
        'te',
        'trailer',
        'transfer-encoding',
        'upgrade',
        'host',
        'content-length',
    ];

    public function forward(Request $request, ?string $path = null): Response
    {
        $prefix = explode('/', ltrim($request->path(), '/'))[0] ?? '';
        if (! in_array($prefix, self::PREFIXES, true)) {
            abort(404);
        }

        $base = rtrim((string) config('services.dx_reporting_sidecar_url'), '/');
        if ($base === '') {
            return response('DX reporting sidecar is not configured', 503);
        }

        $target = $base.'/'.$prefix;
        if (is_string($path) && $path !== '') {
            $target .= '/'.ltrim($path, '/');
        }
        $query = $request->getQueryString();
        if (is_string($query) && $query !== '') {
            $target .= '?'.$query;
        }

        $headers = [];
        foreach ($request->headers->all() as $name => $values) {
            if (in_array(strtolower((string) $name), self::HOP_BY_HOP, true)) {
                continue;
            }
            $headers[$name] = is_array($values) ? implode(', ', $values) : (string) $values;
        }
        unset($headers['Content-Type'], $headers['content-type']);

        [$body, $contentType] = $this->resolveUpstreamBody($request);

        try {
            $upstream = Http::withHeaders($headers)
                ->timeout(120)
                ->withBody($body, $contentType)
                ->withOptions(['http_errors' => false, 'allow_redirects' => false])
                ->send($request->method(), $target);
        } catch (ConnectionException $exception) {
            return response('DX reporting sidecar unreachable', 502);
        }

        $response = response($upstream->body(), $upstream->status());
        foreach ($upstream->headers() as $name => $values) {
            if (in_array(strtolower((string) $name), self::HOP_BY_HOP, true)) {
                continue;
            }
            $response->headers->set($name, $values);
        }

        return $response;
    }

    /**
     * PHP-FPM/nginx a veces dejan getContent() vacío y el POST en el request bag.
     * El sidecar ASP.NET exige application/x-www-form-urlencoded en Invoke (/DXXRD).
     *
     * @return array{0: string, 1: string}
     */
    private function resolveUpstreamBody(Request $request): array
    {
        $raw = $request->getContent();
        if (! is_string($raw)) {
            $raw = '';
        }

        if ($raw !== '') {
            return [$raw, $this->resolveContentType($request, $raw)];
        }

        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $params = $request->request->all();
            if ($params !== []) {
                return [http_build_query($params), 'application/x-www-form-urlencoded'];
            }
        }

        return ['', $this->resolveContentType($request, '')];
    }

    private function resolveContentType(Request $request, string $body): string
    {
        $header = $request->header('Content-Type');
        if (is_string($header) && $header !== '') {
            return $header;
        }

        if ($body !== '' && $this->looksLikeFormUrlEncoded($body)) {
            return 'application/x-www-form-urlencoded';
        }

        return 'application/octet-stream';
    }

    private function looksLikeFormUrlEncoded(string $body): bool
    {
        if (! str_contains($body, '=')) {
            return false;
        }

        return preg_match('/^[\x09\x0a\x0d\x20-\x7e]*$/', $body) === 1;
    }
}
