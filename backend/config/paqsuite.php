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
    | Chat Asistente IA (GEN-21) — timeout producto hacia el proveedor LLM
    |--------------------------------------------------------------------------
    */
    'chatAssistant' => [
        'llmTimeoutSeconds' => (int) env('PAQSUITE_CHAT_LLM_TIMEOUT_SECONDS', 60),
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
    ],
];
