<?php

namespace App\OpenApi;

/**
 * Health y system status (tag FrameWork).
 *
 * @OA\Get(
 *   path="/api/v1/health",
 *   operationId="health",
 *   tags={"FrameWork"},
 *   summary="Health check (sin auth)",
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
 * )
 *
 * @OA\Get(
 *   path="/api/v1/system/status",
 *   operationId="systemStatus",
 *   tags={"FrameWork"},
 *   summary="Estado instalación / entorno",
 *   security={{"sanctum":{}},{"tenant":{}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
 *   @OA\Response(response=401, description="No autenticado")
 * )
 */
abstract class OpenApiPathsSystem
{
}
