<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Http\Middleware\ResolveInstalacionMiddleware;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\Tenancy\InstalacionRecord;
use Symfony\Component\HttpFoundation\Response;

/**
 * Opción B: tras validar X-Paq-Cliente, la conexión default sqlsrv
 * pasa a host/database de EMPRESAS_CONEXION (multidominio MONO).
 */
final class ApplyInstalacionDatabaseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $instalacion = $request->attributes->get(ResolveInstalacionMiddleware::REQUEST_ATTRIBUTE);
        if (! $instalacion instanceof InstalacionRecord) {
            return $next($request);
        }

        if ($instalacion->isGatewayMode()) {
            return $next($request);
        }

        $default = (string) config('database.default');
        $driver = (string) config("database.connections.{$default}.driver");
        if ($driver !== 'sqlsrv') {
            return $next($request);
        }

        $host = $instalacion->host;
        $databaseName = $instalacion->databaseName;
        if ($host === null || $host === '' || $databaseName === null || $databaseName === '') {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::INFRA_UNEXPECTED);
        }

        config([
            "database.connections.{$default}.host" => $host,
            "database.connections.{$default}.port" => $instalacion->port ?? 1433,
            "database.connections.{$default}.database" => $databaseName,
            "database.connections.{$default}.username" => $instalacion->username
                ?? config("database.connections.{$default}.username"),
            "database.connections.{$default}.password" => $instalacion->password
                ?? config("database.connections.{$default}.password"),
        ]);

        DB::purge($default);

        return $next($request);
    }
}
