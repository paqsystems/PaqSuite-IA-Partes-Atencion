<?php

/**
 * Genera JSON del sidecar DX (Field List lab) desde emission-designer-schemas (GEN-37).
 *
 * Uso (desde backend/):
 *   php scripts/dev/export-emission-designer-schemas-for-dx-sidecar.php
 *
 * Luego: scripts/dev/sync-dx-reporting-schemas.ps1 (copia resources/emissions/schemas al sidecar FW).
 */

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\Emissions\EmissionDesignerSchemaDxSidecarExport;

$written = EmissionDesignerSchemaDxSidecarExport::exportAllAliases();

foreach ($written as $path) {
    echo 'export sidecar: '.$path.PHP_EOL;
}

echo 'OK — '.count($written).' archivo(s) desde catálogo canónico GEN-37.'.PHP_EOL;