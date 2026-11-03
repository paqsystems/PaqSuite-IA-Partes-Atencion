<?php

namespace Tests\Unit\Emissions;

use App\Services\Emissions\PartesEmissionDesignRepxBuilder;
use App\Services\Emissions\PartesPaqueteHorasEmissionPort;
use PHPUnit\Framework\TestCase;

final class PartesEmissionDesignRepxBuilderTest extends TestCase
{
    public function test_paquete_horas_repx_expone_schema_de_negocio(): void
    {
        $xml = PartesEmissionDesignRepxBuilder::paqueteHorasPrincipal();
        $this->assertStringContainsString('XtraReportsLayoutSerializer', $xml);
        $this->assertStringContainsString('partes_paqueteHoras_principal', $xml);

        if (! preg_match('/Base64="([^"]+)"/', $xml, $matches)) {
            $this->fail('REPX sin JsonDataSource Base64');
        }
        $decoded = base64_decode($matches[1], true);
        $this->assertIsString($decoded);

        foreach (PartesPaqueteHorasEmissionPort::designDatasetColumnNames() as $column) {
            $this->assertStringContainsString(
                'Name="'.$column.'"',
                $decoded,
                'Falta columna de diseño: '.$column
            );
        }

        $this->assertStringNotContainsString('Name="id"', $decoded);
        $this->assertStringNotContainsString('Name="label"', $decoded);
    }
}
