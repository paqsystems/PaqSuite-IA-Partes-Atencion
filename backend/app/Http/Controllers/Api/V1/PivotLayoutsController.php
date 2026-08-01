<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PqPivotsConfig;
use App\Models\PqPivotsConfigLastUsed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;

/**
 * GEN-12 pivot layouts MVP (adapter Eloquent). Producción: SP pq_sp_pivot_layout_*.
 * Sin dependencia de catálogo pq_pivots_consultas (consultaId libre del producto).
 */
final class PivotLayoutsController extends Controller
{
    private const STATE_JSON_MAX_BYTES = 512 * 1024;

    public function index(Request $request): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $validator = Validator::make($request->query(), [
            'consultaId' => ['required', 'string', 'max:128'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $userId = (int) Auth::id();
        $consultaId = (string) $request->query('consultaId');

        $items = PqPivotsConfig::query()
            ->where('consulta_id', $consultaId)
            ->orderBy('layout_name')
            ->get()
            ->map(fn (PqPivotsConfig $layout): array => [
                'id' => (int) $layout->id,
                'layoutName' => (string) $layout->layout_name,
                'isOwner' => (int) $layout->created_by_user_id === $userId,
                'updatedAt' => $layout->updated_at?->toIso8601String() ?? '',
            ])
            ->all();

        return ApiResponse::success(['items' => $items]);
    }

    public function active(Request $request): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $validator = Validator::make($request->query(), [
            'consultaId' => ['required', 'string', 'max:128'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $userId = (int) Auth::id();
        $consultaId = (string) $request->query('consultaId');

        $lastUsed = PqPivotsConfigLastUsed::query()
            ->with('layout')
            ->where('user_id', $userId)
            ->where('consulta_id', $consultaId)
            ->first();

        if ($lastUsed === null || $lastUsed->layout === null) {
            return ApiResponse::success([
                'layoutId' => null,
                'layoutName' => null,
                'stateJson' => null,
            ]);
        }

        $layout = $lastUsed->layout;

        return ApiResponse::success([
            'layoutId' => (int) $layout->id,
            'layoutName' => (string) $layout->layout_name,
            'stateJson' => (string) $layout->state_json,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $validator = Validator::make($request->all(), [
            'consultaId' => ['required', 'string', 'max:128'],
            'layoutName' => ['required', 'string', 'max:128'],
            'stateJson' => ['required'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $stateJson = $this->normalizeStateJson($request->input('stateJson'));
        if ($stateJson === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::VALIDATION_FAILED);
        }

        $userId = (int) Auth::id();
        $consultaId = (string) $request->input('consultaId');
        $layoutName = trim((string) $request->input('layoutName'));

        if ($layoutName === '') {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::VALIDATION_FAILED);
        }

        $exists = PqPivotsConfig::query()
            ->where('consulta_id', $consultaId)
            ->where('layout_name', $layoutName)
            ->exists();

        if ($exists) {
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                'pivotLayout.duplicate',
                409
            );
        }

        $layout = PqPivotsConfig::query()->create([
            'consulta_id' => $consultaId,
            'layout_name' => $layoutName,
            'state_json' => $stateJson,
            'created_by_user_id' => $userId,
        ]);

        PqPivotsConfigLastUsed::query()->updateOrCreate(
            ['user_id' => $userId, 'consulta_id' => $consultaId],
            ['layout_id' => $layout->id]
        );

        return ApiResponse::success([
            'layoutId' => (int) $layout->id,
            'layoutName' => (string) $layout->layout_name,
            'stateJson' => (string) $layout->state_json,
        ], PaqSuiteEnvelopeCatalog::RESPUESTA_OK, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $layout = PqPivotsConfig::query()->find($id);
        if ($layout === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        $userId = (int) Auth::id();
        if ((int) $layout->created_by_user_id !== $userId) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'stateJson' => ['required'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $stateJson = $this->normalizeStateJson($request->input('stateJson'));
        if ($stateJson === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::VALIDATION_FAILED);
        }

        $layout->state_json = $stateJson;
        $layout->save();

        return ApiResponse::success([
            'layoutId' => (int) $layout->id,
            'layoutName' => (string) $layout->layout_name,
            'stateJson' => (string) $layout->state_json,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $layout = PqPivotsConfig::query()->find($id);
        if ($layout === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        $userId = (int) Auth::id();
        if ((int) $layout->created_by_user_id !== $userId) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN);
        }

        PqPivotsConfigLastUsed::query()
            ->where('layout_id', $layout->id)
            ->update(['layout_id' => null]);

        $layout->delete();

        return ApiResponse::success([]);
    }

    public function setActive(Request $request): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $validator = Validator::make($request->all(), [
            'consultaId' => ['required', 'string', 'max:128'],
            'layoutId' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $userId = (int) Auth::id();
        $consultaId = (string) $request->input('consultaId');
        $layoutIdRaw = $request->input('layoutId');
        $layoutId = $layoutIdRaw === null || $layoutIdRaw === '' ? null : (int) $layoutIdRaw;

        if ($layoutId === null) {
            PqPivotsConfigLastUsed::query()->updateOrCreate(
                ['user_id' => $userId, 'consulta_id' => $consultaId],
                ['layout_id' => null]
            );

            return ApiResponse::success(['layoutId' => null]);
        }

        $layout = PqPivotsConfig::query()
            ->where('id', $layoutId)
            ->where('consulta_id', $consultaId)
            ->first();

        if ($layout === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        PqPivotsConfigLastUsed::query()->updateOrCreate(
            ['user_id' => $userId, 'consulta_id' => $consultaId],
            ['layout_id' => $layout->id]
        );

        return ApiResponse::success(['layoutId' => $layout->id]);
    }

    private function disabledResponse(): ?JsonResponse
    {
        if (config('paqsuite.pivotLayoutsEnabled') === false) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN);
        }

        return null;
    }

    private function normalizeStateJson(mixed $stateJson): ?string
    {
        if (is_string($stateJson)) {
            if ($stateJson === '' || strlen($stateJson) > self::STATE_JSON_MAX_BYTES) {
                return null;
            }

            return $stateJson;
        }

        if (is_array($stateJson)) {
            $encoded = json_encode($stateJson, JSON_UNESCAPED_UNICODE);
            if ($encoded === false || strlen($encoded) > self::STATE_JSON_MAX_BYTES) {
                return null;
            }

            return $encoded;
        }

        return null;
    }
}
