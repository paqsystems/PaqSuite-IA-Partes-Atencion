<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Auth\PolicyCompliantPasswordGenerator;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\NotifyUsuarioAccessNotification;
use App\Services\Auth\PasswordResetLinkBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PaqSuite\LaravelCore\Auth\PasswordPolicy;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\I18n\LocaleNormalizer;
use PaqSuite\LaravelCore\Security\UserAdminRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

/**
 * ABM usuarios (GEN-06). Contrato SPEC: codigo / nombre / email / activo.
 * `index` es de lectura amplia (lookup maestros Partes vía `?soloActivos=`).
 */
final class UsuariosController extends Controller
{
    public function __construct(
        private readonly UserAdminRepository $userAdminRepository,
        private readonly PasswordPolicy $passwordPolicy,
        private readonly PolicyCompliantPasswordGenerator $passwordGenerator,
        private readonly PasswordResetLinkBuilder $passwordResetLinkBuilder,
        private readonly LocaleNormalizer $localeNormalizer,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $items = $this->userAdminRepository->listAll();

        $soloActivosRaw = $request->query('soloActivos');
        // Sin query: ABM SDK lista todos. Maestros envían soloActivos=1.
        $soloActivos = $soloActivosRaw === null || $soloActivosRaw === '' ? '0' : (string) $soloActivosRaw;

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
            'codigo' => ['required_without:usuario', 'string', 'max:64', Rule::unique('users', 'usuario')],
            'usuario' => ['required_without:codigo', 'string', 'max:64', Rule::unique('users', 'usuario')],
            'nombre' => ['required_without:name', 'string', 'max:255'],
            'name' => ['required_without:nombre', 'string', 'max:255'],
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
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                $passwordCheck['errorKey'],
                422
            );
        }

        $data = $validator->validated();
        if (!isset($data['codigo']) && isset($data['usuario'])) {
            $data['codigo'] = $data['usuario'];
        }
        if (!isset($data['nombre']) && isset($data['name'])) {
            $data['nombre'] = $data['name'];
        }

        $user = $this->userAdminRepository->create($data);

        return ApiResponse::success(['item' => $user], PaqSuiteEnvelopeCatalog::RESPUESTA_OK, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'codigo' => ['sometimes', 'string', 'max:64', Rule::unique('users', 'usuario')->ignore($id)],
            'usuario' => ['sometimes', 'string', 'max:64', Rule::unique('users', 'usuario')->ignore($id)],
            'nombre' => ['sometimes', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => ['sometimes', 'nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
            'inhabilitado' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::errorFromCatalog(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                ['errors' => $validator->errors()->toArray()]
            );
        }

        $data = $validator->validated();
        if (!isset($data['codigo']) && isset($data['usuario'])) {
            $data['codigo'] = $data['usuario'];
        }
        if (!isset($data['nombre']) && isset($data['name'])) {
            $data['nombre'] = $data['name'];
        }
        if (array_key_exists('password', $data) && ($data['password'] === null || $data['password'] === '')) {
            unset($data['password']);
        }

        if (isset($data['password'])) {
            $passwordCheck = $this->passwordPolicy->evaluate((string) $data['password']);
            if ($passwordCheck['ok'] !== true) {
                return ApiResponse::error(
                    PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                    $passwordCheck['errorKey'],
                    422
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

    public function notifyAccess(Request $request, int $id): JsonResponse
    {
        $item = $this->userAdminRepository->findById($id);
        if ($item === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        $email = trim((string) ($item['email'] ?? ''));
        if ($email === '') {
            return ApiResponse::error(
                PaqSuiteEnvelopeCatalog::VALIDATION_FAILED,
                'admin.usuarios.notify.emailRequired',
                422
            );
        }

        $plain = $this->passwordGenerator->generate();
        $updated = $this->userAdminRepository->update($id, ['password' => $plain]);
        if ($updated === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        $user = User::query()->find($id);
        if ($user === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::RESOURCE_NOT_FOUND);
        }

        $locale = $this->localeNormalizer->normalize($user->locale)
            ?? $this->localeNormalizer->normalize($request->header('Accept-Language'))
            ?? 'es';
        $cliente = strtoupper(trim((string) $request->header(
            (string) config('paqsuite.headers.cliente', 'X-Paq-Cliente'),
            ''
        )));
        $portalUrl = $this->passwordResetLinkBuilder->buildAccessUrl($cliente === '' ? null : $cliente);

        try {
            Notification::send($user, new NotifyUsuarioAccessNotification(
                $locale,
                $cliente,
                $portalUrl,
                (string) ($item['usuario'] ?? $item['codigo'] ?? ''),
                $plain,
            ));
        } catch (TransportExceptionInterface) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::MAIL_SEND_FAILED);
        } catch (Throwable $e) {
            if ($this->isMailTransportFailure($e)) {
                return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::MAIL_SEND_FAILED);
            }

            throw $e;
        }

        return ApiResponse::success([
            'item' => [
                'id' => $id,
                'notified' => true,
            ],
        ]);
    }

    private function isMailTransportFailure(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'smtp')
            || str_contains($message, 'mailer')
            || str_contains($message, 'unable to send')
            || str_contains($message, 'connection could not be established');
    }
}
