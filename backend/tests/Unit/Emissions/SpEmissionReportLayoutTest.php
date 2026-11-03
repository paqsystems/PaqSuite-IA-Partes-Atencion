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

    public function test_paquete_horas_json_tabular_usa_repx_con_schema_negocio(): void
    {
        DB::table('pq_emission_processes')->updateOrInsert(
            ['process_code' => 'partes.informes.paqueteHoras'],
            [
                'name' => 'Paquete de horas',
                'menu_process_code' => 'partes_informe_paquete_horas',
                'permite_consolidado' => 1,
                'permite_segmentado' => 0,
                'requiere_vista_previa' => 0,
                'canal_pdf' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $layout = json_encode(['kind' => 'tabular', 'columns' => ['fecha', 'clienteNombre', 'saldo']]);
        DB::table('pq_emission_reports')->updateOrInsert(
            [
                'process_code' => 'partes.informes.paqueteHoras',
                'report_code' => 'partes_paqueteHoras_principal',
            ],
            [
                'name' => '<Original>',
                'is_standard' => 1,
                'is_principal' => 1,
                'visible_mobile' => 0,
                'layout_definition' => $layout,
                'layout_mime' => 'application/json',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $id = (int) DB::table('pq_emission_reports')
            ->where('process_code', 'partes.informes.paqueteHoras')
            ->where('report_code', 'partes_paqueteHoras_principal')
            ->value('id');

        $repository = $this->app->make(SpEmissionRepository::class);
        $found = $repository->findReportLayoutForRender($id);

        $this->assertNotNull($found);
        $this->assertStringContainsString('XtraReportsLayoutSerializer', $found['layoutDefinition']);
        $this->assertStringContainsString('clienteNombre', $found['layoutDefinition']);
        if (preg_match('/Base64="([^"]+)"/', $found['layoutDefinition'], $matches)) {
            $decoded = base64_decode($matches[1], true);
            $this->assertIsString($decoded);
            $this->assertStringContainsString('saldo', $decoded);
            $this->assertStringNotContainsString('Name="id"', $decoded);
        } else {
            $this->fail('REPX paquete horas sin JsonDataSource Base64');
        }
    }
}
