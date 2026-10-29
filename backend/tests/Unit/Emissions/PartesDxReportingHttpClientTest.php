<?php

namespace Tests\Unit\Emissions;

use App\Services\Emissions\PartesDxReportingHttpClient;
use Tests\TestCase;

final class PartesDxReportingHttpClientTest extends TestCase
{
    public function test_ensure_repx_completa_layout_vacio(): void
    {
        $payload = PartesDxReportingHttpClient::ensureRepxLayout([
            'format' => 'pdf',
            'layoutDefinition' => null,
        ]);

        $this->assertSame('application/xml', $payload['layoutMime']);
        $this->assertIsString($payload['layoutDefinition']);
        $this->assertStringContainsString('XtraReportsLayoutSerializer', $payload['layoutDefinition']);
        $this->assertStringContainsString('[clienteNombre]', $payload['layoutDefinition']);
    }

    public function test_ensure_repx_conserva_xml_existente(): void
    {
        $xml = '<XtraReportsLayoutSerializer Name="custom"/>';
        $payload = PartesDxReportingHttpClient::ensureRepxLayout([
            'layoutDefinition' => $xml,
            'layoutMime' => 'text/xml',
        ]);

        $this->assertSame($xml, $payload['layoutDefinition']);
        $this->assertSame('text/xml', $payload['layoutMime']);
    }
}
