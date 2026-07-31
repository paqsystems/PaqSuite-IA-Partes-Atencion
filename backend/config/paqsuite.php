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
    | Locales soportados (GEN-02 lista blanca)
    |--------------------------------------------------------------------------
    */
    'supported_locales' => ['es', 'en', 'pt', 'fr', 'it'],

    /*
    |--------------------------------------------------------------------------
    | Grid layouts (GEN-11) — Fase 4; deshabilitado en Fase 1-2
    |--------------------------------------------------------------------------
    */
    'gridLayoutsEnabled' => env('PAQSUITE_GRID_LAYOUTS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Pivots (GEN-12) — solo env; default off
    |--------------------------------------------------------------------------
    */
    'pivotsEnabled' => filter_var(env('PIVOTS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'pivotLayoutsEnabled' => filter_var(env('PIVOT_LAYOUTS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

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
    ],

    /*
    |--------------------------------------------------------------------------
    | Mapa instalaciones (demo / desarrollo local)
    |--------------------------------------------------------------------------
    | Producción: sustituir InstalacionResolver por SQL EMPRESAS_CONEXION.
    */
    'instalaciones' => [
        'DEMO|partesatencion' => [
            'activo' => true,
            'nombre' => 'Partes Demo',
            'connection' => env('DB_CONNECTION', 'sqlsrv'),
            'singleCompanyId' => 1,
        ],
    ],
];
