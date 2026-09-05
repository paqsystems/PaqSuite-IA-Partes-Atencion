<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Http\Middleware\ResolveInstalacionMiddleware;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\Tenancy\InstalacionRecord;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

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
            "database.connections.{$default}.password" => $this->resolveConnectionPassword(
                $instalacion->password,
                (string) config("database.connections.{$default}.password", ''),
            ),
        ]);

        DB::purge($default);

        return $next($request);
    }

    /**
     * EMPRESAS_CONEXION.password suele ir cifrado con APP_KEY (Laravel Crypt).
     * ODBC rechaza el payload en PWD; si no se puede descifrar, se usa DB_PASSWORD.
     */
    private function resolveConnectionPassword(?string $instalacionPassword, string $configuredPassword): string
    {
        if ($instalacionPassword === null || $instalacionPassword === '') {
            return $configuredPassword;
        }

        if (!$this->looksLikeLaravelCryptPayload($instalacionPassword)) {
            return $instalacionPassword;
        }

        try {
            return Crypt::decryptString($instalacionPassword);
        } catch (Throwable) {
            try {
                $decrypted = Crypt::decrypt($instalacionPassword);

                return is_string($decrypted) && $decrypted !== '' ? $decrypted : $configuredPassword;
            } catch (Throwable) {
                return $configuredPassword !== '' ? $configuredPassword : $instalacionPassword;
            }
        }
    }

    private function looksLikeLaravelCryptPayload(string $value): bool
    {
        $decoded = base64_decode($value, true);
        if (!is_string($decoded) || $decoded === '') {
            return false;
        }

        $json = json_decode($decoded, true);

        return is_array($json) && isset($json['iv'], $json['value']);
    }
}
