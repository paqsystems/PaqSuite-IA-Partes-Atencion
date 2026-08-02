<?php

namespace App\Services\Auth;

use App\Models\User;

interface UserEmpresasResolver
{
    /**
     * @return list<array{id: int, nombreEmpresa: string}>
     */
    public function resolveForUser(User $user): array;
}
