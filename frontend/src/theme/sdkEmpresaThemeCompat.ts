import * as paqsuiteReactCore from '@paqsuite/react-core'
import { defaultEmpresaTheme, empresaThemeKeys } from '@paqsuite/react-core'

/**
 * Compat GEN-19: react-core 2.4.17 tiene el puente internamente pero no lo reexporta.
 * Preferir exports del SDK cuando existan (≥ 2.4.20).
 */
type SdkThemeExports = {
  paqsuiteDxStockBridge?: Readonly<Record<string, string>>
  normalizeThemeFromApi?: (theme: string | null | undefined) => string
  mapThemeForApiPersistence?: (theme: string) => string
}

const sdkTheme = paqsuiteReactCore as SdkThemeExports

const paqsuiteDxStockBridgeFallback: Readonly<Record<string, string>> = {
  'paqsuite.light.generic': 'generic.light',
  'paqsuite.light.compact': 'generic.light.compact',
  'paqsuite.light.material': 'material.blue.light',
  'paqsuite.orange.generic': 'material.orange.light',
  'paqsuite.orange.compact': 'material.orange.light.compact',
  'paqsuite.orange.material': 'material.orange.light',
  'paqsuite.rose.generic': 'generic.softblue',
  'paqsuite.rose.compact': 'generic.softblue.compact',
  'paqsuite.rose.material': 'material.purple.light',
  'paqsuite.greenmist.generic': 'generic.greenmist',
  'paqsuite.greenmist.compact': 'generic.greenmist.compact',
  'paqsuite.greenmist.material': 'material.teal.light',
  'paqsuite.dark.generic': 'generic.dark',
  'paqsuite.dark.compact': 'generic.dark.compact',
  'paqsuite.dark.material': 'material.blue.dark',
  'paqsuite.blue.generic': 'generic.darkmoon',
  'paqsuite.blue.compact': 'generic.darkmoon.compact',
  'paqsuite.blue.material': 'material.blue.dark',
  'paqsuite.violet.generic': 'generic.darkviolet',
  'paqsuite.violet.compact': 'generic.darkviolet.compact',
  'paqsuite.violet.material': 'material.purple.dark',
  'paqsuite.burgundy.generic': 'generic.carmine',
  'paqsuite.burgundy.compact': 'generic.carmine.compact',
  'paqsuite.burgundy.material': 'material.orange.dark',
}

const stockToPaqsuiteThemeFallback: Readonly<Record<string, string>> = {
  'generic.light': 'paqsuite.light.generic',
  'generic.dark': 'paqsuite.dark.generic',
  'generic.carmine': 'paqsuite.burgundy.generic',
  'generic.softblue': 'paqsuite.rose.generic',
  'generic.darkmoon': 'paqsuite.dark.generic',
  'generic.darkviolet': 'paqsuite.violet.generic',
  'generic.greenmist': 'paqsuite.greenmist.generic',
  'generic.contrast': 'paqsuite.light.generic',
  'generic.light.compact': 'paqsuite.light.compact',
  'generic.dark.compact': 'paqsuite.dark.compact',
  'generic.carmine.compact': 'paqsuite.burgundy.compact',
  'generic.softblue.compact': 'paqsuite.rose.compact',
  'generic.darkmoon.compact': 'paqsuite.dark.compact',
  'generic.darkviolet.compact': 'paqsuite.violet.compact',
  'generic.greenmist.compact': 'paqsuite.greenmist.compact',
  'generic.contrast.compact': 'paqsuite.light.compact',
  'material.blue.light': 'paqsuite.light.material',
  'material.blue.dark': 'paqsuite.blue.material',
  'material.lime.light': 'paqsuite.greenmist.material',
  'material.lime.dark': 'paqsuite.greenmist.material',
  'material.orange.light': 'paqsuite.orange.material',
  'material.orange.dark': 'paqsuite.burgundy.material',
  'material.purple.light': 'paqsuite.rose.material',
  'material.purple.dark': 'paqsuite.violet.material',
  'material.teal.light': 'paqsuite.greenmist.material',
  'material.teal.dark': 'paqsuite.blue.material',
  'material.blue.light.compact': 'paqsuite.light.material',
  'material.blue.dark.compact': 'paqsuite.blue.material',
  'material.lime.light.compact': 'paqsuite.greenmist.material',
  'material.lime.dark.compact': 'paqsuite.greenmist.material',
  'material.orange.light.compact': 'paqsuite.orange.material',
  'material.orange.dark.compact': 'paqsuite.burgundy.material',
  'material.purple.light.compact': 'paqsuite.rose.material',
  'material.purple.dark.compact': 'paqsuite.violet.material',
  'material.teal.light.compact': 'paqsuite.greenmist.material',
  'material.teal.dark.compact': 'paqsuite.blue.material',
  'fluent.blue.light': 'paqsuite.light.generic',
  'fluent.blue.dark': 'paqsuite.dark.generic',
  'fluent.saas.light': 'paqsuite.light.generic',
  'fluent.saas.dark': 'paqsuite.dark.generic',
  'fluent.blue.light.compact': 'paqsuite.light.compact',
  'fluent.blue.dark.compact': 'paqsuite.dark.compact',
  'fluent.saas.light.compact': 'paqsuite.light.compact',
  'fluent.saas.dark.compact': 'paqsuite.dark.compact',
}

export const paqsuiteDxStockBridge: Readonly<Record<string, string>> =
  sdkTheme.paqsuiteDxStockBridge ?? paqsuiteDxStockBridgeFallback

export function normalizeThemeFromApi(theme: string | null | undefined): string {
  if (sdkTheme.normalizeThemeFromApi) {
    return sdkTheme.normalizeThemeFromApi(theme)
  }
  if (!theme) {
    return defaultEmpresaTheme
  }
  if ((empresaThemeKeys as readonly string[]).includes(theme)) {
    return theme
  }
  return stockToPaqsuiteThemeFallback[theme] ?? defaultEmpresaTheme
}

export function mapThemeForApiPersistence(theme: string): string {
  if (sdkTheme.mapThemeForApiPersistence) {
    return sdkTheme.mapThemeForApiPersistence(theme)
  }
  return paqsuiteDxStockBridge[theme] ?? theme
}
