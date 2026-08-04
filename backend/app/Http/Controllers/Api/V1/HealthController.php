<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Support\PaqSuiteConfig;

final class HealthController extends Controller
{
    public function __invoke()
    {
        return ApiResponse::success([
            'serviceName' => 'paqsuite-partes-backend',
            'status' => 'up',
            'tenancy' => PaqSuiteConfig::tenancy(),
            'db' => PaqSuiteConfig::db(),
        ]);
    }
}
