<?php

namespace App\OpenApi;

/**
 * User shell: menú, preferencias, empresas.
 *
 * @OA\Get(
 *   path="/api/v1/user/menu",
 *   operationId="userMenu",
 *   tags={"Admin"},
 *   summary="Árbol de menú del usuario",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/user/preferences",
 *   operationId="userPreferencesShow",
 *   tags={"Admin"},
 *   summary="Preferencias del usuario",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Patch(
 *   path="/api/v1/user/preferences",
 *   operationId="userPreferencesUpdate",
 *   tags={"Admin"},
 *   summary="Actualizar preferencias (locale, theme, openInNewTab)",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\RequestBody(
 *     @OA\JsonContent(
 *       @OA\Property(property="locale", type="string", example="es"),
 *       @OA\Property(property="theme", type="string"),
 *       @OA\Property(property="openInNewTab", type="boolean")
 *     )
 *   ),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/empresas",
 *   operationId="userEmpresas",
 *   tags={"Admin"},
 *   summary="Empresas accesibles del usuario",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 */
abstract class OpenApiPathsUser
{
}
