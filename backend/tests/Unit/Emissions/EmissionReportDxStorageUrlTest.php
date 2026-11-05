<?php

declare(strict_types=1);

namespace Tests\Unit\Emissions;

use App\Support\Emissions\EmissionReportDxStorageUrl;
use PHPUnit\Framework\TestCase;

final class EmissionReportDxStorageUrlTest extends TestCase
{
    public function test_fromCatalogCode_alinea_con_sidecar_safe_url(): void
    {
        $this->assertSame(
            'partes-consultaDetallada-principal',
            EmissionReportDxStorageUrl::fromCatalogCode('partes.consultaDetallada.principal'),
        );
        $this->assertSame(
            'partes_paqueteHoras_principal',
            EmissionReportDxStorageUrl::fromCatalogCode('partes_paqueteHoras_principal'),
        );
        $this->assertSame(
            'Prueba-de-Partes',
            EmissionReportDxStorageUrl::fromCatalogCode('Prueba-de-Partes'),
        );
    }
}
