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
            'items' => $this->empresaAdminRepository->listAll(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $item = $this->empresaAdminRepository->findById($id);
        if ($item === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        return ApiResponse::success(['item' => $item]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombreEmpresa' => ['sometimes', 'string', 'max:255'],
            'habilitada' => ['sometimes', 'boolean'],
            'theme' => ['sometimes', 'nullable', 'string', Rule::in(EmpresaThemeCatalog::values())],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $item = $this->empresaAdminRepository->update($id, $validator->validated());
        if ($item === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        return ApiResponse::success(['item' => $item]);
    }
}
