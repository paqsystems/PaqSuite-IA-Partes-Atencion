<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PqGridLayout;
use App\Models\PqGridLayoutLastUsed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;

/**
 * GEN-11 grid layouts MVP (smoke). Producción: acceso vía stored procedures
 * (pq_sp_grid_layout_*); estas tablas/consultas son el adapter Eloquent de referencia.
 */
final class GridLayoutsController extends Controller
{
    private const STATE_JSON_MAX_BYTES = 512 * 1024;

    public function index(Request $request): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $validator = Validator::make($request->query(), [
            'proceso' => ['required', 'string', 'max:128'],
            'gridId' => ['required', 'string', 'max:128'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $userId = (int) Auth::id();
        $proceso = (string) $request->query('proceso');
        $gridId = (string) $request->query('gridId');

        $items = PqGridLayout::query()
            ->where('user_id', $userId)
            ->where('proceso', $proceso)
            ->where('grid_id', $gridId)
            ->orderBy('layout_name')
            ->get()
            ->map(fn (PqGridLayout $layout): array => $this->mapItem($layout))
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
            'proceso' => ['required', 'string', 'max:128'],
            'gridId' => ['required', 'string', 'max:128'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $userId = (int) Auth::id();
        $proceso = (string) $request->query('proceso');
        $gridId = (string) $request->query('gridId');

        $lastUsed = PqGridLayoutLastUsed::query()
            ->with('layout')
            ->where('user_id', $userId)
            ->where('proceso', $proceso)
            ->where('grid_id', $gridId)
            ->first();

        if ($lastUsed === null || $lastUsed->layout === null) {
            return ApiResponse::success([
                'layoutId' => null,
                'layoutName' => null,
                'isSystem' => null,
                'stateJson' => null,
            ]);
        }

        return ApiResponse::success($this->mapItem($lastUsed->layout, true));
    }

    public function store(Request $request): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $validator = Validator::make($request->all(), [
            'proceso' => ['required', 'string', 'max:128'],
            'gridId' => ['required', 'string', 'max:128'],
            'layoutName' => ['required', 'string', 'max:128'],
            'stateJson' => ['required'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $stateJsonError = $this->validateStateJsonSize($request->input('stateJson'));
        if ($stateJsonError !== null) {
            return $stateJsonError;
        }

        $userId = (int) Auth::id();
        $proceso = (string) $request->input('proceso');
        $gridId = (string) $request->input('gridId');
        $layoutName = (string) $request->input('layoutName');

        $exists = PqGridLayout::query()
            ->where('user_id', $userId)
            ->where('proceso', $proceso)
            ->where('grid_id', $gridId)
            ->where('layout_name', $layoutName)
            ->exists();

        if ($exists) {
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                'gridLayout.duplicate',
                409
            );
        }

        $layout = PqGridLayout::query()->create([
            'user_id' => $userId,
            'proceso' => $proceso,
            'grid_id' => $gridId,
            'layout_name' => $layoutName,
            'state_json' => json_encode($request->input('stateJson')),
            'is_system' => false,
        ]);

        PqGridLayoutLastUsed::query()->updateOrCreate(
            ['user_id' => $userId, 'proceso' => $proceso, 'grid_id' => $gridId],
            ['layout_id' => $layout->id]
        );

        return ApiResponse::success(['item' => $this->mapItem($layout)], PaqSuiteEnvelopeCatalog::RESPUESTA_OK, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $layout = PqGridLayout::query()->find($id);
        if ($layout === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        $userId = (int) Auth::id();
        if ((int) $layout->user_id !== $userId) {
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

        $stateJsonError = $this->validateStateJsonSize($request->input('stateJson'));
        if ($stateJsonError !== null) {
            return $stateJsonError;
        }

        $layout->state_json = json_encode($request->input('stateJson'));
        $layout->save();

        return ApiResponse::success(['item' => $this->mapItem($layout)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $layout = PqGridLayout::query()->find($id);
        if ($layout === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        $userId = (int) Auth::id();
        if ((int) $layout->user_id !== $userId) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN);
        }

        PqGridLayoutLastUsed::query()
            ->where('layout_id', $layout->id)
            ->update(['layout_id' => null]);

        $layout->delete();

        return ApiResponse::success(['deleted' => true]);
    }

    public function setActive(Request $request): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $validator = Validator::make($request->all(), [
            'proceso' => ['required', 'string', 'max:128'],
            'gridId' => ['required', 'string', 'max:128'],
            'layoutId' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $userId = (int) Auth::id();
        $proceso = (string) $request->input('proceso');
        $gridId = (string) $request->input('gridId');
        $layoutIdRaw = $request->input('layoutId');
        $layoutId = $layoutIdRaw === null || $layoutIdRaw === '' ? null : (int) $layoutIdRaw;

        if ($layoutId === null) {
            PqGridLayoutLastUsed::query()->updateOrCreate(
                ['user_id' => $userId, 'proceso' => $proceso, 'grid_id' => $gridId],
                ['layout_id' => null]
            );

            return ApiResponse::success(['layoutId' => null]);
        }

        $layout = PqGridLayout::query()
            ->where('id', $layoutId)
            ->where('proceso', $proceso)
            ->where('grid_id', $gridId)
            ->where(function ($q) use ($userId): void {
                $q->where('user_id', $userId)->orWhere('is_system', true);
            })
            ->first();

        if ($layout === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        PqGridLayoutLastUsed::query()->updateOrCreate(
            ['user_id' => $userId, 'proceso' => $proceso, 'grid_id' => $gridId],
            ['layout_id' => $layout->id]
        );

        return ApiResponse::success(['layoutId' => $layout->id]);
    }

    private function disabledResponse(): ?JsonResponse
    {
        if (config('paqsuite.gridLayoutsEnabled') === false) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::AUTH_FORBIDDEN);
        }

        return null;
    }

    private function validateStateJsonSize(mixed $stateJson): ?JsonResponse
    {
        $encoded = json_encode($stateJson);
        if ($encoded === false || strlen($encoded) > self::STATE_JSON_MAX_BYTES) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => ['stateJson' => ['stateJson excede el máximo de 512KB']]]
            );
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapItem(PqGridLayout $layout, bool $withState = false): array
    {
        $viewerUserId = (int) Auth::id();
        $item = [
            'id' => $layout->id,
            'layoutName' => (string) $layout->layout_name,
            'isSystem' => (bool) $layout->is_system,
            'isOwner' => (int) $layout->user_id === $viewerUserId,
        ];

        if ($withState) {
            $item['layoutId'] = $layout->id;
            $item['stateJson'] = json_decode((string) $layout->state_json, true);
        }

        return $item;
    }
}
