<?php

namespace Tests\Unit\Emissions;

use App\Services\Emissions\DesignAssistant\DesignSemanticCatalog;
use App\Services\Emissions\PartesConsultaDetalladaEmissionPort;
use App\Services\Emissions\PartesPaqueteHorasEmissionPort;
use App\Support\Emissions\EmissionDesignerSchemaDxSidecarExport;
use Tests\TestCase;

final class EmissionDesignerSchemaCanonicalJsonTest extends TestCase
{
    public function test_paquete_horas_canonical_schema_matches_port(): void
    {
        $this->assertCanonicalSchemaMatchesPort(
            base_path('database/emission-designer-schemas/partes.informes.paqueteHoras.json'),
            PartesPaqueteHorasEmissionPort::designDatasetColumnNames(),
            'Paquete de horas',
            requireIaSemantics: false,
        );
    }

    public function test_consulta_detallada_canonical_schema_matches_port_and_gen37(): void
    {
        $this->assertCanonicalSchemaMatchesPort(
            base_path('database/emission-designer-schemas/partes.informes.consultaDetallada.json'),
            PartesConsultaDetalladaEmissionPort::designDatasetColumnNames(),
            'Consulta detallada',
            requireIaSemantics: true,
        );
    }

    public function test_dx_sidecar_exports_match_canonical_gen37_labels(): void
    {
        foreach (EmissionDesignerSchemaDxSidecarExport::aliasTargetsByProcessCode() as $processCode => $relPaths) {
            $canonical = EmissionDesignerSchemaDxSidecarExport::readCanonical($processCode);
            $expected = EmissionDesignerSchemaDxSidecarExport::encodeSidecarJson(
                EmissionDesignerSchemaDxSidecarExport::toSidecarPayloadFromCanonical($canonical)
            );

            foreach ($relPaths as $rel) {
                $abs = base_path($rel);
                $this->assertFileExists($abs, "Falta export sidecar {$rel}; correr export-emission-designer-schemas-for-dx-sidecar.php");
                $this->assertSame(
                    $expected,
                    (string) file_get_contents($abs),
                    "Drift GEN-37: {$rel} no coincide con canónico {$processCode}"
                );
            }
        }
    }

    public function test_consulta_detallada_semantic_catalog_loads_from_json(): void
    {
        $fields = DesignSemanticCatalog::fieldsForProcess('partes.informes.consultaDetallada');
        $this->assertNotEmpty($fields);
        $byName = [];
        foreach ($fields as $field) {
            $byName[$field['name']] = $field;
        }
        $this->assertArrayHasKey('duracionMinutos', $byName);
        $this->assertSame('Duración (minutos)', $byName['duracionMinutos']['label']);
        $this->assertContains('horas', $byName['duracionMinutos']['synonyms']);
        $vocab = DesignSemanticCatalog::promptVocabulary('partes.informes.consultaDetallada');
        $this->assertStringContainsString('duracionMinutos', $vocab);
        $this->assertStringContainsString('horas', $vocab);
    }

    /**
     * @param list<string> $expectedColumns
     */
    private function assertCanonicalSchemaMatchesPort(
        string $path,
        array $expectedColumns,
        string $displayName,
        bool $requireIaSemantics,
    ): void {
        $this->assertFileExists($path);
        $decoded = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($decoded);
        $this->assertSame(1, $decoded['version'] ?? null);
        $this->assertSame($displayName, $decoded['datasetDisplayName'] ?? null);

        $names = [];
        $synonymOwners = [];
        foreach ($decoded['fields'] ?? [] as $field) {
            $this->assertIsArray($field);
            $this->assertArrayHasKey('name', $field);
            $name = (string) $field['name'];
            $names[] = $name;

            $label = trim((string) ($field['label'] ?? ''));
            $this->assertNotSame('', $label, "GEN-37: falta label en {$name}");
            $this->assertNotSame($name, $label, "GEN-37: label no debe ser el name técnico ({$name})");

            if ($requireIaSemantics) {
                $this->assertNotSame('', trim((string) ($field['description'] ?? '')), "GEN-37 IA: falta description en {$name}");
                $this->assertIsArray($field['synonyms'] ?? null, "GEN-37 IA: falta synonyms en {$name}");
                $this->assertNotEmpty($field['synonyms'], "GEN-37 IA: synonyms vacío en {$name}");
            }

            foreach ($field['synonyms'] ?? [] as $syn) {
                $key = mb_strtolower(trim((string) $syn), 'UTF-8');
                if ($key === '') {
                    continue;
                }
                if (isset($synonymOwners[$key])) {
                    $this->fail("Sinónimo ambiguo \"{$syn}\" en {$synonymOwners[$key]} y {$name}");
                }
                $synonymOwners[$key] = $name;
            }
        }

        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $names, "Falta {$column} en {$path}");
        }
        $this->assertSameSize($expectedColumns, $names, "Cantidad de fields distinta al puerto en {$path}");
    }
}
