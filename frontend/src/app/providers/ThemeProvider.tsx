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
export function ThemeProvider({ children }: { children: ReactNode }) {
  const [themeReady, setThemeReady] = useState(false)

  useLayoutEffect(() => {
    const themeKey = resolveInitialThemeKey()
    void applyThemeWithFallback(themeKey).finally(() => {
      setThemeReady(true)
    })
  }, [])

  if (!themeReady) {
    return null
  }

  return <>{children}</>
}
