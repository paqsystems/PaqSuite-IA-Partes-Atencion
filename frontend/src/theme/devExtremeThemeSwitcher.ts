import {
  applyDxTheme,
  applyShellTokens,
  clearShellTokens,
  defaultEmpresaTheme,
  normalizeThemeFromApi,
  paqsuiteDxStockBridge,
  resolveEmpresaAppearance,
} from '@paqsuite/react-core'

/** Persistido antes de reload al cambiar grupo DX (Generic ↔ Material ↔ Fluent). */
export const PENDING_EMPRESA_THEME_KEY = 'paqPendingEmpresaTheme'

export type ApplyDevExtremeThemeOptions = {
  reloadOnGroupChange?: boolean
}

function resolveDxKey(paqsuiteKey: string): string {
  return (
    paqsuiteDxStockBridge[paqsuiteKey] ??
    paqsuiteDxStockBridge[defaultEmpresaTheme] ??
    'generic.light'
  )
}

export function resolveEmpresaThemeKey(theme: string | null | undefined): string {
  const normalized = normalizeThemeFromApi(theme)
  if (!paqsuiteDxStockBridge[normalized]) {
    console.warn(`[A1] tema desconocido: ${theme ?? ''}; fallback ${defaultEmpresaTheme}`)
    return defaultEmpresaTheme
  }
  return normalized
}

export function themeGroupOf(themeKey: string): string {
  const dxKey = resolveDxKey(resolveEmpresaThemeKey(themeKey))
  const compact = dxKey.includes('.compact')
  if (dxKey.startsWith('material.')) {
    return compact ? 'material.compact' : 'material'
  }
  if (dxKey.startsWith('fluent.')) {
    return compact ? 'fluent.compact' : 'fluent'
  }
  return compact ? 'generic.compact' : 'generic'
}

function readCurrentPaqsuiteTheme(): string | null {
  if (typeof document === 'undefined') {
    return null
  }
  return document.documentElement.getAttribute('data-theme')
}

async function applyAppearance(theme: string | null | undefined): Promise<string> {
  const paqsuiteKey = resolveEmpresaThemeKey(theme)
  const appearance = resolveEmpresaAppearance(paqsuiteKey)
  if (appearance.theme) {
    await applyDxTheme(appearance.theme)
  }
  if (appearance.tokens) {
    applyShellTokens(appearance.tokens)
  } else {
    clearShellTokens()
  }
  return paqsuiteKey
}

/**
 * Aplica apariencia A1 (GEN-19): tema stock DevExtreme + tokens shell del SDK.
 * No reimplementar catálogo ni CSS: `@paqsuite/react-core` + `registerEmpresasAdminI18nResources`.
 */
export function applyDevExtremeTheme(
  theme: string | null | undefined,
  options: ApplyDevExtremeThemeOptions = {},
): Promise<{ theme: string; reloaded: boolean }> {
  const resolved = resolveEmpresaThemeKey(theme)
  const previous = readCurrentPaqsuiteTheme()

  const willReload = Boolean(
    options.reloadOnGroupChange &&
      previous &&
      previous !== resolved &&
      themeGroupOf(previous) !== themeGroupOf(resolved) &&
      typeof window !== 'undefined',
  )

  if (willReload) {
    try {
      sessionStorage.setItem(PENDING_EMPRESA_THEME_KEY, resolved)
    } catch {
      // ignore
    }
    window.location.reload()
    return Promise.resolve({ theme: resolved, reloaded: true })
  }

  return applyAppearance(theme).then((applied) => ({ theme: applied, reloaded: false }))
}

export function consumePendingEmpresaTheme(): string | null {
  try {
    const pending = sessionStorage.getItem(PENDING_EMPRESA_THEME_KEY)
    if (!pending) {
      return null
    }
    sessionStorage.removeItem(PENDING_EMPRESA_THEME_KEY)
    return resolveEmpresaThemeKey(pending)
  } catch {
    return null
  }
}

export function getActiveEmpresaThemeFromSession(input: {
  activeCompanyId?: number
  empresas: Array<{ id: number; theme?: string | null }>
}): string {
  const activeId = input.activeCompanyId
  const match =
    (activeId !== undefined ? input.empresas.find((empresa) => empresa.id === activeId) : undefined) ??
    input.empresas[0]
  return resolveEmpresaThemeKey(match?.theme)
}

export async function bootstrapDevExtremeThemeBeforeMount(
  theme: string = defaultEmpresaTheme,
): Promise<string> {
  const { theme: resolved } = await applyDevExtremeTheme(theme, { reloadOnGroupChange: false })
  return resolved
}
