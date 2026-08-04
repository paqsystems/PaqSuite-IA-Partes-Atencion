<?php

namespace App\OpenApi;

/**
 * Auth — rutas reales en `routes/api.php` (controllers invokable Auth/*).
 *
 * @OA\Post(
 *   path="/api/v1/auth/login",
 *   operationId="authLogin",
 *   tags={"Auth"},
 *   summary="Obtener token API",
 *   security={{"tenant":{}}},
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       required={"password"},
 *       @OA\Property(property="usuario", type="string", example="admin", description="Usuario o email"),
 *       @OA\Property(property="codigo", type="string", example="admin", description="Alias de usuario"),
 *       @OA\Property(property="password", type="string", format="password"),
 *       @OA\Property(property="locale", type="string", example="es", nullable=true)
 *     )
 *   ),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *   @OA\Response(response=401, description="Credenciales inválidas")
 * )
 *
 * @OA\Post(
 *   path="/api/v1/auth/forgot-password",
 *   operationId="authForgotPassword",
 *   tags={"Auth"},
 *   summary="Solicitar recuperación de contraseña",
 *   security={{"tenant":{}}},
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       @OA\Property(property="email", type="string", format="email"),
 *       @OA\Property(property="usuario", type="string")
 *     )
 *   ),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Post(
 *   path="/api/v1/auth/reset-password",
 *   operationId="authResetPassword",
 *   tags={"Auth"},
 *   summary="Restablecer contraseña con token",
 *   security={{"tenant":{}}},
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       required={"token","password","password_confirmation"},
 *       @OA\Property(property="token", type="string"),
 *       @OA\Property(property="email", type="string", format="email"),
 *       @OA\Property(property="password", type="string", format="password"),
 *       @OA\Property(property="password_confirmation", type="string", format="password")
 *     )
 *   ),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Post(
 *   path="/api/v1/auth/logout",
 *   operationId="authLogout",
 *   tags={"Auth"},
 *   summary="Revocar token actual",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *   @OA\Response(response=401, description="No autenticado")
 * )
 *
 * @OA\Get(
 *   path="/api/v1/auth/me",
 *   operationId="authMe",
 *   tags={"Auth"},
 *   summary="Contexto de sesión / usuario autenticado",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *   @OA\Response(response=401, description="No autenticado")
 * )
 *
 * @OA\Post(
 *   path="/api/v1/auth/change-password",
 *   operationId="authChangePassword",
 *   tags={"Auth"},
 *   summary="Cambiar contraseña (sesión autenticada)",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       required={"current_password","password","password_confirmation"},
 *       @OA\Property(property="current_password", type="string", format="password"),
 *       @OA\Property(property="password", type="string", format="password"),
 *       @OA\Property(property="password_confirmation", type="string", format="password")
 *     )
 *   ),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *   @OA\Response(response=401, description="No autenticado")
 * )
 */
abstract class OpenApiPathsAuth
{
}
