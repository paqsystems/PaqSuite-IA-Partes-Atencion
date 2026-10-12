import { RolAtributosPage } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { useNavigate, useParams } from 'react-router-dom'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'

export function RoleAtributosPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const { id } = useParams()
  const rolId = Number(id)

  if (!Number.isFinite(rolId) || rolId <= 0) {
    return (
      <div role="alert" data-testid="adminRolesAtributosLoadError">
        {t('security.roles.atributos.loadError')}
      </div>
    )
  }

  return (
    <div style={{ padding: 16 }} data-testid="adminRolesAtributosPage">
      <RolAtributosPage
        rolId={rolId}
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
        t={(key) => t(key)}
        onBack={() => navigate('/admin/roles')}
      />
    </div>
  )
}
