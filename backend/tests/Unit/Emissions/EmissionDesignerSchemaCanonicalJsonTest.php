<?php

namespace Tests\Unit\Emissions;

use App\Services\Emissions\PartesConsultaDetalladaEmissionPort;
use App\Services\Emissions\PartesPaqueteHorasEmissionPort;
use Tests\TestCase;

final class EmissionDesignerSchemaCanonicalJsonTest extends TestCase
{
    public function test_paquete_horas_canonical_schema_matches_port(): void
    {
        $this->assertCanonicalSchemaMatchesPort(
            base_path('database/emission-designer-schemas/partes.informes.paqueteHoras.json'),
            PartesPaqueteHorasEmissionPort::designDatasetColumnNames(),
            'Paquete de horas',
        );
    }

    public function test_consulta_detallada_canonical_schema_matches_port(): void
    {
        $this->assertCanonicalSchemaMatchesPort(
            base_path('database/emission-designer-schemas/partes.informes.consultaDetallada.json'),
            PartesConsultaDetalladaEmissionPort::designDatasetColumnNames(),
            'Consulta detallada',
        );
    }

    /**
     * @param list<string> $expectedColumns
     */
    private function assertCanonicalSchemaMatchesPort(string $path, array $expectedColumns, string $displayName): void
    {
        $this->assertFileExists($path);
        $decoded = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($decoded);
        $this->assertSame(1, $decoded['version'] ?? null);
        $this->assertSame($displayName, $decoded['datasetDisplayName'] ?? null);

        $names = [];
        foreach ($decoded['fields'] ?? [] as $field) {
            if (is_array($field) && isset($field['name'])) {
                $names[] = (string) $field['name'];
            }
        }

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $names, "Falta {$column} en {$path}");
        }
    }
}
