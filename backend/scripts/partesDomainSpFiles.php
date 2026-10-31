<?php

declare(strict_types=1);

/**
 * SP de dominio Partes (CC informes, paquetes, integración maestros).
 * SoT scripts: backend/scripts/sql/partes/*.sql (excluye *-REFERENCE*).
 *
 * @return list<string> rutas absolutas, ordenadas por basename
 */
function partesDomainSpAbsolutePaths(string $backendRoot): array
{
    $pattern = rtrim(str_replace('\\', '/', $backendRoot), '/').'/scripts/sql/partes/*.sql';
    $matches = glob($pattern) ?: [];
    $out = [];
    foreach ($matches as $absolute) {
        $base = basename($absolute);
        if (stripos($base, '-REFERENCE') !== false) {
            continue;
        }
        $out[] = str_replace('\\', '/', $absolute);
    }
    sort($out);

    return $out;
}

/**
 * @return list<string> rutas relativas al backend (forward slashes)
 */
function partesDomainSpRelativePaths(string $backendRoot): array
{
    $normalizedRoot = rtrim(str_replace('\\', '/', $backendRoot), '/');
    $out = [];
    foreach (partesDomainSpAbsolutePaths($backendRoot) as $absolute) {
        $out[] = substr($absolute, strlen($normalizedRoot) + 1);
    }

    return $out;
}
