/**
 * Catálogo A1 (GEN-06 / SPEC-001-19): reexport del SDK — no duplicar whitelist ni labels.
 * Persistencia API: stock DevExtreme vía `mapThemeForApiPersistence` (SDK EmpresasAdmin).
 */
export {
  buildEmpresaThemeOptions,
  defaultEmpresaTheme as EMPRESA_THEME_DEFAULT,
  empresaThemeKeys as EMPRESA_THEME_VALUES,
  mapThemeForApiPersistence,
  normalizeThemeFromApi,
} from '@paqsuite/react-core'

import { buildEmpresaThemeOptions } from '@paqsuite/react-core'

export type EmpresaThemeOption = { value: string; label: string }

/** @deprecated Usar `buildEmpresaThemeOptions(t)` del SDK. */
export function getEmpresaThemeOptions(): EmpresaThemeOption[] {
  return buildEmpresaThemeOptions().map((option) => ({
    value: option.value,
    label: option.text,
  }))
}
