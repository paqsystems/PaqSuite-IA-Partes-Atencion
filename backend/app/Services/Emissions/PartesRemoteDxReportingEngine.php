<?php

namespace App\Services\Emissions;

use App\Repositories\Sp\Emissions\SpEmissionRepository;
use PaqSuite\LaravelCore\Emissions\Contracts\DxReportingEngine;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionArtifact;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionContext;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionDataset;
use PaqSuite\LaravelCore\Emissions\RemoteDxReportingEngine;

/**
 * Render remoto GEN-15: mapea reportId numérico del catálogo → report_code del sidecar (.repx).
 */
final class PartesRemoteDxReportingEngine implements DxReportingEngine
{
    public function __construct(
        private readonly RemoteDxReportingEngine $remote,
        private readonly SpEmissionRepository $reports,
    ) {
    }

    public function render(
        string $format,
        EmissionDataset $dataset,
        EmissionContext $context,
    ): EmissionArtifact {
        $pk = self::parseReportPk($context->reportId);
        $renderKey = $pk === null ? null : $this->reports->findReportRenderKeyById($pk);

        $mapped = new EmissionContext(
            $context->processCode,
            $context->companyIds,
            $context->userId,
            $context->mode,
            $context->channel,
            $renderKey ?? $context->reportId,
            $context->mailTemplateId,
            $context->jobId,
            $context->groupId,
        );

        return $this->remote->render($format, $dataset, $mapped);
    }

    public static function parseReportPk(int|string|null $reportId): ?int
    {
        if ($reportId === null || $reportId === '') {
            return null;
        }
        if (is_int($reportId)) {
            return $reportId > 0 ? $reportId : null;
        }
        if (ctype_digit($reportId)) {
            $parsed = (int) $reportId;

            return $parsed > 0 ? $parsed : null;
        }

        return null;
    }
}
