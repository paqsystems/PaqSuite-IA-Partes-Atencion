<?php

namespace App\Repositories\Sp;

use PaqSuite\LaravelCore\Security\CompanyAllowedChecker;

final class SpCompanyAllowedChecker implements CompanyAllowedChecker
{
    public function __construct(
        private readonly SpCaller $spCaller
    ) {
    }

    public function isAllowed(int $userId, int $companyId): bool
    {
        $row = $this->spCaller->callFirst('pq_sp_user_empresa_allowed', [
            'user_id' => $userId,
            'empresa_id' => $companyId,
        ]);

        return $row !== null && (int) ($row->allowed ?? 0) === 1;
    }
}
