import { EmpresasAdminPage as EmpresasAdminPageSdk, type EmpresaListItem } from '@paqsuite/react-core'
import { useCallback } from 'react'
import { useTranslation } from 'react-i18next'
import { getAuthSession, getAuthToken, patchAuthSession } from '../../auth/authSessionStore'
import { resolveAuthMessage } from '../../auth/authMessages'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { applyDevExtremeTheme } from '../../../theme/devExtremeThemeSwitcher'
import {
  empresasAdminSdkFallbackEs,
  empresasAdminSdkToHost,
} from './securityEmpresasSdkI18n'

export function EmpresasAdminPage() {
  const { t } = useTranslation()

  const translateSdk = (key: string) => {
    if (key.startsWith('admin.empresas.')) {
      const hostKey = empresasAdminSdkToHost[key]
      const fallback = empresasAdminSdkFallbackEs[key]
      if (hostKey) {
        return t(hostKey, fallback ?? key)
      }
      return t(key, fallback ?? key)
    }
    return resolveAuthMessage(key) ?? key
  }

  const handlePreviewTheme = useCallback((theme: string) => {
    void applyDevExtremeTheme(theme, { reloadOnGroupChange: true })
  }, [])

  const handleRestoreCommittedTheme = useCallback((theme: string) => {
    void applyDevExtremeTheme(theme, { reloadOnGroupChange: true })
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
