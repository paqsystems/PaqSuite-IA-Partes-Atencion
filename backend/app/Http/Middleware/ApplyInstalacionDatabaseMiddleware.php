<?php

namespace App\Http\Middleware;

use App\Tenancy\InstalacionConnectionConfigurator;
use Closure;
use Illuminate\Http\Request;
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
    public function __construct(
        private readonly InstalacionConnectionConfigurator $connectionConfigurator,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $instalacion = $request->attributes->get(ResolveInstalacionMiddleware::REQUEST_ATTRIBUTE);
        if (! $instalacion instanceof InstalacionRecord) {
            return $next($request);
        }

        if ($instalacion->isGatewayMode()) {
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::INFRA_UNEXPECTED,
                'tenant.gatewayNotSupported',
                503,
            );
        }

        $default = (string) config('database.default');
        $driver = (string) config("database.connections.{$default}.driver");
        if ($driver !== 'sqlsrv') {
            return $next($request);
        }

        if (! $this->connectionConfigurator->apply($instalacion)) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::INFRA_UNEXPECTED);
        }

        return $next($request);
    }
}
