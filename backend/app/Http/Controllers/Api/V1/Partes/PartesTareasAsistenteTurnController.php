<?php

namespace App\Http\Controllers\Api\V1\Partes;

use App\Http\Controllers\Api\V1\CapabilityEnvelopeController;
use App\Services\Partes\SmartCapture\PartesTareaSmartCaptureTurnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;

final class PartesTareasAsistenteTurnController extends CapabilityEnvelopeController
{
    public function __construct(
        private readonly PartesTareaSmartCaptureTurnService $service,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();
        if (isset($payload['credentialId']) && is_numeric($payload['credentialId'])) {
            $payload['credentialId'] = (int) $payload['credentialId'];
        }

        try {
            $result = $this->service->handle($this->userId(), $payload);
        } catch (\Throwable $exception) {
            return $this->renderDomainException($exception);
        }

        return ApiResponse::success($result);
    }
}
