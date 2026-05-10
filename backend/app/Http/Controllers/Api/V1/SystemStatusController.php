<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\SystemStatus\GetSystemStatusService;
use App\Http\Controllers\Controller;
use App\Support\ApiEnvelope;
use Illuminate\Http\JsonResponse;

class SystemStatusController extends Controller
{
    /**
     * Estado del sistema (MONO: una sola instalación).
     *
     * @OA\Get(
     *   path="/api/v1/system/status",
     *   tags={"System"},
     *   summary="Estado instalación / entorno",
     *   security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permiso")
     * )
     */
    public function show(GetSystemStatusService $getSystemStatusService): JsonResponse
    {
        return ApiEnvelope::ok($getSystemStatusService->execute());
    }
}
