<?php

declare(strict_types=1);

/**
 * CI/local Partes: merge Framework + host manifiesto (GEN-34).
 *
 * Uso: php tools/validate-installation-manifest.php [--semver=1.3.17-beta.1]
 */

$backendRoot = dirname(__DIR__);
require $backendRoot.'/vendor/autoload.php';

use PaqSuite\LaravelCore\Installation\Manifest\ManifestValidationException;
use PaqSuite\LaravelCore\Installation\Manifest\ManifestValidationService;

$semver = '1.3.17-beta.1';
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--semver=')) {
        $semver = substr($arg, strlen('--semver='));
    }
}

$coreRoot = $backendRoot.'/vendor/paqsuite/laravel-core';
$frameworkManifest = $coreRoot.'/database/manifest/framework-'.$semver.'.json';
$hostManifest = $backendRoot.'/database/manifest/partesatencion-'.$semver.'.json';

if (! is_file($hostManifest)) {
    fwrite(STDERR, "Missing host manifest: {$hostManifest}\n");
    exit(1);
}

$service = new ManifestValidationService();

try {
    $merged = $service->validate(
        frameworkManifestPath: $frameworkManifest,
        packageRoot: $coreRoot,
        publishableGlobs: [],
        hostManifestPath: $hostManifest,
        scanFrameworkArtifacts: false,
    );
} catch (ManifestValidationException $exception) {
    fwrite(STDERR, $exception->getMessage()."\n");
    exit(1);
}

echo 'Manifest merge OK ('.$merged->manifestId.', '.count($merged->entries)." entries)\n";
