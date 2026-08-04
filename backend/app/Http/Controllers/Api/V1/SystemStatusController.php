<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\SystemStatus\GetSystemStatusService;
use App\Http\Controllers\Controller;
use App\Support\ApiEnvelope;
use Illuminate\Http\JsonResponse;

class SystemStatusController extends Controller
{
    /** Estado del sistema (MONO). OpenAPI: App\OpenApi\OpenApiPathsSystem. */
    public function show(GetSystemStatusService $getSystemStatusService): JsonResponse
    {
        return ApiEnvelope::ok($getSystemStatusService->execute());
    }
}
