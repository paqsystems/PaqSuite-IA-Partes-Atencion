<?php

/**
 * Corpus Asistente IA (TR-008) — raíz Partes + GEN.
 * El listado de archivos se resuelve en ManifestChatCorpusProvider (no en boot de config).
 *
 * GEN: override con PAQSUITE_GEN_DOCS_ROOT; si vacío, corpus empaquetado en
 * paqsuite/laravel-core (resources/manual-usuario-gen, GenManualDocsRoot ≥ 1.3.3).
 */
$genFromPackage = class_exists(\PaqSuite\LaravelCore\ChatAssistant\GenManualDocsRoot::class)
    ? \PaqSuite\LaravelCore\ChatAssistant\GenManualDocsRoot::pathIfPresent()
    : null;

return [
    'maxChars' => 28 * 1024,
    'partesManualRoot' => env(
        'PAQSUITE_PARTES_MANUAL_ROOT',
        base_path('../docs/99-manual-usuario')
    ),
    'genDocsRoot' => env('PAQSUITE_GEN_DOCS_ROOT') ?: $genFromPackage,
    // Override explícito (lab). Vacío = usar genDocsRoot (paquete o env).
    'genDocsRootFallback' => env('PAQSUITE_GEN_DOCS_ROOT_FALLBACK', ''),
];
