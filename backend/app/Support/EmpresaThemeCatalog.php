<?php

namespace App\Support;

/**
 * Whitelist de valores persistidos en `pq_empresa.theme` (stock DevExtreme).
 * UI A1 del SDK usa claves `paqsuite.*`; el host acepta ambas en entrada y
 * normaliza a stock al persistir. En respuestas del ABM admin se expone `paqsuite.*`
 * para que el SelectBox del SDK (react-core 2.4.17) coincida con las opciones.
 *
 * Catálogo FE: `frontend/src/theme/sdkEmpresaThemeCompat.ts` (mismo puente).
 */
final class EmpresaThemeCatalog
{
    public const DEFAULT_THEME = 'generic.light';

    public const DEFAULT_UI_THEME = 'paqsuite.light.generic';

    /**
     * Puente A1: clave UI `paqsuite.*` → stock DevExtreme persistido.
     *
     * @var array<string, string>
     */
    private const PAQSUITE_TO_STOCK = [
        'paqsuite.light.generic' => 'generic.light',
        'paqsuite.light.compact' => 'generic.light.compact',
        'paqsuite.light.material' => 'material.blue.light',
        'paqsuite.orange.generic' => 'material.orange.light',
        'paqsuite.orange.compact' => 'material.orange.light.compact',
        'paqsuite.orange.material' => 'material.orange.light',
        'paqsuite.rose.generic' => 'generic.softblue',
        'paqsuite.rose.compact' => 'generic.softblue.compact',
        'paqsuite.rose.material' => 'material.purple.light',
        'paqsuite.greenmist.generic' => 'generic.greenmist',
        'paqsuite.greenmist.compact' => 'generic.greenmist.compact',
        'paqsuite.greenmist.material' => 'material.teal.light',
        'paqsuite.dark.generic' => 'generic.dark',
        'paqsuite.dark.compact' => 'generic.dark.compact',
        'paqsuite.dark.material' => 'material.blue.dark',
        'paqsuite.blue.generic' => 'generic.darkmoon',
        'paqsuite.blue.compact' => 'generic.darkmoon.compact',
        'paqsuite.blue.material' => 'material.blue.dark',
        'paqsuite.violet.generic' => 'generic.darkviolet',
        'paqsuite.violet.compact' => 'generic.darkviolet.compact',
        'paqsuite.violet.material' => 'material.purple.dark',
        'paqsuite.burgundy.generic' => 'generic.carmine',
        'paqsuite.burgundy.compact' => 'generic.carmine.compact',
        'paqsuite.burgundy.material' => 'material.orange.dark',
    ];

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

    /**
     * Valores aceptados en body PUT (stock DX + claves UI del SDK).
     *
     * @return list<string>
     */
    public static function acceptedInputValues(): array
    {
        return array_values(array_unique([
            ...self::values(),
            ...array_keys(self::PAQSUITE_TO_STOCK),
        ]));
    }

    public static function isValid(string $theme): bool
    {
        return in_array($theme, self::values(), true);
    }

    public static function isAcceptedInput(string $theme): bool
    {
        return in_array($theme, self::acceptedInputValues(), true);
    }

    /**
     * Normaliza entrada SDK/API a valor persistido (stock DX).
     */
    public static function normalizeForPersistence(?string $theme): string
    {
        if ($theme === null || $theme === '') {
            return self::DEFAULT_THEME;
        }

        if (isset(self::PAQSUITE_TO_STOCK[$theme])) {
            return self::PAQSUITE_TO_STOCK[$theme];
        }

        if (self::isValid($theme)) {
            return $theme;
        }

        return self::DEFAULT_THEME;
    }

    /**
     * Expone tema para UI A1 del SDK (`paqsuite.*`).
     */
    public static function toUiTheme(?string $theme): string
    {
        if ($theme === null || $theme === '') {
            return self::DEFAULT_UI_THEME;
        }

        if (isset(self::PAQSUITE_TO_STOCK[$theme])) {
            return $theme;
        }

        $stockToPaqsuite = array_flip(self::PAQSUITE_TO_STOCK);
        // Preferir la primera clave paqsuite que mapea a este stock (valores únicos en el puente).
        if (isset($stockToPaqsuite[$theme])) {
            return $stockToPaqsuite[$theme];
        }

        // Stock sin puente 1:1 (p.ej. fluent.*, contrast): mantener stock.
        if (self::isValid($theme)) {
            return $theme;
        }

        return self::DEFAULT_UI_THEME;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public static function mapItemThemeForUi(array $item): array
    {
        if (array_key_exists('theme', $item)) {
            $item['theme'] = self::toUiTheme(
                $item['theme'] !== null ? (string) $item['theme'] : null
            );
        }

        return $item;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    public static function mapItemsThemeForUi(array $items): array
    {
        return array_map(
            static fn (array $item): array => self::mapItemThemeForUi($item),
            $items
        );
    }
}
