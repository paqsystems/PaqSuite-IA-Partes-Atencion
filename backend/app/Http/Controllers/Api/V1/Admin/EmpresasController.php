<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Support\EmpresaThemeCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\Security\EmpresaAdminRepository;

/**
 * Consulta/edición empresas (GEN-06). MONO: sin alta/baja — solo `update`.
 * Contrato SPEC: nombreEmpresa / habilitada / theme.
 *
 * Theme: persistencia stock DX; entrada acepta también `paqsuite.*` del SDK;
 * respuestas del ABM exponen `paqsuite.*` para el SelectBox A1.
 */
final class EmpresasController extends Controller
{
    public function __construct(
        private readonly EmpresaAdminRepository $empresaAdminRepository
    ) {
    }

    public function index(): JsonResponse
    {
        return ApiResponse::success([
            'items' => EmpresaThemeCatalog::mapItemsThemeForUi(
                $this->empresaAdminRepository->listAll()
            ),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $item = $this->empresaAdminRepository->findById($id);
        if ($item === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        return ApiResponse::success([
            'item' => EmpresaThemeCatalog::mapItemThemeForUi($item),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombreEmpresa' => ['sometimes', 'string', 'max:255'],
            'habilitada' => ['sometimes', 'boolean'],
            'theme' => ['sometimes', 'nullable', 'string', Rule::in(EmpresaThemeCatalog::acceptedInputValues())],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $data = $validator->validated();
        if (array_key_exists('theme', $data)) {
            $data['theme'] = EmpresaThemeCatalog::normalizeForPersistence(
                $data['theme'] !== null ? (string) $data['theme'] : null
            );
        }

        $item = $this->empresaAdminRepository->update($id, $data);
        if ($item === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        return ApiResponse::success([
            'item' => EmpresaThemeCatalog::mapItemThemeForUi($item),
        ]);
    }
}
