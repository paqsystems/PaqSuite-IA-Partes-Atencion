<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use Symfony\Component\HttpFoundation\Response;

final class EnsureFirstLoginCompletedMiddleware
{
    /** @var list<string> */
    private const ALLOWLIST_PREFIXES = [
        '/api/v1/auth/change-password',
        '/api/v1/auth/logout',
        '/api/v1/auth/me',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User || ! $user->first_login) {
            return $next($request);
        }

        if ($this->isAllowlisted($request)) {
            return $next($request);
        }

        return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN);
    }

    private function isAllowlisted(Request $request): bool
    {
        $path = '/'.ltrim($request->path(), '/');

        foreach (self::ALLOWLIST_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return true;
            }
        }

        return false;
    }
}
