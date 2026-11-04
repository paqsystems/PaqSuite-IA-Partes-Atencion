<?php

namespace Tests\Unit\Emissions;

use App\Services\Emissions\PartesPaqueteHorasEmissionPort;
use Tests\TestCase;

final class PaqueteHorasEmissionSchemaJsonTest extends TestCase
{
    /** @return list<string> */
    private function schemaFileNames(): array
    {
        return [
            'partes_paqueteHoras_principal.json',
            'partes.informes.paqueteHoras.json',
        ];
    }

    public function test_schema_json_incluye_columnas_del_puerto(): void
    {
        foreach ($this->schemaFileNames() as $fileName) {
            $this->assertSchemaFileMatchesPort(resource_path('emissions/schemas/'.$fileName));
        }
    }

    private function assertSchemaFileMatchesPort(string $path): void
    {
        $this->assertFileExists($path);

        $decoded = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($decoded);

        $columnNames = [];
        foreach ($decoded['columns'] ?? [] as $column) {
            if (is_array($column) && isset($column['name'])) {
                $columnNames[] = (string) $column['name'];
            }
        }

        foreach (PartesPaqueteHorasEmissionPort::designDatasetColumnNames() as $expected) {
            $this->assertContains($expected, $columnNames, "Falta columna {$expected} en {$path}");
        }

        $this->assertSame('Paquete de horas', $decoded['name'] ?? null);
    }
}
