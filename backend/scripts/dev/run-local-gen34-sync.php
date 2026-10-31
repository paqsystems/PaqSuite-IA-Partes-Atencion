<?php

declare(strict_types=1);

/**
 * Desarrollo local / PARTE O — tenant + migrate + installation:update + SP host.
 * Misma secuencia que Forge (run-post-deploy-backend.php) sobre la instalación PAQSUITE_CLIENTE/PROYECTO.
 *
 * Exit codes: 0 = OK · 1 = error · 2 = gateway (no SQL local; no es fallo para levantar el stack).
 */

require __DIR__ . '/../../vendor/autoload.php';

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @param  array<string, mixed>  $parameters
 */
function runLocalGen34Artisan(ConsoleKernel $kernel, string $command, array $parameters = []): int
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

require_once __DIR__ . '/../forge/bootstrap-tenant-sqlsrv.php';

$skipHostSp = in_array('--skip-host-sp', $argv, true);

try {
    $instalacion = bootstrapTenantSqlsrvFromEnv();
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage()."\n");
    exit(1);
}

if ($instalacion->isGatewayMode()) {
    fwrite(STDERR, "Instalación {$instalacion->cliente}/{$instalacion->proyecto} en modo gateway: no aplicar SQL directo en local.\n");
    fwrite(STDERR, "Levantá la app contra el backend desplegado o usá una fila EMPRESAS_CONEXION directo (runbook).\n");
    exit(2);
}

$defaultConnection = (string) config('database.default');
$databaseName = (string) config("database.connections.{$defaultConnection}.database");
echo "GEN-34 sync local → {$instalacion->cliente}/{$instalacion->proyecto} → {$databaseName}\n";

$migrateExit = runLocalGen34Artisan($kernel, 'migrate', ['--force' => true]);
if ($migrateExit !== 0) {
    exit($migrateExit);
}

$artisanCommands = $kernel->all();
if (! array_key_exists('paqsuite:installation:update', $artisanCommands)) {
    fwrite(STDERR, "paqsuite:installation:update no disponible; bump paqsuite/laravel-core (GEN-34).\n");
    exit(1);
}

$updateExit = runLocalGen34Artisan($kernel, 'paqsuite:installation:update', [
    '--cliente' => $instalacion->cliente,
    '--proyecto' => $instalacion->proyecto,
    '--non-interactive' => true,
]);
if ($updateExit !== 0) {
    exit($updateExit);
}

$statusExit = runLocalGen34Artisan($kernel, 'paqsuite:installation:status', [
    '--cliente' => $instalacion->cliente,
    '--proyecto' => $instalacion->proyecto,
]);
if ($statusExit !== 0) {
    exit($statusExit);
}

if (! $skipHostSp) {
    define('PAQSUITE_DEPLOY_SP_NO_AUTO_RUN', true);
    require __DIR__ . '/../deploy-sp.php';
    deployHostStoredProcedures();
}

echo "GEN-34 sync local OK\n";
exit(0);
