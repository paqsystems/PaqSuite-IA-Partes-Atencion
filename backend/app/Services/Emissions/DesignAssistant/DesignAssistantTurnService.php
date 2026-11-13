<?php

namespace App\Services\Emissions\DesignAssistant;

use Illuminate\Support\Facades\Log;
use PaqSuite\LaravelCore\Llm\LlmCredentialResolver;
use PaqSuite\LaravelCore\Llm\LlmDomainException;
use PaqSuite\LaravelCore\SmartCapture\SmartCaptureDomainException;
use PaqSuite\LaravelCore\SmartCapture\SmartCaptureGuardOptions;
use PaqSuite\LaravelCore\SmartCapture\SmartCaptureTurnGuard;

/**
 * Turno Smart Capture de diseño de layout (TR-015-update-b).
 */
final class DesignAssistantTurnService
{
    public function __construct(
        private readonly SmartCaptureTurnGuard $guard,
        private readonly LlmCredentialResolver $credentialResolver,
        private readonly DesignLayoutActionPlanner $planner,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     * @return array{
     *   replyText: string,
     *   actions: list<array{action: string, payload: array<string, mixed>, resultado: string}>,
     *   pendingChoice: null,
     *   configurationRequired: bool
     * }
     */
    public function handle(int $userId, string $processCode, array $body): array
    {
        if ($processCode !== DesignLayoutWhitelist::PROCESS_CODE) {
            throw new DesignAssistantValidationException('partes.emissionDesign.processNotSupported');
        }

        $reportUrl = trim((string) ($body['reportUrl'] ?? ''));
        if ($reportUrl === '') {
            throw new DesignAssistantValidationException('partes.emissionDesign.reportUrlRequired');
        }

        if (isset($body['credentialId']) && is_numeric($body['credentialId'])) {
            $body['credentialId'] = (int) $body['credentialId'];
        }

        $credential = null;
        $credentialId = $body['credentialId'] ?? null;
        if (is_int($credentialId) && $credentialId > 0) {
            try {
                $credential = $this->credentialResolver->resolveSecret($credentialId, $userId);
            } catch (LlmDomainException) {
                $credential = null;
            }
        }

        $options = new SmartCaptureGuardOptions(
            supportsVision: false,
            hasValidLlmCredential: $credential !== null,
            companyId: null,
            assertDraftBelongsToCompany: null,
            companyRequired: false,
            authenticated: true,
        );

        try {
            $this->guard->validateGen($body, $options);
        } catch (SmartCaptureDomainException $exception) {
            if ($credential === null) {
                return [
                    'replyText' => 'partes.emissionDesign.configurationRequired',
                    'actions' => [],
                    'pendingChoice' => null,
                    'configurationRequired' => true,
                ];
            }
            throw $exception;
        }

        if ($credential === null) {
            return [
                'replyText' => 'partes.emissionDesign.configurationRequired',
                'actions' => [],
                'pendingChoice' => null,
                'configurationRequired' => true,
            ];
        }

        $message = trim((string) ($body['message'] ?? ''));
        if ($message === '') {
            throw new DesignAssistantValidationException('partes.emissionDesign.messageRequired');
        }

        $llmTimeout = max(5, (int) config('paqsuite.chatAssistant.llmTimeoutSeconds', 60));
        if (function_exists('set_time_limit')) {
            @set_time_limit($llmTimeout + 20);
        }

        try {
            $planned = $this->planner->plan($message, $credential);
        } catch (\Throwable $exception) {
            Log::warning('partes.emissionDesign.planFailed', [
                'exception' => $exception::class,
                'detail' => $exception->getMessage(),
            ]);

            return [
                'replyText' => 'partes.emissionDesign.turnError',
                'actions' => [],
                'pendingChoice' => null,
                'configurationRequired' => false,
            ];
        }

        return [
            'replyText' => $planned['replyText'],
            'actions' => $planned['actions'],
            'pendingChoice' => null,
            'configurationRequired' => false,
        ];
    }
}
