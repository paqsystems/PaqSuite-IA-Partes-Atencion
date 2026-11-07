import type { ReactNode } from 'react'
import { useLayoutEffect, useState } from 'react'
import { getAuthSession } from '../../features/auth/authSessionStore'
import { EMPRESA_THEME_DEFAULT } from '../../features/admin/security/empresaThemeCatalog'
import {
  applyDevExtremeTheme,
  consumePendingEmpresaTheme,
  getActiveEmpresaThemeFromSession,
} from '../../theme/devExtremeThemeSwitcher'
import '../../theme/dxIconsFix.css'

function resolveInitialThemeKey(): string {
  const pendingTheme = consumePendingEmpresaTheme()
  if (pendingTheme) {
    return pendingTheme
  }

  const session = getAuthSession()
  if (!session) {
    return EMPRESA_THEME_DEFAULT
  }

  return getActiveEmpresaThemeFromSession({
    activeCompanyId: session.activeCompanyId,
    empresas: session.empresas,
  })
}

async function applyThemeWithFallback(themeKey: string): Promise<void> {
  try {
    await applyDevExtremeTheme(themeKey, { reloadOnGroupChange: false })
    return
  } catch (error) {
    console.error('[ThemeProvider] No se pudo aplicar el tema de empresa:', themeKey, error)
  }

  if (themeKey === EMPRESA_THEME_DEFAULT) {
    return
  }

  try {
    await applyDevExtremeTheme(EMPRESA_THEME_DEFAULT, { reloadOnGroupChange: false })
  } catch (fallbackError) {
    console.error(
      '[ThemeProvider] Fallback de tema también falló; la app montará sin tema DX aplicado.',
      fallbackError,
    )
  }
}

/**
 * Tema inicial DevExtreme (layout effect: antes del paint de hijos).
 * Licencia: `src/init-devextreme-license.ts` (importado primero en `main.tsx`).
 * Prioridad: preview pendiente (reload de grupo) → sesión empresa → default SDK (`paqsuite.light.generic`).
 */
/** Si `themes.init` / `themesReady` no responde (red, CSP, 404 en `/dx-themes`), sin esto la SPA queda en blanco para siempre. */
const THEME_BOOTSTRAP_TIMEOUT_MS = 10_000

export function ThemeProvider({ children }: { children: ReactNode }) {
  const [themeReady, setThemeReady] = useState(false)

  useLayoutEffect(() => {
    let mounted = true
    const themeKey = resolveInitialThemeKey()
    const timeoutId = window.setTimeout(() => {
      if (!mounted) {
        return
      }
      console.warn(
        '[ThemeProvider] Tiempo de espera del tema DevExtreme agotado; se monta la UI igual. Revisá red / /dx-themes / consola.',
      )
      setThemeReady(true)
    }, THEME_BOOTSTRAP_TIMEOUT_MS)

    void applyThemeWithFallback(themeKey).finally(() => {
      window.clearTimeout(timeoutId)
      if (mounted) {
        setThemeReady(true)
      }
    })

    return () => {
      mounted = false
      window.clearTimeout(timeoutId)
    }
  }, [])

  if (!themeReady) {
    return null
  }

  return <>{children}</>
}
