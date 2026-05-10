<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modo de instalación (MONO | MULTI)
    |--------------------------------------------------------------------------
    | Debe alinearse con docs/_base/00-inicio-arquitectura.md §1.
    */
    'installation_mode' => env('PAQSUITE_INSTALLATION_MODE', env('INSTALLATION_MODE', 'MONO')),
];
