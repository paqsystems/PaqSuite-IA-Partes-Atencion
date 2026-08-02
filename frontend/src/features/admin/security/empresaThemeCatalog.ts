/**
 * Whitelist efectiva de temas DevExtreme (GEN-06-empresas, D1-06-26 / SPEC-001-19 §5.1).
 * Catálogo = temas predefinidos empaquetados en `devextreme/dist/css` (v26.1 del host).
 * Debe reflejar `App\Support\EmpresaThemeCatalog` (backend).
 */
export const EMPRESA_THEME_VALUES = [
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
] as const

export const EMPRESA_THEME_DEFAULT = 'generic.light'

/**
 * Nombres de tema DevExtreme: no se traducen (son nombres propios del catálogo
 * de la librería), se formatean a partir de la clave técnica `data-theme`.
 */
export function formatThemeLabel(theme: string): string {
  return theme
    .split('.')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ')
}

export type EmpresaThemeOption = { value: string; label: string }

export function getEmpresaThemeOptions(): EmpresaThemeOption[] {
  return EMPRESA_THEME_VALUES.map((value) => ({ value, label: formatThemeLabel(value) }))
}
