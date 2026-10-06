import { RolAtributosPage } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { useNavigate, useParams } from 'react-router-dom'
import { resolveAuthMessage } from '../../auth/authMessages'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { securityRolesSdkToHost } from './securityRolesSdkI18n'

export function RoleAtributosPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const { id } = useParams()
  const rolId = Number(id)

  const translateSdk = (key: string) => {
    if (key.startsWith('security.roles.')) {
      const hostKey = securityRolesSdkToHost[key]
      return hostKey ? t(hostKey) : t(key)
    }
    return resolveAuthMessage(key) ?? key
  }

  if (!Number.isFinite(rolId) || rolId <= 0) {
    return (
      <div role="alert" data-testid="adminRolesAtributosLoadError">
        {t('admin.roles.atributos.loadError')}
      </div>
    )
  }

  return (
    <div style={{ padding: 16 }} data-testid="adminRolesAtributosPage">
      <RolAtributosPage
        rolId={rolId}
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
        t={translateSdk}
        onBack={() => navigate('/admin/roles')}
      />
    </div>
  )
}
