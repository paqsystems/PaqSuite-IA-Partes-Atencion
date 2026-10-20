<?php

/**
 * Router para `php -S 127.0.0.1:8010 e2e-server.php`.
 * Fuerza sqlite + resolver config (el :8000 local suele ser otro producto).
 */

putenv('APP_ENV=e2e');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=C:/Programacion/PaqSuite-IA-Partes-Atencion/backend/database/e2e.sqlite');
putenv('PAQSUITE_INSTALACION_RESOLVER=config');
$_ENV['APP_ENV'] = 'e2e';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = 'C:/Programacion/PaqSuite-IA-Partes-Atencion/backend/database/e2e.sqlite';
$_ENV['PAQSUITE_INSTALACION_RESOLVER'] = 'config';
$_SERVER['APP_ENV'] = 'e2e';
$_SERVER['DB_CONNECTION'] = 'sqlite';
$_SERVER['DB_DATABASE'] = 'C:/Programacion/PaqSuite-IA-Partes-Atencion/backend/database/e2e.sqlite';
$_SERVER['PAQSUITE_INSTALACION_RESOLVER'] = 'config';

if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/e2e-env')) {
    header('Content-Type: application/json');
    echo json_encode([
        'getenvDb' => getenv('DB_CONNECTION'),
        'envDb' => $_ENV['DB_CONNECTION'] ?? null,
        'resolver' => getenv('PAQSUITE_INSTALACION_RESOLVER'),
        'database' => getenv('DB_DATABASE'),
    ]);
    return;
}

require __DIR__.'/public/index.php';
