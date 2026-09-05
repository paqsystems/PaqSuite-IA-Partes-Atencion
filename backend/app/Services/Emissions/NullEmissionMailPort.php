<?php

namespace App\Services\Emissions;

use PaqSuite\LaravelCore\Emissions\Contracts\EmissionMailPort;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionArtifact;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionContext;

final class NullEmissionMailPort implements EmissionMailPort
{
    public function sendDocument(
        array $recipients,
        int|string|null $templateId,
        EmissionArtifact $pdf,
        EmissionContext $context,
    ): void {
    }
}
