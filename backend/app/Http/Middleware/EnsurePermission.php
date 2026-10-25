<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissionSlugs): Response
    {
        $user = $request->user();
        if (! $user) {
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::AUTH_UNAUTHENTICATED,
                'auth.unauthenticated',
                401
            );
        }

        $granted = $user->permission_slugs ?? [];
        if (! is_array($granted)) {
            $granted = [];
        }

        foreach ($permissionSlugs as $required) {
            if (! $this->userHasPermission($granted, $required)) {
                return ApiResponse::error(
                    PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN,
                    'auth.forbidden',
                    403
                );
            }
        }

        return $next($request);
    }

    /**
     * @param  list<string>  $granted
     */
    private function userHasPermission(array $granted, string $required): bool
    {
        if (in_array('*', $granted, true)) {
            return true;
        }

        return in_array($required, $granted, true);
    }
}
