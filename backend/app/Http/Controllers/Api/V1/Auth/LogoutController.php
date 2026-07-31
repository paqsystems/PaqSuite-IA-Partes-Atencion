<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;

final class LogoutController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user !== null) {
            $currentToken = $user->currentAccessToken();
            if ($currentToken !== null) {
                $currentToken->delete();
            } else {
                $plainToken = $request->bearerToken();
                if (is_string($plainToken) && $plainToken !== '') {
                    PersonalAccessToken::findToken($plainToken)?->delete();
                }
            }
        }

        return ApiResponse::success();
    }
}
