<?php

namespace App\Http\Controllers\Api\V1\Emissions;

use App\Http\Controllers\Api\V1\CapabilityEnvelopeController;
use App\Repositories\Sp\Emissions\SpEmissionRepository;
use DateTimeInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionContext;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionRequest;
use PaqSuite\LaravelCore\Emissions\EmissionCapabilityGuard;
use PaqSuite\LaravelCore\Emissions\EmissionException;
use PaqSuite\LaravelCore\Emissions\EmissionOrchestrator;
use PaqSuite\LaravelCore\Emissions\EmissionPreviewService;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\Security\AccesoTotalChecker;
use PaqSuite\LaravelCore\Tenancy\MenuProcedimientoChecker;

final class EmissionsController extends CapabilityEnvelopeController
{
    private const MODE_CONSOLIDATED = 'consolidated';

    public function __construct(
        private readonly SpEmissionRepository $repository,
        private readonly EmissionOrchestrator $orchestrator,
        private readonly EmissionPreviewService $previewService,
        private readonly EmissionCapabilityGuard $capabilityGuard,
        private readonly MenuProcedimientoChecker $menuChecker,
        private readonly AccesoTotalChecker $accesoTotalChecker,
    ) {
    }

    public function processes(): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        return ApiResponse::success(['items' => $this->repository->listProcesses()]);
    }

    public function showProcess(string $processCode): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $process = $this->repository->findProcess($processCode);
        if ($process === null || ! $process->active) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_PROCESS_NOT_FOUND);
        }

        $forbidden = $this->forbiddenMenuResponse($process->menuProcessCode);
        if ($forbidden !== null) {
            return $forbidden;
        }

        return ApiResponse::success(['item' => $this->processPayload($process)]);
    }

    public function storeJob(Request $request): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $processCode = (string) $request->input('processCode', '');
        $process = $this->repository->findProcess($processCode);
        if ($process === null || ! $process->active) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_PROCESS_NOT_FOUND);
        }

        $forbidden = $this->forbiddenMenuResponse($process->menuProcessCode);
        if ($forbidden !== null) {
            return $forbidden;
        }

        $companyId = $this->companyId($request);
        $mailTo = $request->input('mailTo', []);
        if (! is_array($mailTo)) {
            $mailTo = [];
        }

        $previewSessionId = $request->input('previewSessionId');

        try {
            $job = $this->orchestrator->emit(new EmissionRequest(
                processCode: $processCode,
                channel: (string) $request->input('channel', 'pdf'),
                mode: (string) $request->input('mode', self::MODE_CONSOLIDATED),
                companyIds: [$companyId],
                userId: $this->userId(),
                companyId: $companyId,
                groupId: $request->input('groupId'),
                reportId: $request->input('reportId'),
                mailTemplateId: $request->input('mailTemplateId'),
                mailTo: array_values(array_filter(array_map('strval', $mailTo))),
                previewSessionId: is_string($previewSessionId) ? $previewSessionId : null,
                mobile: (bool) $request->boolean('mobile'),
            ));
        } catch (EmissionException $exception) {
            return $this->renderDomainException($exception);
        }

        $httpStatus = $job->status === 'queued' ? 202 : 200;

        return ApiResponse::success(
            ['item' => $this->repository->jobRow($job->jobId, $companyId)],
            PaqSuiteEnvelopeCatalog::RESPUESTA_OK,
            $httpStatus,
        );
    }

    public function showJob(Request $request, string $jobId): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $job = $this->repository->jobRow($jobId, $this->companyId($request));
        if ($job === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_JOB_NOT_FOUND);
        }

        return ApiResponse::success(['item' => $job]);
    }

    public function download(Request $request, string $jobId): JsonResponse|Response
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        try {
            $artifact = $this->orchestrator->download($jobId, $this->companyId($request));
        } catch (EmissionException $exception) {
            return $this->renderDomainException($exception);
        }

        return response($artifact->content, 200, [
            'Content-Type' => $artifact->mimeType,
            'Content-Disposition' => 'attachment; filename="'.$artifact->fileName.'"',
            'Content-Length' => (string) $artifact->sizeBytes(),
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $processCode = (string) $request->input('processCode', '');
        $process = $this->repository->findProcess($processCode);
        if ($process === null || ! $process->active) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_PROCESS_NOT_FOUND);
        }

        $forbidden = $this->forbiddenMenuResponse($process->menuProcessCode);
        if ($forbidden !== null) {
            return $forbidden;
        }

        $companyId = $this->companyId($request);

        try {
            $session = $this->previewService->preview(
                new EmissionContext(
                    $processCode,
                    [$companyId],
                    $this->userId(),
                    (string) $request->input('mode', self::MODE_CONSOLIDATED),
                    (string) $request->input('channel', 'pdf'),
                    $request->input('reportId'),
                    $request->input('mailTemplateId'),
                    null,
                    $request->input('groupId'),
                ),
                $companyId,
            );
        } catch (EmissionException $exception) {
            return $this->renderDomainException($exception);
        }

        return ApiResponse::success([
            'previewSessionId' => $session->previewSessionId,
            'expiresAt' => $session->expiresAt->format(DateTimeInterface::ATOM),
            'mimeType' => $session->artifact->mimeType,
            'contentBase64' => base64_encode($session->artifact->content),
        ]);
    }

    public function designReports(string $processCode): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $designForbidden = $this->designForbiddenResponse();
        if ($designForbidden !== null) {
            return $designForbidden;
        }

        $process = $this->repository->findProcess($processCode);
        if ($process === null || ! $process->active) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_PROCESS_NOT_FOUND);
        }

        return ApiResponse::success([
            'items' => $process->reports,
            'designer' => 'dx',
        ]);
    }

    public function designCreateReport(Request $request, string $processCode): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $designForbidden = $this->designForbiddenResponse();
        if ($designForbidden !== null) {
            return $designForbidden;
        }

        $process = $this->repository->findProcess($processCode);
        if ($process === null || ! $process->active) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_PROCESS_NOT_FOUND);
        }

        $name = trim((string) $request->input('name', ''));
        $code = trim((string) $request->input('code', ''));
        if ($name === '' || $code === '' || strlen($code) > 64 || strlen($name) > 200) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_VALIDATION_FAILED);
        }

        $layoutDefinition = $request->input('layoutDefinition');
        $layoutMime = $request->input('layoutMime');
        $item = $this->repository->upsertDesignReport(
            $processCode,
            $code,
            $name,
            is_string($layoutDefinition) ? $layoutDefinition : null,
            is_string($layoutMime) ? $layoutMime : null,
            (bool) $request->boolean('setPrincipal', false),
        );

        return ApiResponse::success([
            'item' => $item,
            'designer' => 'dx',
        ]);
    }

    public function designUpdateLayout(Request $request, int $reportId): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $designForbidden = $this->designForbiddenResponse();
        if ($designForbidden !== null) {
            return $designForbidden;
        }

        $layoutMime = $request->input('layoutMime');
        if (! is_string($layoutMime) || $layoutMime === '') {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_VALIDATION_FAILED);
        }

        $layoutDefinition = $request->input('layoutDefinition');
        $updated = $this->repository->updateDesignReportLayout(
            $reportId,
            $layoutMime,
            is_string($layoutDefinition) ? $layoutDefinition : null,
        );
        if (! $updated) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_VALIDATION_FAILED);
        }

        return ApiResponse::success([
            'item' => [
                'reportId' => $reportId,
                'layoutMime' => $layoutMime,
                'designer' => 'dx',
            ],
        ]);
    }

    public function designSetPrincipal(int $reportId): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $designForbidden = $this->designForbiddenResponse();
        if ($designForbidden !== null) {
            return $designForbidden;
        }

        if (! $this->repository->setDesignReportPrincipal($reportId)) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_VALIDATION_FAILED);
        }

        return ApiResponse::success([
            'item' => [
                'reportId' => $reportId,
                'isPrincipal' => true,
                'designer' => 'dx',
            ],
        ]);
    }

    private function disabledResponse(): ?JsonResponse
    {
        try {
            $this->capabilityGuard->ensureEnabled();
        } catch (EmissionException $exception) {
            return $this->renderDomainException($exception);
        }

        return null;
    }

    private function designForbiddenResponse(): ?JsonResponse
    {
        $empresaId = (int) (request()->header(config('paqsuite.headers.company', 'X-Company-Id')) ?: 1);
        if ($this->accesoTotalChecker->hasAccesoTotal($this->userId(), $empresaId)) {
            return null;
        }

        return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_DESIGN_FORBIDDEN);
    }

    private function forbiddenMenuResponse(string $menuProcessCode): ?JsonResponse
    {
        if (
            $this->menuChecker->existsInMenu($menuProcessCode)
            && $this->menuChecker->userMayExecute($menuProcessCode)
        ) {
            return null;
        }

        return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_FORBIDDEN);
    }

    private function companyId(Request $request): int
    {
        $raw = $request->header(config('paqsuite.headers.company', 'X-Company-Id'));
        if ($raw === null || $raw === '') {
            return 1;
        }

        return (int) $raw;
    }

    /**
     * @return array<string, mixed>
     */
    private function processPayload(object $process): array
    {
        return [
            'processCode' => $process->processCode,
            'menuProcessCode' => $process->menuProcessCode,
            'channels' => $process->channels,
            'allowsConsolidated' => $process->allowsConsolidated,
            'allowsSegmented' => $process->allowsSegmented,
            'modes' => [
                'consolidated' => $process->allowsConsolidated,
                'segmented' => $process->allowsSegmented,
            ],
            'requiresPreview' => $process->requiresPreview,
            'reports' => $process->reports,
            'mailTemplates' => $process->mailTemplates,
        ];
    }
}
