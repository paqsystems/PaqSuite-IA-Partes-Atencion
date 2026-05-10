<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissionSlugs): Response
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'error' => ['codigo' => 'unauthenticated', 'mensaje' => 'No autenticado'],
                'respuesta' => null,
                'resultado' => null,
            ], 401);
        }

        $granted = $user->permission_slugs ?? [];
        if (! is_array($granted)) {
            $granted = [];
        }

        foreach ($permissionSlugs as $required) {
            if (! $this->userHasPermission($granted, $required)) {
                return response()->json([
                    'error' => ['codigo' => 'forbidden', 'mensaje' => 'Permiso denegado'],
                    'respuesta' => null,
                    'resultado' => null,
                ], 403);
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
