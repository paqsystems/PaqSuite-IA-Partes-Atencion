<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\PostLoginBusinessGate;
use App\Services\Auth\PostLoginBusinessGateException;
use App\Services\Auth\UserEmpresasResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PaqSuite\LaravelCore\Auth\ParametroStore;
use PaqSuite\LaravelCore\Auth\SessionIdleMinutes;
use PaqSuite\LaravelCore\Auth\SessionPayloadBuilder;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\I18n\LocaleNormalizer;
use PaqSuite\LaravelCore\Security\UserPreferencesRepository;

final class LoginController extends Controller
{
    public function __construct(
        private readonly PostLoginBusinessGate $postLoginBusinessGate,
        private readonly UserEmpresasResolver $userEmpresasResolver,
        private readonly ParametroStore $parametroStore,
        private readonly UserPreferencesRepository $userPreferencesRepository,
        private readonly LocaleNormalizer $localeNormalizer
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $usuario = trim((string) ($request->input('usuario') ?? $request->input('codigo') ?? ''));
        $password = (string) $request->input('password', '');

        if ($usuario === '' || $password === '') {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::AUTH_INVALID_CREDENTIALS);
        }

        $user = User::findByUsuarioOrEmail($usuario);

        if ($user === null || ! $user->isLoginAllowed() || ! Hash::check($password, $user->password)) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::AUTH_INVALID_CREDENTIALS);
        }

        try {
            $gateExtra = $this->postLoginBusinessGate->assertAllowed($user);
        } catch (PostLoginBusinessGateException $e) {
            return ApiResponse::error($e->catalogError, $e->respuesta, $e->httpStatus);
        }

        $localeRaw = $request->input('locale');
        if ($localeRaw !== null) {
            $normalizedLocale = $this->localeNormalizer->normalize($localeRaw);
            if ($normalizedLocale !== null && $normalizedLocale !== $user->locale) {
                $this->userPreferencesRepository->patchForUser((int) $user->id, ['locale' => $normalizedLocale]);
                $user->refresh();
            }
        }

        $token = $user->createToken('auth')->plainTextToken;
        $empresas = $this->userEmpresasResolver->resolveForUser($user);
        $minutosWeb = (new SessionIdleMinutes($this->parametroStore))->resolve();

        $resultado = SessionPayloadBuilder::buildSessionResultado([
            'token' => $token,
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

        if (isset($gateExtra['partes']) && is_array($gateExtra['partes'])) {
            $resultado['partes'] = $gateExtra['partes'];
        }

        return ApiResponse::success($resultado);
    }
}
