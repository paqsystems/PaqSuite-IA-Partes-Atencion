<?php

namespace App\Repositories\Sp;

use PaqSuite\LaravelCore\Security\AccesoTotalChecker;

final class SpAccesoTotalChecker implements AccesoTotalChecker
{
    public function __construct(
        private readonly SpCaller $spCaller
    ) {
    }

    public function hasAccesoTotal(int $userId, ?int $empresaId = null): bool
    {
        $row = $this->spCaller->callFirst('pq_sp_user_acceso_total', [
            'UserId' => $userId,
            'EmpresaId' => $empresaId,
        ]);

        return $row !== null && isset($row->accesoTotal);
    }
}
