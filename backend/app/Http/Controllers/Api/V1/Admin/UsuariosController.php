<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PaqSuite\LaravelCore\Auth\PasswordPolicy;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\Security\UserAdminRepository;

/**
 * ABM usuarios (GEN-06). `index` es de lectura amplia (usado también por lookup
 * de maestros Partes vía `?soloActivos=`); alta/edición/baja exigen `paqsuite.seguridadAdmin`.
 */
final class UsuariosController extends Controller
{
    public function __construct(
        private readonly UserAdminRepository $userAdminRepository,
        private readonly PasswordPolicy $passwordPolicy
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $items = $this->userAdminRepository->listAll();

        $soloActivosRaw = $request->query('soloActivos');
        $soloActivos = $soloActivosRaw === null || $soloActivosRaw === '' ? '1' : (string) $soloActivosRaw;

        if ($soloActivos !== '0') {
            $items = array_values(array_filter(
                $items,
                static fn (array $item): bool => ($item['activo'] ?? false) && !($item['inhabilitado'] ?? false)
            ));
        }

        return ApiResponse::success(['items' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'usuario' => ['required', 'string', 'max:64', Rule::unique('users', 'usuario')],
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $passwordCheck = $this->passwordPolicy->evaluate((string) $request->input('password'));
        if ($passwordCheck['ok'] !== true) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['respuesta' => $passwordCheck['errorKey']]
            );
        }

        $user = $this->userAdminRepository->create($validator->validated());

        return ApiResponse::success(['item' => $user], PaqSuiteEnvelopeCatalog::RESPUESTA_OK, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'usuario' => ['sometimes', 'string', 'max:64', Rule::unique('users', 'usuario')->ignore($id)],
            'nombre' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'activo' => ['sometimes', 'boolean'],
            'inhabilitado' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $user = $this->userAdminRepository->update($id, $validator->validated());
        if ($user === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        return ApiResponse::success(['item' => $user]);
    }

    public function destroy(int $id): JsonResponse
    {
        if (!$this->userAdminRepository->softDelete($id)) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        return ApiResponse::success(['deleted' => true]);
    }
}
