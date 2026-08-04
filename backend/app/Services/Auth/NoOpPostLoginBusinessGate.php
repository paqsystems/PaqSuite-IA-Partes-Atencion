<?php

namespace App\Services\Auth;

use App\Models\User;

/**
 * Gate de negocio post-credenciales: no-op (host genérico / tests sin dominio Partes).
 */
final class NoOpPostLoginBusinessGate implements PostLoginBusinessGate
{
    public function assertAllowed(User $user): array
    {
        return [];
    }
}
