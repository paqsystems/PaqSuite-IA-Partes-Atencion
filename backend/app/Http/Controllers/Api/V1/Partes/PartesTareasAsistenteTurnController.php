<?php

namespace App\Http\Controllers\Api\V1\Partes;

use App\Http\Controllers\Api\V1\CapabilityEnvelopeController;
use App\Services\Partes\SmartCapture\PartesTareaSmartCaptureTurnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Llm\LlmDomainException;
use PaqSuite\LaravelCore\SmartCapture\SmartCaptureDomainException;

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
        } catch (SmartCaptureDomainException|LlmDomainException $exception) {
            return $this->renderDomainException($exception);
        } catch (\Throwable $exception) {
            Log::error('partes.smartCapture.unhandled', [
                'exception' => $exception::class,
                'detail' => $exception->getMessage(),
            ]);

            return ApiResponse::error(500, 'partes.smartCapture.turnError', 500);
        }

        return ApiResponse::success($result);
    }
}
