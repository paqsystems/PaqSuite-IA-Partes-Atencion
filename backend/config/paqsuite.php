<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenancy mode
    |--------------------------------------------------------------------------
    | single = una empresa (MONO canónico)
    | multi  = N empresas
    */
    'tenancy' => env('PAQSUITE_TENANCY', 'single'),

    /*
    |--------------------------------------------------------------------------
    | Database topology
    |--------------------------------------------------------------------------
    | unified = Dictionary y Operativa en la misma conexión
    | split   = Dictionary y Company separados
    */
    'db' => env('PAQSUITE_DB', 'unified'),

    /*
    |--------------------------------------------------------------------------
    | HTTP headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'cliente' => env('PAQSUITE_HEADER_CLIENTE', 'X-Paq-Cliente'),
        'company' => env('PAQSUITE_HEADER_COMPANY', 'X-Company-Id'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database driver reference
    |--------------------------------------------------------------------------
    | Diseño de referencia: sqlsrv. MySQL es perfil adaptable.
    */
    'databaseDriverReference' => env('PAQSUITE_DB_DRIVER_REFERENCE', 'sqlsrv'),

    /*
    |--------------------------------------------------------------------------
    | Proyecto (lookup instalación con X-Paq-Cliente)
    |--------------------------------------------------------------------------
    */
    'proyecto' => env('PAQSUITE_PROYECTO', 'partesatencion'),

    /*
    |--------------------------------------------------------------------------
    | Cliente de instalación (CLI / GEN-13)
    |--------------------------------------------------------------------------
    | En HTTP llega por X-Paq-Cliente. tasks:run-due no tiene header: usar este
    | código de EMPRESAS_CONEXION. Debe vivir en config para sobrevivir a
    | config:cache (env() queda vacío).
    */
    'clienteCodigo' => env('PAQSUITE_CLIENTE', ''),

    /*
    |--------------------------------------------------------------------------
    | Resolver de instalación (GEN-18)
    |--------------------------------------------------------------------------
    | config = mapa instalaciones (PHPUnit / fallback local)
    | sql    = PAQSYSTEMS.EMPRESAS_CONEXION vía SP (multi-cliente)
    */
    'instalacion' => [
        'resolver' => env('PAQSUITE_INSTALACION_RESOLVER', 'config'),
        'centralConnection' => env('PAQSUITE_CENTRAL_CONNECTION', 'paqsuite_central'),
        'procedure' => env('PAQSUITE_EMPRESAS_CONEXION_PROCEDURE', 'pq_sp_empresas_conexion_get'),
        'cacheTtlSeconds' => (int) env('PAQSUITE_INSTALACION_CACHE_TTL', 60),
        // Red de seguridad del host: mantener la aplicación de la conexión
        // fuera del SDK para conservar el control local aun después de un bump.
        // null/false permite Opción A (gateway); este host usa Opción B local.
        'applyDatabaseMiddleware' => env(
            'PAQSUITE_APPLY_INSTALACION_DB_MIDDLEWARE',
            \App\Http\Middleware\ApplyInstalacionDatabaseMiddleware::class,
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Locales soportados (GEN-02 lista blanca)
    |--------------------------------------------------------------------------
    */
    'supported_locales' => ['es', 'en', 'pt', 'fr', 'it'],

    /*
    |--------------------------------------------------------------------------
    | Grid layouts (GEN-11) — Fase 4; deshabilitado en Fase 1-2
    |--------------------------------------------------------------------------
    */
    'gridLayoutsEnabled' => env('PAQSUITE_GRID_LAYOUTS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Pivots (GEN-12) — solo env; default off
    |--------------------------------------------------------------------------
    */
    'pivotsEnabled' => filter_var(env('PIVOTS_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'pivotLayoutsEnabled' => filter_var(env('PIVOT_LAYOUTS_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Tareas programadas / backups (GEN-13)
    |--------------------------------------------------------------------------
    */
    'tasks' => [
        'middleware' => [
            'paqsuite.instalacion',
            'paqsuite.instalacion.db',
            'auth:sanctum',
        ],
        'scheduleTimezone' => env('PAQSUITE_TASKS_SCHEDULE_TIMEZONE', 'America/Argentina/Buenos_Aires'),
        'backup' => [
            'backend' => env('PAQSUITE_TASKS_BACKUP_BACKEND', 'rds'),
            's3ArnPrefix' => env('TASKS_BACKUP_S3_ARN_PREFIX', ''),
            'windowsService' => env('TASKS_BACKUP_WINDOWS_SERVICE', ''),
            'pollSeconds' => (int) env('TASKS_BACKUP_POLL_SECONDS', 5),
            'lockLeaseSeconds' => (int) env('TASKS_BACKUP_LOCK_LEASE_SECONDS', 900),
            'rdsTimeoutMinutes' => (int) env('TASKS_BACKUP_RDS_TIMEOUT_MINUTES', 120),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Chat Asistente IA (GEN-21) — timeout producto hacia el proveedor LLM
    |--------------------------------------------------------------------------
    */
    'chatAssistant' => [
        'llmTimeoutSeconds' => (int) env('PAQSUITE_CHAT_LLM_TIMEOUT_SECONDS', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Emisiones documentales GEN-15 — motor DevExpress (sidecar .NET)
    |--------------------------------------------------------------------------
    | mode: fake = MinimalDxReportingEngine (CI/tests); remote = sidecar /render
    */
    'emissions' => [
        'dxReporting' => [
            'mode' => env('PAQSUITE_EMISSION_DX_MODE', 'fake'),
            'serviceUrl' => env(
                'PAQSUITE_EMISSION_DX_SERVICE_URL',
                env('PAQSUITE_DX_REPORTING_SIDECAR_URL', 'http://127.0.0.1:5055').'/api/v1/dx-reporting',
            ),
            'apiKey' => env('PAQSUITE_EMISSION_DX_API_KEY', ''),
            'timeoutSeconds' => (int) env('PAQSUITE_EMISSION_DX_TIMEOUT', 60),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowlist de paths (multi) que no exigen X-Company-Id
    |--------------------------------------------------------------------------
    */
    'companyHeaderAllowlist' => [
        '/api/v1/health',
        '/api/v1/auth/login',
        '/api/v1/auth/logout',
        '/api/v1/auth/forgot-password',
        '/api/v1/auth/reset-password',
        '/api/v1/auth/change-password',
        '/api/v1/auth/me',
        '/api/v1/empresas',
        '/api/v1/user/preferences',
        '/api/v1/user/menu',
        '/api/v1/grid-layouts',
        '/api/v1/pivots',
        '/api/v1/pivot-layouts',
        '/api/v1/llm-credentials',
        '/api/v1/chat-assistant',
    ],

    /*
    |--------------------------------------------------------------------------
    | Mapa instalaciones (solo si PAQSUITE_INSTALACION_RESOLVER=config)
    |--------------------------------------------------------------------------
    | Fallback si PAQSUITE_INSTALACION_RESOLVER=config (PHPUnit / local sin PAQSYSTEMS).
    | Multidominio Forge: resolver=sql + EMPRESAS_CONEXION (no este mapa).
    */
    'instalaciones' => [
        'DEMO|partesatencion' => [
            'activo' => true,
            'nombre' => 'Partes Demo',
            'connection' => env('DB_CONNECTION', 'sqlsrv'),
            'singleCompanyId' => 1,
            'host' => env('DB_HOST'),
            'port' => (int) env('DB_PORT', 1433),
            'database_name' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'dictionary_database' => env('DB_DATABASE'),
        ],
        'PAQ|partesatencion' => [
            'activo' => true,
            'nombre' => 'Partes Paq',
            'connection' => env('DB_CONNECTION', 'sqlsrv'),
            'singleCompanyId' => 1,
            'host' => env('DB_HOST'),
            'port' => (int) env('DB_PORT', 1433),
            'database_name' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'dictionary_database' => env('DB_DATABASE'),
        ],
        'DEV|partesatencion' => [
            'activo' => true,
            'nombre' => 'Partes Dev',
            'connection' => env('DB_CONNECTION', 'sqlsrv'),
            'singleCompanyId' => 1,
            'host' => env('DB_HOST'),
            'port' => (int) env('DB_PORT', 1433),
            'database_name' => env('DB_COMPANY_DATABASE', env('DB_DATABASE')),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'dictionary_database' => env('DB_COMPANY_DATABASE', env('DB_DATABASE')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | GEN-34 — manifiesto instalación (host)
    |--------------------------------------------------------------------------
    */
    'installation' => [
        'hostManifestPath' => env(
            'PAQSUITE_HOST_MANIFEST',
            database_path('manifest/partesatencion-1.3.18-beta.13.json')
        ),
        'frameworkPackageRoot' => base_path('vendor/paqsuite/laravel-core'),
    ],

    'menuI18n' => [
        'enabled' => filter_var(env('PAQSUITE_MENU_I18N_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'installGate' => filter_var(env('PAQSUITE_MENU_I18N_INSTALL_GATE', false), FILTER_VALIDATE_BOOLEAN),
        'driver' => env('PAQSUITE_MENU_I18N_DRIVER', 'productI18nTs'),
        'repoRoot' => env('PAQSUITE_MENU_I18N_REPO_ROOT', dirname(base_path())),
        'productI18nTsPath' => env(
            'PAQSUITE_MENU_I18N_TS_PATH',
            'frontend/src/features/auth/productI18n.ts'
        ),
        'jsonLocales' => [],
    ],
];
