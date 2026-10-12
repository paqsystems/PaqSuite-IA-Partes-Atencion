import {
  EmpresasAdminPage as EmpresasAdminPageSdk,
  type EmpresaListItem,
} from '@paqsuite/react-core'
import { useCallback } from 'react'
import { getAuthSession, getAuthToken, patchAuthSession } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { applyDevExtremeTheme } from '../../../theme/devExtremeThemeSwitcher'

export function EmpresasAdminPage() {
  const previewTheme = useCallback((themeKey: string) => {
    void applyDevExtremeTheme(themeKey, { reloadOnGroupChange: false })
  }, [])

  const restoreTheme = useCallback((themeKey: string) => {
    void applyDevExtremeTheme(themeKey, { reloadOnGroupChange: false })
  }, [])

  const handleEmpresaSaved = useCallback((item: EmpresaListItem) => {
    const session = getAuthSession()
    if (session) {
      patchAuthSession({
        empresas: session.empresas.map((empresa) =>
          Number(empresa.id) === Number(item.id)
            ? {
                ...empresa,
                nombreEmpresa: item.nombreEmpresa,
                theme: item.theme,
              }
            : empresa,
        ),
      })
    }
    void applyDevExtremeTheme(item.theme, { reloadOnGroupChange: false })
  }, [])

  return (
    <div data-testid="adminEmpresasPage" style={{ padding: 16 }}>
      <EmpresasAdminPageSdk
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
        i18nNamespace="common"
        tenancyMode="single"
        onPreviewTheme={previewTheme}
        onRestoreCommittedTheme={restoreTheme}
        onEmpresaSaved={handleEmpresaSaved}
      />
    </div>
  )
}
