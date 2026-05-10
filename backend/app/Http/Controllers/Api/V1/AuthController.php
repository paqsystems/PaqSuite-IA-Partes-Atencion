<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ApiEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login con email/password; devuelve token Bearer Sanctum.
     *
     * @OA\Post(
     *   path="/api/v1/auth/login",
     *   tags={"Auth"},
     *   summary="Obtener token API",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", example="dev@example.com"),
     *       @OA\Property(property="password", type="string", format="password")
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=422, description="Validación")
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        /** @var User|null $user */
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $token = $user->createToken('spa')->plainTextToken;

        return ApiEnvelope::ok([
            'tokenType' => 'Bearer',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/logout",
     *   tags={"Auth"},
     *   summary="Revocar token actual",
     *   security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiEnvelope::ok(['loggedOut' => true]);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/auth/me",
     *   tags={"Auth"},
     *   summary="Usuario autenticado",
     *   security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return ApiEnvelope::fail(['codigo' => 'unauthenticated', 'mensaje' => 'No autenticado'], 401);
        }

        return ApiEnvelope::ok([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'permissionSlugs' => $user->permission_slugs ?? [],
        ]);
    }
}
