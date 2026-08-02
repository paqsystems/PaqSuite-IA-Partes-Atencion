<?php

namespace App\Services\Auth;

use App\Models\User;
use PaqSuite\LaravelCore\Security\UserEmpresasQueryRepository;

final class SpUserEmpresasResolver implements UserEmpresasResolver
{
    public function __construct(
        private readonly UserEmpresasQueryRepository $userEmpresasQueryRepository
    ) {
    }

    public function resolveForUser(User $user): array
    {
        return array_map(static function (array $empresa): array {
            return [
                'id' => (int) $empresa['id'],
                'nombreEmpresa' => (string) ($empresa['nombreEmpresa'] ?? ''),
            ];
        }, $this->userEmpresasQueryRepository->listForUser((int) $user->id));
    }
}
