<?php

/**
 * Smoke: GetDesignerModel del sidecar debe exponer schema Paquete de horas (no id/label).
 *
 * Uso:
 *   php backend/scripts/dev/smoke-paquete-horas-designer-schema.php [baseUrl]
 *
 * baseUrl default: PAQSUITE_DX_REPORTING_SIDECAR_URL o http://127.0.0.1:5055
 */

$base = $argv[1] ?? getenv('PAQSUITE_DX_REPORTING_SIDECAR_URL') ?: 'http://127.0.0.1:5055';
$base = rtrim($base, '/');
$url = str_contains($base, '/DXXRD') ? $base : $base.'/DXXRD/GetDesignerModel';

$body = http_build_query(['reportUrl' => 'partes_paqueteHoras_principal']);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $body,
        'timeout' => 30,
        'ignore_errors' => true,
    ],
]);

$raw = @file_get_contents($url, false, $context);
if (! is_string($raw) || $raw === '') {
    fwrite(STDERR, "ERROR: sin respuesta de {$url}\n");
    exit(1);
}

$okName = str_contains($raw, 'Paquete de horas');
$badFallback = str_contains($raw, '"name":"Dataset"');

if (! $okName || $badFallback) {
    fwrite(STDERR, "FAIL: sidecar sin schema negocio (esperado name=Paquete de horas).\n");
    exit(1);
}

echo "OK smoke designer schema Paquete de horas ({$url})\n";
exit(0);
