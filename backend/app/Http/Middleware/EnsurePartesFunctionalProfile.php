<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Repositories\Sp\Partes\PartesIdentidadRepository;
use App\Services\Auth\PostLoginBusinessGateException;
use Closure;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use Symfony\Component\HttpFoundation\Response;

/**
 * Revalida identidad funcional Partes (TR-002). Alias: partes.profile
 */
final class EnsurePartesFunctionalProfile
{
    public const REQUEST_ATTR = 'partes.profile';

    public function __construct(private readonly PartesIdentidadRepository $partesIdentidadRepository)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::AUTH_UNAUTHENTICATED);
        }

        try {
            $resolved = $this->partesIdentidadRepository->resolveByUserId((int) $user->id);
            $codigo = $resolved['codigoResultado'];

            if ($codigo === 2) {
                throw new PostLoginBusinessGateException(
                    PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN,
                    'partes.auth.inconsistentProfile',
                    403
                );
            }

            if ($codigo !== 0 || $resolved['tipoFuncional'] === null) {
                throw new PostLoginBusinessGateException(
                    PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN,
                    'partes.auth.noFunctionalProfile',
                    403
                );
            }

            $partes = $this->partesIdentidadRepository->toPartesPayload($resolved);
            $request->attributes->set(self::REQUEST_ATTR, $partes);
        } catch (PostLoginBusinessGateException $e) {
            return ApiResponse::error($e->catalogError, $e->respuesta, $e->httpStatus);
        }

        return $next($request);
    }
}
