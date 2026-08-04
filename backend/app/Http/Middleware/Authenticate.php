<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * API (/api/*) nunca redirige a ruta nombrada `login` (no existe en SPA).
     * Sin sesión → 401 JSON vía parent::unauthenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return null;
        }

        return '/login';
    }
}
