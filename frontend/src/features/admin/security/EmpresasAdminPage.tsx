import {
  EmpresasAdminPage as EmpresasAdminPageSdk,
  type EmpresaListItem,
} from '@paqsuite/react-core'
import { useCallback } from 'react'
import { getAuthSession, getAuthToken, patchAuthSession } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { applyDevExtremeTheme } from '../../../theme/devExtremeThemeSwitcher'

export function EmpresasAdminPage() {
  const handlePreviewTheme = useCallback((dxTheme: string) => {
    void applyDevExtremeTheme(dxTheme, { reloadOnGroupChange: false })
  }, [])

  const handleRestoreCommittedTheme = useCallback((dxTheme: string) => {
    void applyDevExtremeTheme(dxTheme, { reloadOnGroupChange: false })
  }, [])

  const handleEmpresaSaved = useCallback((item: EmpresaListItem) => {
    const session = getAuthSession()
    if (!session) {
      return
    }
    patchAuthSession({
      empresas: session.empresas.map((empresa) =>
        empresa.id === item.id
          ? {
              ...empresa,
              nombreEmpresa: item.nombreEmpresa,
              theme: item.theme,
            }
          : empresa,
      ),
    })
    void applyDevExtremeTheme(item.theme, { reloadOnGroupChange: true })
  }, [])

  return (
    <div data-testid="adminEmpresasPage" style={{ padding: 16 }}>
      <EmpresasAdminPageSdk
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
        i18nNamespace="common"
        tenancyMode="single"
        onPreviewTheme={handlePreviewTheme}
        onRestoreCommittedTheme={handleRestoreCommittedTheme}
        onEmpresaSaved={handleEmpresaSaved}
      />
    </div>
  )
}
