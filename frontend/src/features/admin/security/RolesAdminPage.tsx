import { SecurityRolesPage } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { useNavigate } from 'react-router-dom'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'

export function RolesAdminPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()

  return (
    <div data-testid="adminRolesPage" style={{ padding: 16 }}>
      <SecurityRolesPage
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
        t={(key) => t(key)}
        onNavigateToAtributos={(rolId) => navigate(`/admin/roles/${rolId}/atributos`)}
      />
    </div>
  )
}
