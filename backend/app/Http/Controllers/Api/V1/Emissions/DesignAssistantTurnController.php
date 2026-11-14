<?php

namespace App\Http\Controllers\Api\V1\Emissions;

use App\Http\Controllers\Api\V1\CapabilityEnvelopeController;
use App\Services\Emissions\DesignAssistant\DesignAssistantTurnService;
use App\Services\Emissions\DesignAssistant\DesignAssistantValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\Llm\LlmDomainException;
use PaqSuite\LaravelCore\Security\AccesoTotalChecker;
use PaqSuite\LaravelCore\SmartCapture\SmartCaptureDomainException;

final class DesignAssistantTurnController extends CapabilityEnvelopeController
{
    public function __construct(
        private readonly DesignAssistantTurnService $service,
        private readonly AccesoTotalChecker $accesoTotalChecker,
    ) {}

    public function __invoke(Request $request, string $processCode): JsonResponse
    {
        if (! (bool) config('paqsuite.emissions.enabled', true)) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_DISABLED);
        }

        $empresaId = (int) ($request->header(config('paqsuite.headers.company', 'X-Company-Id')) ?: 1);
        if (! $this->accesoTotalChecker->hasAccesoTotal($this->userId(), $empresaId)) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_DESIGN_FORBIDDEN);
        }

        $payload = $request->all();
        if (isset($payload['credentialId']) && is_numeric($payload['credentialId'])) {
            $payload['credentialId'] = (int) $payload['credentialId'];
        }

        try {
            $result = $this->service->handle($this->userId(), $processCode, $payload);
        } catch (DesignAssistantValidationException $exception) {
            return ApiResponse::error(422, $exception->respuestaKey, 422);
        } catch (SmartCaptureDomainException|LlmDomainException $exception) {
            return $this->renderDomainException($exception);
        } catch (\Throwable $exception) {
            Log::error('partes.emissionDesign.unhandled', [
                'exception' => $exception::class,
                'detail' => $exception->getMessage(),
            ]);

            return ApiResponse::error(500, 'partes.emissionDesign.turnError', 500);
        }

        return ApiResponse::success($result);
    }
}
