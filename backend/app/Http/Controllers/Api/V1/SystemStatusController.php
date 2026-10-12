<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\SystemStatus\GetSystemStatusService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;

class SystemStatusController extends Controller
{
    /** Estado del sistema (MONO). OpenAPI: App\OpenApi\OpenApiPathsSystem. */
    public function show(GetSystemStatusService $getSystemStatusService): JsonResponse
    {
        return ApiResponse::success($getSystemStatusService->execute());
    }
}
