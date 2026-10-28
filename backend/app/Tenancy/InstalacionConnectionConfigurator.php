<?php

namespace App\Tenancy;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Tenancy\InstalacionRecord;
use Throwable;

/**
 * Aplica host/database/credenciales de EMPRESAS_CONEXION sobre la conexión sqlsrv default.
 */
final class InstalacionConnectionConfigurator
{
    public function apply(InstalacionRecord $instalacion): bool
    {
        $default = (string) config('database.default');
        $driver = (string) config("database.connections.{$default}.driver");
        if ($driver !== 'sqlsrv') {
            return false;
        }

        $host = $instalacion->host;
        $databaseName = $instalacion->databaseName;
        if ($host === null || $host === '' || $databaseName === null || $databaseName === '') {
            return false;
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

        return true;
    }

    /**
     * EMPRESAS_CONEXION.password suele ir cifrado con APP_KEY (Laravel Crypt).
     */
    private function resolveConnectionPassword(?string $instalacionPassword, string $configuredPassword): string
    {
        if ($instalacionPassword === null || $instalacionPassword === '') {
            return $configuredPassword;
        }

        if (! $this->looksLikeLaravelCryptPayload($instalacionPassword)) {
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
        if (! is_string($decoded) || $decoded === '') {
            return false;
        }

        $json = json_decode($decoded, true);

        return is_array($json) && isset($json['iv'], $json['value']);
    }
}
