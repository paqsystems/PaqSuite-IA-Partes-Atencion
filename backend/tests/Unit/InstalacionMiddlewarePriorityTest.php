<?php

namespace Tests\Unit;

use App\Http\Kernel;
use App\Http\Middleware\ApplyInstalacionDatabaseMiddleware;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Routing\Router;
use PaqSuite\LaravelCore\Http\Middleware\ResolveInstalacionMiddleware;
use Tests\TestCase;

class InstalacionMiddlewarePriorityTest extends TestCase
{
    public function test_instalacion_middleware_runs_before_authenticate(): void
    {
        /** @var Kernel $kernel */
        $kernel = app(Kernel::class);
        $priority = $kernel->getMiddlewarePriority();

        $resolveIndex = array_search(ResolveInstalacionMiddleware::class, $priority, true);
        $applyIndex = array_search(ApplyInstalacionDatabaseMiddleware::class, $priority, true);
        $authIndex = array_search(AuthenticatesRequests::class, $priority, true);

        $this->assertNotFalse($resolveIndex);
        $this->assertNotFalse($applyIndex);
        $this->assertNotFalse($authIndex);
        $this->assertLessThan($applyIndex, $resolveIndex);
        $this->assertLessThan($authIndex, $applyIndex);
    }

    public function test_host_conserva_el_alias_de_aplicacion_de_instalacion(): void
    {
        /** @var Router $router */
        $router = app('router');
        $middleware = $router->getMiddleware();

        $this->assertSame(
            ApplyInstalacionDatabaseMiddleware::class,
            $middleware['paqsuite.instalacion.db'] ?? null,
        );
        $this->assertSame(
            ApplyInstalacionDatabaseMiddleware::class,
            config('paqsuite.instalacion.applyDatabaseMiddleware'),
        );
    }
}
