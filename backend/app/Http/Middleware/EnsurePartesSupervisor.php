<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rutas ABM paquetes de horas (TR-013): solo supervisor Partes.
 */
final class EnsurePartesSupervisor
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var array<string, mixed>|null $partes */
        $partes = $request->attributes->get(EnsurePartesFunctionalProfile::REQUEST_ATTR);
        if (! is_array($partes) || ! (bool) ($partes['esSupervisor'] ?? false)) {
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN,
                'partes.paquete.soloSupervisor',
                403
            );
        }

        return $next($request);
    }
}
