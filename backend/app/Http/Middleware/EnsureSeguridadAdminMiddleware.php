<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate ABM Seguridad GEN-06 (AccesoTotal empresa activa). Ver AuthServiceProvider::boot().
 */
final class EnsureSeguridadAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Gate::allows('seguridadAdmin')) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN);
        }

        return $next($request);
    }
}
