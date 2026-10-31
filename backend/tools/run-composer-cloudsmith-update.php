<?php

declare(strict_types=1);

$authPath = dirname(__DIR__).'/auth.json';
if (! is_file($authPath)) {
    fwrite(STDERR, "ERROR: auth.json no encontrado\n");
    exit(1);
}

$auth = json_decode((string) file_get_contents($authPath), true);
$token = trim((string) ($auth['bearer']['composer.cloudsmith.io'] ?? ''));
if ($token === '') {
    $token = trim((string) ($auth['http-basic']['dl.cloudsmith.io']['password'] ?? ''));
}
if ($token === '') {
    fwrite(STDERR, "ERROR: token Cloudsmith no encontrado en auth.json\n");
    exit(1);
}

putenv('CLOUDSMITH_READ_TOKEN='.$token);
passthru('php '.escapeshellarg(__DIR__.'/composer-cloudsmith-update-temp.php'), $exitCode);
exit($exitCode);
