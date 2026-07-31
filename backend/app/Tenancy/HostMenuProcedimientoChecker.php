<?php

namespace App\Tenancy;

use Illuminate\Support\Facades\Auth;
use PaqSuite\LaravelCore\Menu\MenuQueryRepository;
use PaqSuite\LaravelCore\Security\AccesoTotalChecker;
use PaqSuite\LaravelCore\Security\CompanyAllowedChecker;
use PaqSuite\LaravelCore\Tenancy\MenuProcedimientoChecker;

/**
 * Checker host Partes: AccesoTotal OR permiso en empresa activa (GEN-07 MVP).
 */
final class HostMenuProcedimientoChecker implements MenuProcedimientoChecker
{
    public function __construct(
        private readonly AccesoTotalChecker $accesoTotalChecker,
        private readonly CompanyAllowedChecker $companyAllowedChecker,
        private readonly MenuQueryRepository $menuQueryRepository,
    ) {
    }

    public function existsInMenu(string $procedimiento): bool
    {
        $codigo = trim($procedimiento);
        if ($codigo === '') {
            return false;
        }

        foreach ($this->menuQueryRepository->listEnabled() as $row) {
            if (trim((string) ($row['procedimiento'] ?? '')) === $codigo) {
                return true;
            }
        }

        return false;
    }

    public function userMayExecute(string $procedimiento): bool
    {
        $user = Auth::user();
        if ($user === null) {
            return false;
        }

        $empresaId = (int) (request()->header(config('paqsuite.headers.company', 'X-Company-Id')) ?: 0);
        if ($empresaId <= 0) {
            $empresaId = 1;
        }

        if ($this->accesoTotalChecker->hasAccesoTotal((int) $user->id, $empresaId)) {
            return true;
        }

        return $this->companyAllowedChecker->isAllowed((int) $user->id, $empresaId);
    }
}
