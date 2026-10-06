import { EmpresasAdminPage as EmpresasAdminPageSdk } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { resolveAuthMessage } from '../../auth/authMessages'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
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

  return (
    <div data-testid="adminEmpresasPage" style={{ padding: 16 }}>
      <EmpresasAdminPageSdk
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
        t={translateSdk}
        tenancyMode="single"
      />
    </div>
  )
}
