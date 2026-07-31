<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PaqSuite\LaravelCore\Auth\ParametroStore;
use PaqSuite\LaravelCore\Auth\PasswordPolicy;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;

final class ChangePasswordController extends Controller
{
    public function __construct(
        private readonly ParametroStore $parametroStore
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        if ($user === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::AUTH_UNAUTHENTICATED);
        }

        $passwordActual = (string) $request->input('passwordActual', '');
        $password = (string) $request->input('password', '');
        $passwordConfirmation = (string) $request->input('passwordConfirmation', '');

        if ($passwordActual === '' || $password === '' || $passwordConfirmation === '') {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::VALIDATION_FAILED);
        }

        if ($password !== $passwordConfirmation) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::VALIDATION_FAILED);
        }

        if (! Hash::check($passwordActual, $user->password)) {
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                'auth.password.currentInvalid',
                422
            );
        }

        if (Hash::check($password, $user->password)) {
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                'auth.password.sameAsCurrent',
                422
            );
        }

        $policyResult = (new PasswordPolicy($this->parametroStore))->evaluate($password);
        if ($policyResult['ok'] !== true) {
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                $policyResult['errorKey'],
                422
            );
        }

        $user->password = Hash::make($password);
        $user->first_login = false;
        $user->save();

        return ApiResponse::success();
    }
}
