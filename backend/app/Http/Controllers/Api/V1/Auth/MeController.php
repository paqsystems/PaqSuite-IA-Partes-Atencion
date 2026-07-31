<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsurePartesFunctionalProfile;
use App\Models\User;
use App\Services\Auth\UserEmpresasResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Auth\ParametroStore;
use PaqSuite\LaravelCore\Auth\SessionIdleMinutes;
use PaqSuite\LaravelCore\Auth\SessionPayloadBuilder;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;

final class MeController extends Controller
{
    public function __construct(
        private readonly UserEmpresasResolver $userEmpresasResolver,
        private readonly ParametroStore $parametroStore
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $empresas = $this->userEmpresasResolver->resolveForUser($user);
        $minutosWeb = (new SessionIdleMinutes($this->parametroStore))->resolve();

        /** @var array<string, mixed> $partes */
        $partes = $request->attributes->get(EnsurePartesFunctionalProfile::REQUEST_ATTR, []);

        $resultado = SessionPayloadBuilder::buildSessionResultado([
            'user' => [
                'id' => $user->id,
                'usuario' => (string) ($user->usuario ?? $user->email),
                'email' => $user->email,
                'locale' => $user->locale,
            ],
            'firstLogin' => (bool) $user->first_login,
            'minutosWeb' => $minutosWeb,
            'empresas' => $empresas,
        ]);

        $resultado['partes'] = $partes;

        return ApiResponse::success($resultado);
    }
}
