import config from 'devextreme/core/config'
import type { ReactNode } from 'react'
import { useLayoutEffect } from 'react'
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
 * Licencia DX + tema inicial (layout effect: antes del paint de hijos).
 * Prioridad: preview pendiente (Aplicar con reload) → sesión empresa → generic.light.
 */
export function ThemeProvider({ children }: { children: ReactNode }) {
  useLayoutEffect(() => {
    config({
      licenseKey: import.meta.env.VITE_DEVEXTREME_LICENSE ?? '',
    })

    const pendingTheme = consumePendingEmpresaTheme()
    if (pendingTheme) {
      void applyDevExtremeTheme(pendingTheme, { reloadOnGroupChange: false })
      return
    }

    const session = getAuthSession()
    const theme = session
      ? getActiveEmpresaThemeFromSession({
          activeCompanyId: session.activeCompanyId,
          empresas: session.empresas,
        })
      : EMPRESA_THEME_DEFAULT
    void applyDevExtremeTheme(theme, { reloadOnGroupChange: false })
  }, [])

  return <>{children}</>
}
