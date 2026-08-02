<?php

namespace App\Support;

/**
 * Whitelist efectiva de temas DevExtreme (GEN-06-empresas, D1-06-26 / SPEC-001-19 §5.1).
 * Catálogo = temas predefinidos empaquetados en `devextreme/dist/css` (v26.1 del host).
 * Debe reflejar `frontend/.../empresaThemeCatalog.ts`.
 */
final class EmpresaThemeCatalog
{
    public const DEFAULT_THEME = 'generic.light';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [
            // Generic
            'generic.light',
            'generic.dark',
            'generic.carmine',
            'generic.softblue',
            'generic.darkmoon',
            'generic.darkviolet',
            'generic.greenmist',
            'generic.contrast',
            // Generic Compact
            'generic.light.compact',
            'generic.dark.compact',
            'generic.carmine.compact',
            'generic.softblue.compact',
            'generic.darkmoon.compact',
            'generic.darkviolet.compact',
            'generic.greenmist.compact',
            'generic.contrast.compact',
            // Material
            'material.blue.light',
            'material.blue.dark',
            'material.lime.light',
            'material.lime.dark',
            'material.orange.light',
            'material.orange.dark',
            'material.purple.light',
            'material.purple.dark',
            'material.teal.light',
            'material.teal.dark',
            // Material Compact
            'material.blue.light.compact',
            'material.blue.dark.compact',
            'material.lime.light.compact',
            'material.lime.dark.compact',
            'material.orange.light.compact',
            'material.orange.dark.compact',
            'material.purple.light.compact',
            'material.purple.dark.compact',
            'material.teal.light.compact',
            'material.teal.dark.compact',
            // Fluent
            'fluent.blue.light',
            'fluent.blue.dark',
            'fluent.saas.light',
            'fluent.saas.dark',
            // Fluent Compact
            'fluent.blue.light.compact',
            'fluent.blue.dark.compact',
            'fluent.saas.light.compact',
            'fluent.saas.dark.compact',
        ];
    }

    public static function isValid(string $theme): bool
    {
        return in_array($theme, self::values(), true);
    }
}
