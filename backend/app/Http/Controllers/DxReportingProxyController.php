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

        try {
            $upstream = Http::withHeaders($headers)
                ->timeout(120)
                ->withBody($request->getContent(), (string) $request->header('Content-Type', 'application/octet-stream'))
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
}
