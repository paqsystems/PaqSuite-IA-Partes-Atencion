<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class InstallationManifestTest extends TestCase
{
    private function laravelCoreSemver(): string
    {
        $composerPath = dirname(__DIR__, 2).'/composer.json';
        $data = json_decode((string) file_get_contents($composerPath), true);

        return (string) ($data['require']['paqsuite/laravel-core'] ?? '');
    }

    public function testHostManifestFileExistsAndIsValidJson(): void
    {
        $semver = $this->laravelCoreSemver();
        $this->assertNotSame('', $semver);

        $path = dirname(__DIR__, 2).'/database/manifest/partesatencion-'.$semver.'.json';
        $this->assertFileExists($path);

        $data = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($data);
        $this->assertSame('partesatencion-'.$semver, $data['manifestId'] ?? null);
        $this->assertNotEmpty($data['entries'] ?? null);
        $this->assertContains(
            'fw-sp-pq_sp_admin_empresa_tango_dictionary',
            $data['omitFrameworkEntryIds'] ?? [],
        );

        $entryIds = array_column($data['entries'] ?? [], 'id');
        $this->assertContains('partes-sp-pq_sp_partes_informe_paquete_horas', $entryIds);
    }

    public function testValidateScriptWhenCoreSupportsGen34(): void
    {
        if (! class_exists(\PaqSuite\LaravelCore\Installation\Manifest\ManifestValidationService::class)) {
            $this->markTestSkipped('Bump paqsuite/laravel-core to 1.3.17-beta.1+ with GEN-34 manifest support');
        }

        $backendRoot = dirname(__DIR__, 2);
        $command = 'php '.escapeshellarg($backendRoot.'/tools/validate-installation-manifest.php');
        exec($command, $output, $exitCode);

        $this->assertSame(0, $exitCode, implode("\n", $output));
    }
}
