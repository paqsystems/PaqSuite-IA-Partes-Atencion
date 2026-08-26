<?php

namespace App\Services\Emissions;

use PaqSuite\LaravelCore\Emissions\Contracts\EmissionTaskDispatcher;

final class SyncNoopEmissionTaskDispatcher implements EmissionTaskDispatcher
{
    public function dispatchSystemTask(
        string $processCode,
        array $parameters,
        int|string|null $companyId,
    ): string {
        return 'noop';
    }
}
