import {
  current as themesCurrent,
  init as themesInit,
  ready as themesReady,
} from 'devextreme/ui/themes'
import { EMPRESA_THEME_DEFAULT } from '../features/admin/security/empresaThemeCatalog'
import { EMPRESA_THEME_CSS_URLS } from './empresaThemeCssUrls'

const linkRel = 'dx-theme'
/** Persistido antes de reload al previsualizar otro grupo de tema (Generic↔Material↔Fluent). */
export const PENDING_EMPRESA_THEME_KEY = 'paqPendingEmpresaTheme'

let themesBootstrapped = false

export type ApplyDevExtremeThemeOptions = {
  /** DevExtreme solo cambia entre temas del mismo grupo sin reload. Default false. */
  reloadOnGroupChange?: boolean
}

export function resolveEmpresaThemeKey(theme: string | null | undefined): string {
  if (theme && Object.prototype.hasOwnProperty.call(EMPRESA_THEME_CSS_URLS, theme)) {
    return theme
  }
  if (theme) {
    console.warn(`[A1] tema desconocido o no empaquetado: ${theme}; fallback ${EMPRESA_THEME_DEFAULT}`)
  }
  return EMPRESA_THEME_DEFAULT
}

export function themeGroupOf(themeKey: string): string {
  const compact = themeKey.includes('.compact')
  if (themeKey.startsWith('material.')) {
    return compact ? 'material.compact' : 'material'
  }
  if (themeKey.startsWith('fluent.')) {
    return compact ? 'fluent.compact' : 'fluent'
  }
  return compact ? 'generic.compact' : 'generic'
}

function markDocumentTheme(resolved: string): void {
  if (typeof document === 'undefined') {
    return
  }
  document.documentElement.setAttribute('data-pq-theme', resolved)
  document.documentElement.classList.toggle('pq-theme-dark', /\.dark(\.|$)/.test(resolved))
}

/**
 * Inyecta `<link rel="dx-theme">` para todos los temas empaquetados (SPEC-001-19 §5.1).
 * Debe ejecutarse **antes** de `themes.init` (DX consume y remueve estos links).
 */
export function ensureDevExtremeThemeLinks(activeTheme: string = EMPRESA_THEME_DEFAULT): void {
  if (typeof document === 'undefined') {
    return
  }

  const resolved = resolveEmpresaThemeKey(activeTheme)

  for (const [themeKey, href] of Object.entries(EMPRESA_THEME_CSS_URLS)) {
    let link = document.querySelector(
      `link[rel="${linkRel}"][data-theme="${themeKey}"]`
    ) as HTMLLinkElement | null

    if (!link) {
      link = document.createElement('link')
      link.rel = linkRel
      link.setAttribute('data-theme', themeKey)
      link.href = href
      document.head.appendChild(link)
    } else {
      link.href = href
    }

    link.setAttribute('data-active', themeKey === resolved ? 'true' : 'false')
  }

  markDocumentTheme(resolved)
}

/**
 * Cambia el tema DevExtreme en runtime.
 * Primer llamado: `themes.init` (parsea links). Luego: `themes.current`.
 * Si el grupo cambia (Generic ↔ Material ↔ Fluent ↔ Compact) y `reloadOnGroupChange`, recarga.
 */
export function applyDevExtremeTheme(
  theme: string | null | undefined,
  options: ApplyDevExtremeThemeOptions = {}
): Promise<{ theme: string; reloaded: boolean }> {
  const resolved = resolveEmpresaThemeKey(theme)
  const previous =
    typeof document !== 'undefined' ? document.documentElement.getAttribute('data-pq-theme') : null

  const willReload = Boolean(
    options.reloadOnGroupChange &&
      themesBootstrapped &&
      previous &&
      previous !== resolved &&
      themeGroupOf(previous) !== themeGroupOf(resolved) &&
      typeof window !== 'undefined'
  )

  if (willReload) {
    try {
      sessionStorage.setItem(PENDING_EMPRESA_THEME_KEY, resolved)
    } catch {
      // ignore
    }
    markDocumentTheme(resolved)
    window.location.reload()
    return Promise.resolve({ theme: resolved, reloaded: true })
  }

  if (!themesBootstrapped) {
    ensureDevExtremeThemeLinks(resolved)
    return new Promise((resolvePromise) => {
      themesReady(() => {
        themesBootstrapped = true
        markDocumentTheme(resolved)
        resolvePromise({ theme: resolved, reloaded: false })
      })
      themesInit({ theme: resolved })
    })
  }

  markDocumentTheme(resolved)
  return new Promise((resolvePromise) => {
    themesReady(() => {
      resolvePromise({ theme: resolved, reloaded: false })
    })
    themesCurrent(resolved)
  })
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
