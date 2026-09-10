<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use PaqSuite\LaravelCore\Auth\ParametroStore;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;

/**
 * Política de contraseña del diccionario (hint FE guest + autenticado).
 * Compatible con laravel-core publicado (sin depender de PasswordPolicy::sessionPolicyFields).
 */
final class PasswordPolicyController extends Controller
{
    public function __construct(
        private readonly ParametroStore $parametroStore
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $complejidad = strtolower((string) ($this->parametroStore->getString('PasswordComplejidad', 'simple') ?? 'simple'));

        return ApiResponse::success([
            'passwordComplejidad' => $complejidad === 'segura' ? 'segura' : 'simple',
            'passwordLongitudMin' => max(1, (int) ($this->parametroStore->getInt('PasswordLongitudMin', 8) ?? 8)),
        ]);
    }
}
