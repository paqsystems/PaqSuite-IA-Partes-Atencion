<?php

declare(strict_types=1);

/**
 * GEN-34 fase 5 — regenera manifiesto producto Partes (host).
 *
 * Uso (desde backend/): php tools/build-installation-manifest.php [--semver=1.3.17-beta.1]
 */

$backendRoot = dirname(__DIR__);

/** @var list<string> */
$omitFrameworkEntryIds = [
    'fw-sp-pq_sp_admin_empresa_tango_dictionary',
];

function resolveLaravelCoreSemver(string $backendRoot): string
{
    $composerPath = $backendRoot.'/composer.json';
    $json = file_get_contents($composerPath);
    if ($json === false) {
        throw new RuntimeException('Cannot read composer.json');
    }
    $data = json_decode($json, true);
    $semver = is_array($data) ? (string) ($data['require']['paqsuite/laravel-core'] ?? '') : '';
    if ($semver === '') {
        throw new RuntimeException('composer.json require paqsuite/laravel-core missing');
    }

    return $semver;
}

$semver = resolveLaravelCoreSemver($backendRoot);
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--semver=')) {
        $semver = substr($arg, strlen('--semver='));
    }
}

$coreRoot = $backendRoot.'/vendor/paqsuite/laravel-core';
$coreSpBasenames = [];
if (is_dir($coreRoot)) {
    foreach (glob($coreRoot.'/database/sp/**/*.sql') ?: [] as $absolute) {
        $coreSpBasenames[basename($absolute)] = true;
    }
}

$excludeSpBasenames = [
    'seed_empresas_conexion_partesatencion_demo.sql',
    'ensure_paqsystems_instalacion_partesatencion.sql',
    'pq_sp_empresas_conexion_get.sql',
];

/**
 * @return list<string> paths relative to backend root (forward slashes)
 */
function scanPublishable(string $root, string $pattern): array
{
    $normalizedRoot = rtrim(str_replace('\\', '/', $root), '/');
    $fullPattern = $normalizedRoot.'/'.ltrim(str_replace('\\', '/', $pattern), '/');
    $matches = glob($fullPattern) ?: [];
    $out = [];
    foreach ($matches as $absolute) {
        $relative = substr(str_replace('\\', '/', $absolute), strlen($normalizedRoot) + 1);
        $out[] = $relative;
    }
    sort($out);

    return $out;
}

function hashFile(string $absolute): string
{
    $content = file_get_contents($absolute);
    if ($content === false) {
        throw new RuntimeException('Cannot read: '.$absolute);
    }

    return hash('sha256', str_replace(["\r\n", "\r"], "\n", $content));
}

require_once $backendRoot.'/scripts/partesDomainSpFiles.php';

$migrationPaths = scanPublishable($backendRoot, 'database/migrations/*.php');
$spPaths = scanPublishable($backendRoot, 'database/sp/*.sql');
$partesDomainSpPaths = partesDomainSpRelativePaths($backendRoot);

$entries = [];

foreach ($migrationPaths as $path) {
    $basename = basename($path);
    $stem = pathinfo($basename, PATHINFO_FILENAME);
    $absolute = $backendRoot.'/'.$path;
    $entries[] = [
        'id' => 'partes-migration-'.$stem,
        'kind' => 'migration',
        'target' => 'dictionary',
        'path' => $path,
        'checksum' => hashFile($absolute),
        'dependsOn' => [],
    ];
}

foreach (array_merge($spPaths, $partesDomainSpPaths) as $path) {
    $basename = basename($path);
    if (isset($coreSpBasenames[$basename]) || in_array($basename, $excludeSpBasenames, true)) {
        continue;
    }

    $stem = pathinfo($basename, PATHINFO_FILENAME);
    $absolute = $backendRoot.'/'.$path;
    $entries[] = [
        'id' => 'partes-sp-'.$stem,
        'kind' => 'sql',
        'target' => str_starts_with($stem, 'pq_sp_task_execution_') ? 'both' : 'dictionary',
        'path' => $path,
        'checksum' => hashFile($absolute),
        'dependsOn' => [],
    ];
}

// GEN-15 / CC#13 — Field List schemas (DIC) para el diseñador DX.
$designerSchemaPaths = scanPublishable($backendRoot, 'database/emission-designer-schemas/*.json');
foreach ($designerSchemaPaths as $path) {
    $basename = basename($path);
    if (str_ends_with($basename, '.port-names.json') || strcasecmp($basename, 'README.md') === 0) {
        continue;
    }
    if (! str_ends_with($basename, '.json')) {
        continue;
    }

    $processCode = pathinfo($basename, PATHINFO_FILENAME);
    $absolute = $backendRoot.'/'.$path;
    $portNamesRel = 'database/emission-designer-schemas/'.$processCode.'.port-names.json';
    $entry = [
        'id' => 'partes-emission-designer-schema-'.$processCode,
        'kind' => 'emissionDesignerSchema',
        'target' => 'dictionary',
        'path' => $path,
        'checksum' => hashFile($absolute),
        'dependsOn' => [
            'fw-migration-2026_11_03_000041_create_pq_emission_designer_schemas_table',
            'fw-sp-pq_sp_emission_designer_schema',
        ],
        'processCode' => $processCode,
    ];
    if (is_readable($backendRoot.'/'.$portNamesRel)) {
        $entry['emissionPortSchemaRef'] = $portNamesRel;
    }
    $entries[] = $entry;
}

usort($entries, static fn (array $a, array $b) => strcmp($a['id'], $b['id']));

$document = [
    'manifestId' => 'partesatencion-'.$semver,
    'semver' => $semver,
    'omitFrameworkEntryIds' => $omitFrameworkEntryIds,
    'entries' => $entries,
];

$outDir = $backendRoot.'/database/manifest';
if (! is_dir($outDir)) {
    mkdir($outDir, 0775, true);
}

$outPath = $outDir.'/partesatencion-'.$semver.'.json';
file_put_contents(
    $outPath,
    json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
);

echo 'Wrote '.$outPath.' ('.count($entries)." entries)\n";
