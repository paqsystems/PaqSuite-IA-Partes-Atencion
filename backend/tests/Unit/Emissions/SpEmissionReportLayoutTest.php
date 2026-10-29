<?php

namespace Tests\Unit\Emissions;

use App\Repositories\Sp\Emissions\SpEmissionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SpEmissionReportLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function seedProcess(): void
    {
        DB::table('pq_emission_processes')->insert([
            'process_code' => 'partes.informes.consultaDetallada',
            'name' => 'Consulta detallada',
            'menu_process_code' => 'partes_consulta_detallada',
            'permite_consolidado' => 1,
            'permite_segmentado' => 0,
            'requiere_vista_previa' => 0,
            'canal_pdf' => 1,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_catalogo_sin_xml_usa_layout_must_repx(): void
    {
        $this->seedProcess();
        $id = (int) DB::table('pq_emission_reports')->insertGetId([
            'process_code' => 'partes.informes.consultaDetallada',
            'report_code' => 'copia-sin-xml',
            'name' => 'Copia',
            'is_standard' => 0,
            'is_principal' => 0,
            'visible_mobile' => 1,
            'layout_definition' => null,
            'layout_mime' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $repository = $this->app->make(SpEmissionRepository::class);
        $byId = $repository->findReportLayoutForRender($id);
        $byCode = $repository->findReportLayoutForRenderByCode('copia-sin-xml');

        $this->assertNotNull($byId);
        $this->assertSame('application/xml', $byId['layoutMime']);
        $this->assertStringContainsString('XtraReportsLayoutSerializer', $byId['layoutDefinition']);
        $this->assertStringContainsString('[clienteNombre]', $byId['layoutDefinition']);
        $this->assertSame($byId, $byCode);
    }

    public function test_catalogo_con_repx_propio_no_usa_fallback(): void
    {
        $this->seedProcess();
        $xml = '<XtraReportsLayoutSerializer Name="custom"/>';
        $id = (int) DB::table('pq_emission_reports')->insertGetId([
            'process_code' => 'partes.informes.consultaDetallada',
            'report_code' => 'copia-con-xml',
            'name' => 'Copia XML',
            'is_standard' => 0,
            'is_principal' => 0,
            'visible_mobile' => 1,
            'layout_definition' => $xml,
            'layout_mime' => 'application/xml',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $repository = $this->app->make(SpEmissionRepository::class);
        $found = $repository->findReportLayoutForRender($id);

        $this->assertSame($xml, $found['layoutDefinition'] ?? null);
    }
}
