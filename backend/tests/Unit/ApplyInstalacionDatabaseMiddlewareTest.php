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
    }
}
