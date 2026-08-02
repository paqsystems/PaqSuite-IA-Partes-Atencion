<?php

namespace App\Http\Middleware;

use App\Http\Middleware\EnsurePartesFunctionalProfile;
use Closure;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deniega perfil cliente en rutas de maestros Partes (TR-003).
 */
final class EnsurePartesNotCliente
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var array<string, mixed>|null $partes */
        $partes = $request->attributes->get(EnsurePartesFunctionalProfile::REQUEST_ATTR);
        if (! is_array($partes) || ($partes['tipoFuncional'] ?? null) === 'cliente') {
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN,
                'partes.maestros.forbidden',
                403
            );
        }

        return $next($request);
    }
}
