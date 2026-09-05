<?php

namespace App\Services\Emissions;

use PaqSuite\LaravelCore\Emissions\Contracts\EmissionAuditPort;

final class NullEmissionAuditPort implements EmissionAuditPort
{
    public function record(
        string $action,
        string $result,
        string $jobId,
        int|string|null $companyId,
        int|string|null $userId,
        array $payload,
    ): void {
    }
}
