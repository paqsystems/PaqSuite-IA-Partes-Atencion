<?php

namespace App\Services\Auth;

use App\Models\User;

interface PostLoginBusinessGate
{
    /**
     * @return array<string, mixed> fragmento a mergear en resultado de sesión (p. ej. ['partes' => ...])
     *
     * @throws PostLoginBusinessGateException cuando el gate rechaza (3003)
     */
    public function assertAllowed(User $user): array;
}
