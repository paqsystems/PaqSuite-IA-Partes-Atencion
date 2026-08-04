<?php

namespace App\Http\Controllers\Api\V1\Llm;

use App\Http\Controllers\Api\V1\CapabilityEnvelopeController;
use App\Repositories\Sp\SpLlmCredentialRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\Llm\LlmCredentialService;
use PaqSuite\LaravelCore\Llm\LlmProviderCatalog;

final class LlmCredentialsController extends CapabilityEnvelopeController
{
    public function __construct(
        private readonly LlmCredentialService $service,
        private readonly SpLlmCredentialRepository $repository,
    ) {
    }

    public function index(): JsonResponse
    {
        return ApiResponse::success([
            'items' => $this->service->listForUser($this->userId()),
            'activeLlmCredentialId' => $this->repository->getActiveCredentialId($this->userId()),
            'providers' => LlmProviderCatalog::supported(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $item = $this->service->create($this->userId(), $request->all());
        } catch (\Throwable $exception) {
            return $this->renderDomainException($exception);
        }

        return ApiResponse::success(['item' => $item], PaqSuiteEnvelopeCatalog::RESPUESTA_OK, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $item = $this->service->patch($this->userId(), $id, $request->all());
        } catch (\Throwable $exception) {
            return $this->renderDomainException($exception);
        }

        return ApiResponse::success(['item' => $item]);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($this->userId(), $id);
        } catch (\Throwable $exception) {
            return $this->renderDomainException($exception);
        }

        return ApiResponse::success(['deleted' => true]);
    }

    public function showActive(): JsonResponse
    {
        return ApiResponse::success([
            'activeLlmCredentialId' => $this->repository->getActiveCredentialId($this->userId()),
        ]);
    }

    public function setActive(Request $request): JsonResponse
    {
        if (! $request->has('activeLlmCredentialId')) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::LLM_VALIDATION_FAILED,
                ['errors' => ['activeLlmCredentialId' => ['required']]],
            );
        }

        $raw = $request->input('activeLlmCredentialId');
        if ($raw !== null && ! is_numeric($raw)) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::LLM_VALIDATION_FAILED);
        }

        $normalized = $this->service->setActiveCredentialId(
            $this->userId(),
            $raw === null || $raw === '' ? null : (int) $raw,
        );

        return ApiResponse::success(['activeLlmCredentialId' => $normalized]);
    }
}
