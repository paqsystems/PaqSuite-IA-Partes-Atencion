<?php

namespace App\Http\Controllers\Api\V1\ExcelImport;

use App\Http\Controllers\Api\V1\CapabilityEnvelopeController;
use App\Repositories\Sp\ExcelImport\SpExcelImportRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PaqSuite\LaravelCore\ExcelImport\Contracts\ExcelImportBinaryExporter;
use PaqSuite\LaravelCore\ExcelImport\ExcelImportCapabilityGuard;
use PaqSuite\LaravelCore\ExcelImport\ExcelImportException;
use PaqSuite\LaravelCore\ExcelImport\ExcelImportProcessOrchestrator;
use PaqSuite\LaravelCore\ExcelImport\ExcelImportValidationOrchestrator;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\Tenancy\MenuProcedimientoChecker;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExcelImportController extends CapabilityEnvelopeController
{
    public function __construct(
        private readonly SpExcelImportRepository $repository,
        private readonly ExcelImportCapabilityGuard $capabilityGuard,
        private readonly ExcelImportValidationOrchestrator $validationOrchestrator,
        private readonly ExcelImportProcessOrchestrator $processOrchestrator,
        private readonly ExcelImportBinaryExporter $binaryExporter,
        private readonly MenuProcedimientoChecker $menuChecker,
    ) {
    }

    public function template(Request $request, string $codigo): Response|JsonResponse|StreamedResponse
    {
        try {
            $this->capabilityGuard->ensureEnabled();
            $this->assertProcessAllowed($codigo);
        } catch (ExcelImportException $exception) {
            return $this->fromException($exception);
        }

        $process = $this->repository->findProcess($codigo);
        if ($process === null || ! $process->active) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EXCEL_IMPORT_PROCESS_NOT_FOUND);
        }

        $binary = $this->binaryExporter->template($process);

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$codigo.'-plantilla.xlsx"',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $this->capabilityGuard->ensureEnabled();
            $processCode = (string) $request->input('processCode', '');
            $this->assertProcessAllowed($processCode);

            if (! $request->hasFile('file')) {
                return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EXCEL_IMPORT_VALIDATION_FAILED);
            }

            $file = $request->file('file');
            $path = $file?->getRealPath();
            if ($path === false || $path === null) {
                throw ExcelImportException::fromCode(4606);
            }

            $mime = (string) ($file->getMimeType() ?? '');
            if (
                $mime === ''
                || $mime === 'application/octet-stream'
                || $mime === 'application/zip'
            ) {
                $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            }

            $batch = $this->validationOrchestrator->validate(
                $path,
                (string) ($file->getClientOriginalName() ?? 'upload.xlsx'),
                $mime,
                $processCode,
                $this->companyId($request),
                $this->userId(),
                $request->input('sheetName'),
            );
        } catch (ExcelImportException $exception) {
            return $this->fromException($exception);
        }

        return ApiResponse::success(
            $this->repository->batchRow($batch->batchId, $this->companyId($request)) ?? [],
            PaqSuiteEnvelopeCatalog::RESPUESTA_OK,
            201,
        );
    }

    public function show(Request $request, string $batchId): JsonResponse
    {
        try {
            $this->capabilityGuard->ensureEnabled();
        } catch (ExcelImportException $exception) {
            return $this->fromException($exception);
        }

        $batch = $this->repository->batchRow($batchId, $this->companyId($request));
        if ($batch === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EXCEL_IMPORT_BATCH_NOT_FOUND);
        }

        try {
            $this->assertProcessAllowed((string) $batch['processCode']);
        } catch (ExcelImportException $exception) {
            return $this->fromException($exception);
        }

        return ApiResponse::success($batch);
    }

    public function errors(Request $request, string $batchId): JsonResponse
    {
        try {
            $this->capabilityGuard->ensureEnabled();
        } catch (ExcelImportException $exception) {
            return $this->fromException($exception);
        }

        $batch = $this->repository->findBatch($batchId, $this->companyId($request));
        if ($batch === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EXCEL_IMPORT_BATCH_NOT_FOUND);
        }

        try {
            $this->assertProcessAllowed($batch->processCode);
        } catch (ExcelImportException $exception) {
            return $this->fromException($exception);
        }

        $page = max(1, (int) $request->query('page', 1));
        $pageSize = min(100, max(1, (int) $request->query('pageSize', 25)));
        $items = array_map(
            static fn ($error): array => [
                'rowNumber' => $error->rowNumber,
                'column' => $error->column,
                'messageKey' => $error->messageKey,
                'params' => $error->params,
            ],
            $this->repository->errors($batchId, $page, $pageSize),
        );

        return ApiResponse::success([
            'items' => $items,
            'total' => $this->repository->errorsCount($batchId),
            'page' => $page,
            'pageSize' => $pageSize,
        ]);
    }

    public function exportErrors(Request $request, string $batchId): Response|JsonResponse
    {
        try {
            $this->capabilityGuard->ensureEnabled();
        } catch (ExcelImportException $exception) {
            return $this->fromException($exception);
        }

        $batch = $this->repository->findBatch($batchId, $this->companyId($request));
        if ($batch === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EXCEL_IMPORT_BATCH_NOT_FOUND);
        }

        try {
            $this->assertProcessAllowed($batch->processCode);
        } catch (ExcelImportException $exception) {
            return $this->fromException($exception);
        }

        $allErrors = $this->repository->errors($batchId, 1, 10_000);
        $binary = $this->binaryExporter->errors($allErrors);

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="batch-'.$batchId.'-errors.xlsx"',
        ]);
    }

    public function process(Request $request, string $batchId): JsonResponse
    {
        try {
            $this->capabilityGuard->ensureEnabled();
            $batch = $this->repository->findBatch($batchId, $this->companyId($request));
            if ($batch === null) {
                throw ExcelImportException::fromCode(4602);
            }
            $this->assertProcessAllowed($batch->processCode);

            $outcome = $this->processOrchestrator->process(
                $batchId,
                $this->companyId($request),
                $this->userId(),
            );
        } catch (ExcelImportException $exception) {
            return $this->fromException($exception);
        }

        $item = $this->repository->batchRow($batchId, $this->companyId($request)) ?? [];
        $httpStatus = $outcome->status === 'queued' ? 202 : 200;

        return ApiResponse::success($item, PaqSuiteEnvelopeCatalog::RESPUESTA_OK, $httpStatus);
    }

    private function assertProcessAllowed(string $processCode): void
    {
        $process = $this->repository->findProcess($processCode);
        if ($process === null || ! $process->active) {
            throw ExcelImportException::fromCode(4609);
        }
        $menuCode = $process->menuProcessCode;
        if (
            ! $this->menuChecker->existsInMenu($menuCode)
            || ! $this->menuChecker->userMayExecute($menuCode)
        ) {
            throw ExcelImportException::fromCode(4603);
        }
    }

    private function companyId(Request $request): ?int
    {
        $raw = $request->header(config('paqsuite.headers.company', 'X-Company-Id'));
        if ($raw === null || $raw === '') {
            return 1;
        }

        return (int) $raw;
    }

    private function fromException(ExcelImportException $exception): JsonResponse
    {
        return ApiResponse::error(
            $exception->errorCode,
            $exception->messageKey,
            $exception->httpStatus,
        );
    }
}
