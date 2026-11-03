<?php

declare(strict_types=1);

$argv = $_SERVER['argv'] ?? [];
$base = $argv[1] ?? (getenv('SMOKE_API_BASE') ?: 'http://127.0.0.1:8010/api/v1');
$tenant = $argv[2] ?? (getenv('SMOKE_TENANT') ?: 'DEV');
$user = $argv[3] ?? (getenv('SMOKE_USER') ?: 'PQ');
$pass = $argv[4] ?? (getenv('SMOKE_PASS') ?: 'Paqsystems26*');

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

$checks = [
    'clientes_all' => "{$base}/partes/clientes?page=1&pageSize=5",
    'clientes_nombre_ankas' => "{$base}/partes/clientes?page=1&pageSize=50&nombre=ANKAS",
    'asistentes_nombre_ankas' => "{$base}/partes/asistentes?page=1&pageSize=50&nombre=ANKAS",
    'tipos_cliente_desc' => "{$base}/partes/tipos-cliente?page=1&pageSize=50&descripcion=Premium",
    'tipos_tarea_desc' => "{$base}/partes/tipos-tarea?page=1&pageSize=50&descripcion=General",
];

$hoy = (new DateTimeImmutable('now'))->format('Y-m-d');
$checks['masivo_cliente_nombre'] = "{$base}/partes/tareas?fechaDesde={$hoy}&fechaHasta={$hoy}&page=1&pageSize=20&clienteNombre=ANKAS";

$out = ['tenant' => $tenant, 'base' => $base, 'user' => $user, 'results' => []];
foreach ($checks as $key => $url) {
    $json = request('GET', $url, $authHeaders);
    $items = $json['resultado']['items'] ?? [];
    $out['results'][$key] = [
        'error' => $json['error'] ?? null,
        'total' => $json['resultado']['total'] ?? null,
        'items' => is_array($items) ? count($items) : 0,
    ];
}

$design = request('GET', "{$base}/emissions/design/processes/partes.informes.paqueteHoras/reports", $authHeaders);
$out['design_paquete_horas'] = [
    'error' => $design['error'] ?? null,
    'firstCode' => $design['resultado']['items'][0]['code'] ?? null,
];

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";
