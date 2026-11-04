<?php

declare(strict_types=1);

/**
 * Smoke GEN-15: emisión PDF con dataset vacío (0 filas) por cada proceso Partes con Emitir en menú.
 *
 * Uso:
 *   php backend/scripts/dev/smoke-emissions-empty-report.php [baseUrl] [tenant] [user] [pass]
 *
 * Env: SMOKE_API_BASE, SMOKE_TENANT, SMOKE_USER, SMOKE_PASS
 */

$argv = $_SERVER['argv'] ?? [];
$base = rtrim((string) ($argv[1] ?? (getenv('SMOKE_API_BASE') ?: 'http://127.0.0.1:8010/api/v1')), '/');
$tenant = (string) ($argv[2] ?? (getenv('SMOKE_TENANT') ?: (getenv('PAQSUITE_CLIENTE') ?: 'DEMO')));
$user = (string) ($argv[3] ?? (getenv('SMOKE_USER') ?: 'admin'));
$pass = (string) ($argv[4] ?? (getenv('SMOKE_PASS') ?: 'Paqsystems'));

const GHOST_TAREA_ID = 9999999;

/** @return array<string, mixed> */
function request(string $method, string $url, array $headers, ?array $body = null): array
{
    $headerLines = [];
    foreach ($headers as $key => $value) {
        $headerLines[] = "{$key}: {$value}";
    }
    $opts = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headerLines),
            'ignore_errors' => true,
        ],
    ];
    if ($body !== null) {
        $opts['http']['content'] = json_encode($body, JSON_UNESCAPED_UNICODE);
    }
    $raw = file_get_contents($url, false, stream_context_create($opts));
    if ($raw === false) {
        return ['_httpError' => 'request failed'];
    }

    return json_decode($raw, true) ?? ['_parseError' => $raw];
}

$headers = [
    'X-Paq-Cliente' => $tenant,
    'Content-Type' => 'application/json',
];

$login = request('POST', "{$base}/auth/login", $headers, [
    'usuario' => $user,
    'password' => $pass,
]);
if ((int) ($login['error'] ?? 1) !== 0) {
    fwrite(STDERR, 'LOGIN_FAIL: '.json_encode($login, JSON_UNESCAPED_UNICODE)."\n");
    exit(1);
}

$token = (string) ($login['resultado']['token'] ?? '');
$authHeaders = $headers + ['Authorization' => 'Bearer '.$token];
$hoy = (new DateTimeImmutable('now'))->format('Y-m-d');

$processes = [
    [
        'processCode' => 'partes.informes.consultaDetallada',
        'hostContext' => [
            'fechaDesde' => $hoy,
            'fechaHasta' => $hoy,
            'clienteId' => null,
            'usuarioId' => null,
            'tipoTareaId' => null,
            'estadoCerrado' => 'todas',
            'selectedTareaIds' => [GHOST_TAREA_ID],
        ],
    ],
];

$clientes = request('GET', "{$base}/partes/clientes?page=1&pageSize=1", $authHeaders);
$clienteId = (int) ($clientes['resultado']['items'][0]['id'] ?? 0);
if ($clienteId <= 0) {
    fwrite(STDERR, "SMOKE_SKIP paqueteHoras: sin clientes en catálogo\n");
} else {
    $processes[] = [
        'processCode' => 'partes.informes.paqueteHoras',
        'hostContext' => [
            'fechaDesde' => $hoy,
            'fechaHasta' => $hoy,
            'clienteId' => $clienteId,
            'tipoClienteId' => null,
            'selectedTareaIds' => [GHOST_TAREA_ID],
        ],
    ];
}

/** @return int|null */
function resolveEmittableReportId(string $base, array $authHeaders, string $processCode): ?int
{
    $report = request('GET', "{$base}/emissions/design/processes/{$processCode}/reports", $authHeaders);
    foreach ($report['resultado']['items'] ?? [] as $item) {
        if (! ($item['isStandard'] ?? true) && (int) ($item['isPrincipal'] ?? $item['is_principal'] ?? 0) === 1) {
            $id = (int) ($item['id'] ?? 0);

            return $id > 0 ? $id : null;
        }
    }
    foreach ($report['resultado']['items'] ?? [] as $item) {
        if (! ($item['isStandard'] ?? true)) {
            $id = (int) ($item['id'] ?? 0);

            return $id > 0 ? $id : null;
        }
    }

    return null;
}

$summary = ['tenant' => $tenant, 'base' => $base, 'date' => $hoy, 'processes' => []];
$failed = false;

foreach ($processes as $spec) {
    $processCode = $spec['processCode'];
    $body = [
        'processCode' => $processCode,
        'channel' => 'pdf',
        'mode' => 'consolidated',
        'hostContext' => $spec['hostContext'],
    ];
    $reportId = resolveEmittableReportId($base, $authHeaders, $processCode);
    if ($reportId !== null) {
        $body['reportId'] = $reportId;
    }

    $job = request('POST', "{$base}/emissions/jobs", $authHeaders, $body);
    $row = [
        'processCode' => $processCode,
        'error' => $job['error'] ?? null,
        'respuesta' => $job['respuesta'] ?? null,
        'status' => $job['resultado']['item']['status'] ?? null,
        'datasetRowCount' => $job['resultado']['item']['datasetRowCount'] ?? null,
        'jobId' => $job['resultado']['item']['jobId'] ?? null,
    ];

    if ((int) ($job['error'] ?? 1) === 0 && ($row['status'] ?? '') === 'done') {
        $jobId = (string) ($row['jobId'] ?? '');
        if ($jobId !== '') {
            $downloadHeaders = $authHeaders;
            unset($downloadHeaders['Content-Type']);
            $dl = request('GET', "{$base}/emissions/jobs/{$jobId}/download", $downloadHeaders);
            $row['downloadError'] = $dl['error'] ?? null;
            $row['downloadOk'] = ! isset($dl['error']) || (int) $dl['error'] === 0;
        }
    } else {
        $failed = true;
    }

    $summary['processes'][] = $row;
}

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
exit($failed ? 1 : 0);
