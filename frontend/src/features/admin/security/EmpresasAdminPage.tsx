import {
  EmpresasAdminPage as EmpresasAdminPageSdk,
  type EmpresaListItem,
} from '@paqsuite/react-core'
import { useCallback } from 'react'
import { useTranslation } from 'react-i18next'
import { getAuthSession, getAuthToken, patchAuthSession } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { applyDevExtremeTheme } from '../../../theme/devExtremeThemeSwitcher'
import { translateEmpresasAdminSdk } from './securityEmpresasSdkI18n'

export function EmpresasAdminPage() {
  const { t } = useTranslation()

  const translateSdk = useCallback(
    (key: string) => translateEmpresasAdminSdk(key, t),
    [t],
  )

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
        t={translateSdk}
        tenancyMode="single"
        onPreviewTheme={handlePreviewTheme}
        onRestoreCommittedTheme={handleRestoreCommittedTheme}
        onEmpresaSaved={handleEmpresaSaved}
      />
    </div>
  )
}
