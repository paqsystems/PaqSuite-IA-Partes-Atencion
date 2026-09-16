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

        $locale = $this->resolveLocale($request);
        $cliente = $this->resolveCliente($request);
        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        try {
            Notification::send($user, new ResetPasswordNotification($plainToken, $locale, $cliente));
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

    private function resolveLocale(Request $request): string
    {
        $candidates = [];
        $bodyLocale = $request->input('locale');
        if (is_string($bodyLocale) && $bodyLocale !== '') {
            $candidates[] = $bodyLocale;
        }

        $acceptLanguage = $request->header('Accept-Language');
        if (is_string($acceptLanguage) && $acceptLanguage !== '') {
            $candidates[] = $acceptLanguage;
        }

        foreach ($candidates as $raw) {
            $normalized = $this->normalizeSupportedLocale($raw);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        return 'es';
    }

    private function resolveCliente(Request $request): ?string
    {
        $headerName = (string) config('paqsuite.headers.cliente', 'X-Paq-Cliente');
        $cliente = strtoupper(trim((string) $request->header($headerName, '')));

        return $cliente === '' ? null : $cliente;
    }

    private function normalizeSupportedLocale(string $raw): ?string
    {
        $supported = config('paqsuite.supported_locales', ['es', 'en', 'pt', 'fr', 'it']);
        if (! is_array($supported)) {
            $supported = ['es', 'en', 'pt', 'fr', 'it'];
        }

        $primary = strtolower(trim(explode(',', $raw)[0]));
        $primary = trim(explode(';', $primary)[0]);
        if ($primary === '') {
            return null;
        }

        if (in_array($primary, $supported, true)) {
            return $primary;
        }

        $short = substr($primary, 0, 2);
        if (in_array($short, $supported, true)) {
            return $short;
        }

        return null;
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
