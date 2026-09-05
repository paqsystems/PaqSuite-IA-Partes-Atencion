<?php

namespace App\Services\Emissions;

use PaqSuite\LaravelCore\Emissions\Contracts\EmissionNotificationPort;

final class NullEmissionNotificationPort implements EmissionNotificationPort
{
    public function notify(
        int|string $userId,
        string $titleKey,
        string $bodyKey,
        array $params,
        string $jobId,
    ): void {
    }
}
