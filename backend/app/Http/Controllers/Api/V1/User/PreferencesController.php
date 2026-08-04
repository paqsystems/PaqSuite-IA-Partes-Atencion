<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\I18n\LocaleNormalizer;
use PaqSuite\LaravelCore\Security\UserPreferencesRepository;

final class PreferencesController extends Controller
{
    public function __construct(
        private readonly UserPreferencesRepository $userPreferencesRepository,
        private readonly LocaleNormalizer $localeNormalizer
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $userId = (int) Auth::id();

        return ApiResponse::success($this->userPreferencesRepository->getForUser($userId));
    }

    public function update(Request $request): JsonResponse
    {
        $userId = (int) Auth::id();

        $partial = [];

        if ($request->has('locale')) {
            $raw = $request->input('locale');
            if (! $this->localeNormalizer->isValid($raw)) {
                return ApiResponse::error(
                    PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                    PaqSuiteEnvelopeCatalog::RESPUESTA_LOCALE_INVALID,
                    422
                );
            }
            $partial['locale'] = $this->localeNormalizer->normalize($raw);
        }

        if ($request->has('openInNewTab')) {
            $partial['openInNewTab'] = (bool) $request->input('openInNewTab');
        }

        if ($request->has('activeLlmCredentialId')) {
            $raw = $request->input('activeLlmCredentialId');
            $partial['activeLlmCredentialId'] = $raw === null || $raw === ''
                ? null
                : (int) $raw;
        }

        if ($partial === []) {
            return ApiResponse::success($this->userPreferencesRepository->getForUser($userId));
        }

        $result = $this->userPreferencesRepository->patchForUser($userId, $partial);

        return ApiResponse::success($result);
    }
}
