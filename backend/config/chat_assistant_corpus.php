<?php

/**
 * Corpus Asistente IA (TR-008) — raíz Partes + GEN.
 * El listado de archivos se resuelve en ManifestChatCorpusProvider (no en boot de config).
 */
return [
    'maxChars' => 28 * 1024,
    'partesManualRoot' => env(
        'PAQSUITE_PARTES_MANUAL_ROOT',
        base_path('../docs/99-manual-usuario')
    ),
    'genDocsRoot' => env('PAQSUITE_GEN_DOCS_ROOT'),
    'genDocsRootFallback' => base_path('../../PaqSuite-IA-FRAMEWORK/docs/99-manual-usuario'),
];
