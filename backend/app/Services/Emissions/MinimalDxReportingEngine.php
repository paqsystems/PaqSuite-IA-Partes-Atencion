<?php

namespace App\Services\Emissions;

use PaqSuite\LaravelCore\Emissions\Contracts\DxReportingEngine;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionArtifact;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionContext;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionDataset;

final class MinimalDxReportingEngine implements DxReportingEngine
{
    public function __construct(private readonly MinimalPdfArtifactFactory $factory = new MinimalPdfArtifactFactory())
    {
    }

    public function render(
        string $format,
        EmissionDataset $dataset,
        EmissionContext $context,
    ): EmissionArtifact {
        $seed = (string) ($context->jobId ?? $context->processCode);
        $extension = match ($format) {
            'xlsx' => 'xlsx',
            'csv' => 'csv',
            default => 'pdf',
        };
        $fileName = sprintf(
            '%s-%s.%s',
            $context->processCode,
            substr($seed, 0, 8),
            $extension,
        );

        return $this->factory->make($seed.'-rows'.$dataset->rowCount(), $fileName);
    }
}
