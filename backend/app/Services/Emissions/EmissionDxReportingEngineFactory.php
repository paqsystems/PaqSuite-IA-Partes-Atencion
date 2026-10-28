<?php

namespace App\Services\Emissions;

use App\Repositories\Sp\Emissions\SpEmissionRepository;
use Illuminate\Contracts\Foundation\Application;
use PaqSuite\LaravelCore\Emissions\Contracts\DxReportingEngine;
use PaqSuite\LaravelCore\Emissions\DxReportingMode;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionContext;
use PaqSuite\LaravelCore\Emissions\RemoteDxReportingEngine;

final class EmissionDxReportingEngineFactory
{
    public static function make(Application $app): DxReportingEngine
    {
        $mode = DxReportingMode::normalize(
            (string) $app->make('config')->get('paqsuite.emissions.dxReporting.mode', 'fake'),
        );
        if ($mode !== DxReportingMode::REMOTE) {
            return new MinimalDxReportingEngine();
        }

        /** @var SpEmissionRepository $reports */
        $reports = $app->make(SpEmissionRepository::class);
        $httpClient = $app->make(PartesDxReportingHttpClient::class);

        $layoutResolver = static function (EmissionContext $context) use ($reports): ?array {
            $pk = PartesRemoteDxReportingEngine::parseReportPk($context->reportId);

            return $reports->findReportLayoutForRender($pk);
        };

        $remote = new RemoteDxReportingEngine(
            (string) $app->make('config')->get('paqsuite.emissions.dxReporting.serviceUrl', ''),
            $httpClient,
            (string) $app->make('config')->get('paqsuite.emissions.dxReporting.apiKey', ''),
            (int) $app->make('config')->get('paqsuite.emissions.dxReporting.timeoutSeconds', 60),
            $layoutResolver,
        );

        return new PartesRemoteDxReportingEngine($remote, $reports);
    }
}
