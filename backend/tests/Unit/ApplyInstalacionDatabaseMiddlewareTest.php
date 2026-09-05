<?php

namespace Tests\Unit;

use App\Http\Middleware\ApplyInstalacionDatabaseMiddleware;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Http\Middleware\ResolveInstalacionMiddleware;
use PaqSuite\LaravelCore\Tenancy\InstalacionRecord;
use Tests\TestCase;

class ApplyInstalacionDatabaseMiddlewareTest extends TestCase
{
    public function test_no_altera_sqlite_de_phpunit(): void
    {
        $request = Request::create('/api/v1/auth/login', 'POST');
        $request->attributes->set(
            ResolveInstalacionMiddleware::REQUEST_ATTRIBUTE,
            new InstalacionRecord(
                cliente: 'DEMO',
                proyecto: 'partesatencion',
                habilitado: true,
                host: 'database-1.example.rds.amazonaws.com',
                port: 1433,
                databaseName: 'paqsystems_partesatencion_demo',
                username: 'admin',
                password: 'secret',
            )
        );

        $middleware = new ApplyInstalacionDatabaseMiddleware();
        $response = $middleware->handle($request, function () {
            return response('ok');
        });

        $this->assertSame('ok', $response->getContent());
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    public function test_pisa_sqlsrv_con_la_fila_de_instalacion(): void
    {
        config(['database.default' => 'sqlsrv']);
        config(['database.connections.sqlsrv.host' => 'old-host']);
        config(['database.connections.sqlsrv.database' => 'old-db']);

        $request = Request::create('/api/v1/auth/login', 'POST');
        $request->attributes->set(
            ResolveInstalacionMiddleware::REQUEST_ATTRIBUTE,
            new InstalacionRecord(
                cliente: 'PAQ',
                proyecto: 'partesatencion',
                habilitado: true,
                host: 'database-1.example.rds.amazonaws.com',
                port: 1433,
                databaseName: 'paqsystems_partesatencion_paq',
                username: 'admin',
                password: 'secret',
            )
        );

        $middleware = new ApplyInstalacionDatabaseMiddleware();
        $middleware->handle($request, function () {
            return response('ok');
        });

        $this->assertSame('database-1.example.rds.amazonaws.com', config('database.connections.sqlsrv.host'));
        $this->assertSame('paqsystems_partesatencion_paq', config('database.connections.sqlsrv.database'));
        $this->assertSame('admin', config('database.connections.sqlsrv.username'));
        $this->assertSame('secret', config('database.connections.sqlsrv.password'));
    }

    public function test_desencripta_password_laravel_crypt_en_sqlsrv(): void
    {
        config(['database.default' => 'sqlsrv']);
        config(['database.connections.sqlsrv.driver' => 'sqlsrv']);
        config(['database.connections.sqlsrv.password' => 'from-env']);

        $plain = 'sql-plain-password';
        $cipher = \Illuminate\Support\Facades\Crypt::encryptString($plain);

        $request = Request::create('/api/v1/auth/login', 'POST');
        $request->attributes->set(
            ResolveInstalacionMiddleware::REQUEST_ATTRIBUTE,
            new InstalacionRecord(
                cliente: 'DEMO',
                proyecto: 'partesatencion',
                habilitado: true,
                host: '192.168.41.2',
                port: 1433,
                databaseName: 'PAQSYSTEMS_PARTESATENCION_DEMO',
                username: 'Axoft',
                password: $cipher,
            )
        );

        $middleware = new ApplyInstalacionDatabaseMiddleware();
        $middleware->handle($request, function () {
            return response('ok');
        });

        $this->assertSame($plain, config('database.connections.sqlsrv.password'));
    }

    public function test_si_el_crypt_no_coincide_usa_db_password(): void
    {
        config(['database.default' => 'sqlsrv']);
        config(['database.connections.sqlsrv.driver' => 'sqlsrv']);
        config(['database.connections.sqlsrv.password' => 'from-env']);

        $foreignPayload = base64_encode(json_encode([
            'iv' => base64_encode(str_repeat('a', 16)),
            'value' => 'not-a-real-cipher',
            'mac' => str_repeat('b', 64),
        ], JSON_THROW_ON_ERROR));

        $request = Request::create('/api/v1/auth/login', 'POST');
        $request->attributes->set(
            ResolveInstalacionMiddleware::REQUEST_ATTRIBUTE,
            new InstalacionRecord(
                cliente: 'DEMO',
                proyecto: 'partesatencion',
                habilitado: true,
                host: '192.168.41.2',
                port: 1433,
                databaseName: 'PAQSYSTEMS_PARTESATENCION_DEMO',
                username: 'Axoft',
                password: $foreignPayload,
            )
        );

        $middleware = new ApplyInstalacionDatabaseMiddleware();
        $middleware->handle($request, function () {
            return response('ok');
        });

        $this->assertSame('from-env', config('database.connections.sqlsrv.password'));
    }
}
