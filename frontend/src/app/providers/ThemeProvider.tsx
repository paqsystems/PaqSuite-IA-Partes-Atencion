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
import '../../theme/shellAppearanceBridge.css'

/**
 * Tema inicial DevExtreme (layout effect: antes del paint de hijos).
 * Licencia: `src/init-devextreme-license.ts` (importado primero en `main.tsx`).
 * Prioridad: preview pendiente (Aplicar con reload) → sesión empresa → generic.light.
 */
export function ThemeProvider({ children }: { children: ReactNode }) {
  const [themeReady, setThemeReady] = useState(false)

  useLayoutEffect(() => {
    const pendingTheme = consumePendingEmpresaTheme()
    if (pendingTheme) {
      void applyDevExtremeTheme(pendingTheme, { reloadOnGroupChange: false }).then(() => {
        setThemeReady(true)
      })
      return
    }

    const session = getAuthSession()
    const theme = session
      ? getActiveEmpresaThemeFromSession({
          activeCompanyId: session.activeCompanyId,
          empresas: session.empresas,
        })
      : EMPRESA_THEME_DEFAULT
    void applyDevExtremeTheme(theme, { reloadOnGroupChange: false }).then(() => {
      setThemeReady(true)
    })
  }, [])

  if (!themeReady) {
    return null
  }

  return <>{children}</>
}
