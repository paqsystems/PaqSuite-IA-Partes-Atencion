<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PaqSuite\LaravelCore\Auth\ParametroStore;
use PaqSuite\LaravelCore\Auth\PasswordPolicy;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;

final class ResetPasswordController extends Controller
{
    public function __construct(
        private readonly ParametroStore $parametroStore
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $token = trim((string) $request->input('token', ''));
        $password = (string) $request->input('password', '');
        $passwordConfirmation = (string) $request->input('passwordConfirmation', '');

        if ($token === '' || $password === '' || $passwordConfirmation === '') {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::VALIDATION_FAILED);
        }

        if ($password !== $passwordConfirmation) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::VALIDATION_FAILED);
        }

        $email = $this->resolveEmailForToken($token);
        if ($email === null) {
            return $this->resetTokenInvalid();
        }

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();
        if ($record === null || ! Hash::check($token, $record->token)) {
            return $this->resetTokenInvalid();
        }

        if ($this->isTokenExpired($record->created_at)) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return $this->resetTokenInvalid();
        }

        $user = User::query()->where('email', $email)->first();
        if ($user === null) {
            return $this->resetTokenInvalid();
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
        $user->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return ApiResponse::success();
    }

    private function resolveEmailForToken(string $plainToken): ?string
    {
        foreach (DB::table('password_reset_tokens')->get() as $record) {
            if (Hash::check($plainToken, $record->token)) {
                return $record->email;
            }
        }

        return null;
    }

    private function isTokenExpired(?string $createdAt): bool
    {
        if ($createdAt === null || $createdAt === '') {
            return true;
        }

        $ttlMin = max(1, (int) ($this->parametroStore->getInt('PasswordResetTtlMin', 10) ?? 10));

        return Carbon::parse($createdAt)->addMinutes($ttlMin)->isPast();
    }

    private function resetTokenInvalid(): JsonResponse
    {
        return ApiResponse::error(
            PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
            'auth.resetTokenInvalid',
            422
        );
    }
}
