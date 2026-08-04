<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use PaqSuite\LaravelCore\ChatAssistant\ChatAssistantDomainException;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Llm\LlmDomainException;
use PaqSuite\LaravelCore\SmartCapture\SmartCaptureDomainException;

/**
 * Base thin para capacidades GEN-16/21/03 en el host Partes.
 */
abstract class CapabilityEnvelopeController extends Controller
{
    protected function userId(): int
    {
        return (int) Auth::id();
    }

    protected function renderDomainException(\Throwable $exception): JsonResponse
    {
        return match (true) {
            $exception instanceof LlmDomainException => ApiResponse::error(
                $exception->errorCode,
                $exception->respuesta,
                $exception->httpStatus,
            ),
            $exception instanceof ChatAssistantDomainException => ApiResponse::error(
                $exception->errorCode,
                $exception->respuesta,
                $exception->httpStatus,
                $exception->resultado,
            ),
            $exception instanceof SmartCaptureDomainException => ApiResponse::error(
                $exception->errorCode,
                $exception->respuesta,
                $exception->httpStatus,
                $exception->resultado !== []
                    ? $exception->resultado
                    : (
                        $exception->errorCode === 4201
                            ? ['configurationRequired' => true]
                            : []
                    ),
            ),
            default => throw $exception,
        };
    }
}
