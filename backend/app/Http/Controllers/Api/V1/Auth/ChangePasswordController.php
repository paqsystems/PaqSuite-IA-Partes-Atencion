<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                'auth.password.fieldsRequired',
                422
            );
        }

        if ($password !== $passwordConfirmation) {
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                'auth.password.mismatch',
                422
            );
        }

        if (!Hash::check($passwordActual, $user->password)) {
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

        $this->persistPasswordChange((int) $user->id, Hash::make($password));
        $user->refresh();

        return ApiResponse::success();
    }

    /**
     * Auth: no Eloquent save. En SQL Server el dateFormat Ymd del modelo User
     * deja el UPDATE de updated_at colgado al grabar.
     */
    private function persistPasswordChange(int $userId, string $passwordHash): void
    {
        if (DB::connection()->getDriverName() === 'sqlsrv') {
            DB::update(
                'UPDATE dbo.users SET password = ?, first_login = 0, updated_at = SYSUTCDATETIME() WHERE id = ?',
                [$passwordHash, $userId]
            );

            return;
        }

        DB::table('users')->where('id', $userId)->update([
            'password' => $passwordHash,
            'first_login' => false,
            'updated_at' => now(),
        ]);
    }
}
