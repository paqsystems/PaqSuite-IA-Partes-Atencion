import { SecurityRolesPage } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { useNavigate } from 'react-router-dom'
import { resolveAuthMessage } from '../../auth/authMessages'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { securityRolesSdkToHost } from './securityRolesSdkI18n'

export function RolesAdminPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()

  const translateSdk = (key: string) => {
    if (key.startsWith('security.roles.')) {
      const hostKey = securityRolesSdkToHost[key]
      return hostKey ? t(hostKey) : t(key)
    }
    return resolveAuthMessage(key) ?? key
  }

  return (
    <div data-testid="adminRolesPage" style={{ padding: 16 }}>
      <SecurityRolesPage
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
        t={translateSdk}
        onNavigateToAtributos={(rolId) => navigate(`/admin/roles/${rolId}/atributos`)}
      />
    </div>
  )
}
