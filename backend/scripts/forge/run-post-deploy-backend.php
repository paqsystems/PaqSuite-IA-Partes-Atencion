<?php

declare(strict_types=1);

/**
 * Forge GEN-34 — por cada instalación SQL directo del proyecto:
 * tenant + migrate + installation:update + deploy-sp (host).
 */

require __DIR__ . '/../../vendor/autoload.php';

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @param  array<string, mixed>  $parameters
 */
function runArtisanLogged(ConsoleKernel $kernel, string $command, array $parameters = []): int
{
    $output = new BufferedOutput();
    echo '>>> php artisan '.$command."\n";
    $exitCode = $kernel->call($command, $parameters, $output);
    $text = trim($output->fetch());
    if ($text !== '') {
        echo $text."\n";
    }
    echo '<<< php artisan '.$command.' exit='.$exitCode."\n";

    return $exitCode;
}

$app = require __DIR__ . '/../../bootstrap/app.php';
/** @var ConsoleKernel $kernel */
$kernel = $app->make(ConsoleKernel::class);
$kernel->bootstrap();

require_once __DIR__ . '/post-deploy-installations.php';

$proyecto = trim(getenv('PAQSUITE_PROYECTO') ?: (string) config('paqsuite.proyecto', 'partesatencion'));
$targets = resolveForgePostDeployInstallationTargets($proyecto);

echo 'Post-deploy GEN-34: '.count($targets)." instalación(es) para {$proyecto}\n";

$hasInstallationUpdate = array_key_exists('paqsuite:installation:update', $kernel->all());
if (! defined('PAQSUITE_DEPLOY_SP_NO_AUTO_RUN')) {
    define('PAQSUITE_DEPLOY_SP_NO_AUTO_RUN', true);
}
require_once __DIR__ . '/../deploy-sp.php';

$targetIndex = 0;
$targetCount = count($targets);
$failFast = ! in_array(strtolower(trim(getenv('PAQSUITE_DEPLOY_FAIL_FAST') ?: '0')), ['0', 'false', 'no', 'off'], true);
/** @var list<string> */
$failedTenants = [];

foreach ($targets as $target) {
    $targetIndex++;
    $cliente = $target['cliente'];
    $proyectoTarget = $target['proyecto'];

    try {
        $instalacion = bootstrapTenantSqlsrvForInstallation($cliente, $proyectoTarget);
    } catch (Throwable $e) {
        $label = "{$cliente}/{$proyectoTarget}";
        fwrite(STDERR, "[FAIL {$label}] bootstrap: ".$e->getMessage()."\n");
        $failedTenants[] = $label.' (bootstrap)';
        if ($failFast) {
            exit(1);
        }

        continue;
    }

    if ($instalacion->isGatewayMode()) {
        echo "SKIP {$cliente}/{$proyectoTarget}: modo gateway (sin SQL directo en Forge).\n";
        continue;
    }

    $defaultConnection = (string) config('database.default');
    $databaseName = (string) config("database.connections.{$defaultConnection}.database");
    $label = "{$cliente}/{$proyectoTarget}";
    echo "\n=== [{$targetIndex}/{$targetCount}] {$label} → {$databaseName} ===\n";

    $tenantFailed = false;

    $migrateExit = runArtisanLogged($kernel, 'migrate', ['--force' => true]);
    if ($migrateExit !== 0) {
        $failedTenants[] = $label.' (migrate)';
        $tenantFailed = true;
    }

    if (! $tenantFailed && $hasInstallationUpdate) {
        $updateExit = runArtisanLogged($kernel, 'paqsuite:installation:update', [
            '--cliente' => $instalacion->cliente,
            '--proyecto' => $instalacion->proyecto,
            '--non-interactive' => true,
        ]);
        if ($updateExit !== 0) {
            $failedTenants[] = $label.' (installation:update)';
            $tenantFailed = true;
        }
    } elseif (! $tenantFailed) {
        echo "paqsuite:installation:update no disponible; omito GEN-34 CLI.\n";
    }

    if (! $tenantFailed) {
        try {
            deployHostStoredProcedures();
        } catch (Throwable $e) {
            fwrite(STDERR, "[FAIL {$label}] deploy-sp: ".$e->getMessage()."\n");
            $failedTenants[] = $label.' (deploy-sp)';
            $tenantFailed = true;
        }
    }

    if (! $tenantFailed) {
        $taskCatalogSeedExit = runArtisanLogged($kernel, 'db:seed', [
            '--class' => 'Database\\Seeders\\PqTaskProcessFrameworkSeeder',
            '--force' => true,
        ]);
        if ($taskCatalogSeedExit !== 0) {
            $failedTenants[] = $label.' (task-process-seed)';
            $tenantFailed = true;
        }
    }

    if ($tenantFailed) {
        fwrite(STDERR, "[FAIL {$label}] omito resto de pasos de este tenant; continúo con los siguientes.\n");
        if ($failFast) {
            exit(1);
        }
    }
}

if ($failedTenants !== []) {
    echo "\nPost-deploy GEN-34: tenants con error (".count($failedTenants)."):\n";
    foreach ($failedTenants as $failed) {
        echo "  - {$failed}\n";
    }
    echo "Deploy Forge debe revisarse (exit 1). PAQSUITE_DEPLOY_FAIL_FAST=1 aborta en el primer fallo.\n";
    exit(1);
}

echo "\nPost-deploy GEN-34: todos los tenants OK ({$targetCount}).\n";
exit(0);
