<?php

namespace App\Services\Emissions;

use App\Repositories\Sp\Emissions\SpEmissionRepository;
use App\Support\Emissions\EmissionReportDxStorageUrl;
use Illuminate\Support\Facades\Http;

/**
 * CC-ST-4: materializa layouts del catálogo diseño en el sidecar (if-not-exists).
 */
final class EmissionSidecarLayoutHydrator
{
    public function __construct(
        private readonly SpEmissionRepository $repository,
    ) {
    }

    /**
     * @param  list<string>|null  $reportCodesFilter
     * @return array{hydrated: int, skipped: int, failed: list<array{code: string, reason: string}>}
     */
    public function hydrateProcess(string $processCode, ?array $reportCodesFilter = null): array
    {
        $processCode = trim($processCode);
        $hydrated = 0;
        $skipped = 0;
        $failed = [];

        $sidecarBase = rtrim((string) config('services.dx_reporting_sidecar_url'), '/');
        if ($sidecarBase === '') {
            return [
                'hydrated' => 0,
                'skipped' => 0,
                'failed' => [['code' => '*', 'reason' => 'sidecar_not_configured']],
            ];
        }

        $putUrl = $sidecarBase.'/api/layout/put';
        $timeout = max(5, (int) config('paqsuite.emissions.dxReporting.timeoutSeconds', 30));
        $token = trim((string) config('paqsuite.emissions.designerSidecarToken', ''));

        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        if ($token !== '') {
            $headers['X-Paq-Sidecar-Schema-Token'] = $token;
        }

        $filterSet = null;
        if (is_array($reportCodesFilter) && $reportCodesFilter !== []) {
            $filterSet = array_fill_keys(
                array_map(static fn (string $c): string => trim($c), $reportCodesFilter),
                true,
            );
        }

        foreach ($this->repository->reportsForDesignCatalog($processCode) as $item) {
            $code = is_string($item['code'] ?? null) ? trim($item['code']) : '';
            if ($code === '') {
                continue;
            }
            if ($filterSet !== null && ! isset($filterSet[$code])) {
                continue;
            }

            $isStandard = (bool) ($item['isStandard'] ?? false);
            $layoutXml = $this->repository->resolveDesignLayoutXmlForSidecarHydrate(
                $processCode,
                $code,
                $isStandard,
            );
            if ($layoutXml === null) {
                $skipped++;

                continue;
            }

            $reportUrl = EmissionReportDxStorageUrl::fromCatalogCode($code);

            try {
                $response = Http::timeout($timeout)
                    ->withHeaders($headers)
                    ->post($putUrl, [
                        'reportUrl' => $reportUrl,
                        'layoutXml' => $layoutXml,
                    ]);
            } catch (\Throwable $exception) {
                $failed[] = [
                    'code' => $code,
                    'reason' => 'sidecar_unreachable: '.$exception->getMessage(),
                ];

                continue;
            }

            if (! $response->successful()) {
                $failed[] = [
                    'code' => $code,
                    'reason' => 'sidecar_http_'.$response->status(),
                ];

                continue;
            }

            $body = $response->json();
            if (! is_array($body) || ($body['ok'] ?? false) !== true) {
                $failed[] = [
                    'code' => $code,
                    'reason' => is_string($body['error'] ?? null) ? (string) $body['error'] : 'sidecar_rejected',
                ];

                continue;
            }

            if (($body['skipped'] ?? false) === true) {
                $skipped++;
            } else {
                $hydrated++;
            }
        }

        return [
            'hydrated' => $hydrated,
            'skipped' => $skipped,
            'failed' => $failed,
        ];
    }
}