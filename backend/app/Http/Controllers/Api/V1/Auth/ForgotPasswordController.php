<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

final class ForgotPasswordController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $email = strtolower(trim((string) $request->input('email', '')));

        if ($email === '') {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::VALIDATION_FAILED);
        }

        $user = User::findByUsuarioOrEmail($email);

        if ($user === null) {
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                'auth.emailNotFound',
                422
            );
        }

        $locale = $this->resolveLocale($request, $user);
        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        try {
            Notification::send($user, new ResetPasswordNotification($plainToken, $locale));
        } catch (TransportExceptionInterface) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::MAIL_SEND_FAILED);
        } catch (Throwable $e) {
            if ($this->isMailTransportFailure($e)) {
                return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::MAIL_SEND_FAILED);
            }

            throw $e;
        }

        return ApiResponse::success();
    }

    private function resolveLocale(Request $request, User $user): string
    {
        $bodyLocale = $request->input('locale');
        if (is_string($bodyLocale) && $bodyLocale !== '') {
            return $bodyLocale;
        }

        $acceptLanguage = $request->header('Accept-Language');
        if (is_string($acceptLanguage) && $acceptLanguage !== '') {
            $primary = trim(explode(',', $acceptLanguage)[0]);
            $primary = trim(explode(';', $primary)[0]);
            if ($primary !== '') {
                return $primary;
            }
        }

        if (is_string($user->locale) && $user->locale !== '') {
            return $user->locale;
        }

        return 'es';
    }

    private function isMailTransportFailure(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'smtp')
            || str_contains($message, 'mailer')
            || str_contains($message, 'unable to send')
            || str_contains($message, 'connection could not be established');
    }
}
