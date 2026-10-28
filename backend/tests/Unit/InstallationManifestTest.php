<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class InstallationManifestTest extends TestCase
{
    public function testHostManifestFileExistsAndIsValidJson(): void
    {
        $path = dirname(__DIR__, 2).'/database/manifest/partesatencion-1.3.17-beta.1.json';
        $this->assertFileExists($path);

        $data = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($data);
        $this->assertSame('partesatencion-1.3.17-beta.1', $data['manifestId'] ?? null);
        $this->assertNotEmpty($data['entries'] ?? null);
    }

    public function testValidateScriptWhenCoreSupportsGen34(): void
    {
        if (! class_exists(\PaqSuite\LaravelCore\Installation\Manifest\ManifestValidationService::class)) {
            $this->markTestSkipped('Bump paqsuite/laravel-core to 1.3.17-beta.1+ with GEN-34 manifest support');
        }

        $backendRoot = dirname(__DIR__, 2);
        $command = 'php '.escapeshellarg($backendRoot.'/tools/validate-installation-manifest.php').' --semver=1.3.17-beta.1';
        exec($command, $output, $exitCode);

        $this->assertSame(0, $exitCode, implode("\n", $output));
    }
}
