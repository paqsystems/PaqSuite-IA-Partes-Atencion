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
 * ABM usuarios (GEN-06). Contrato SPEC: codigo / nombre / email / activo.
 * `index` es de lectura amplia (lookup maestros Partes vía `?soloActivos=`).
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
                static fn (array $item): bool => (bool) ($item['activo'] ?? false)
            ));
        }

        return ApiResponse::success(['items' => $items]);
    }

    public function show(int $id): JsonResponse
    {
        $item = $this->userAdminRepository->findById($id);
        if ($item === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        return ApiResponse::success(['item' => $item]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'codigo' => ['required', 'string', 'max:64', Rule::unique('users', 'usuario')],
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
            'codigo' => ['sometimes', 'string', 'max:64', Rule::unique('users', 'usuario')->ignore($id)],
            'nombre' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => ['sometimes', 'nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $data = $validator->validated();
        if (array_key_exists('password', $data) && ($data['password'] === null || $data['password'] === '')) {
            unset($data['password']);
        }

        if (isset($data['password'])) {
            $passwordCheck = $this->passwordPolicy->evaluate((string) $data['password']);
            if ($passwordCheck['ok'] !== true) {
                return ApiResponse::errorFromCatalog(
                    PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                    ['respuesta' => $passwordCheck['errorKey']]
                );
            }
        }

        $user = $this->userAdminRepository->update($id, $data);
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
