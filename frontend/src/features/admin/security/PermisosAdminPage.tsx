import { PermisosAdminPage as PermisosAdminPageSdk } from '@paqsuite/react-core'
import { getAuthSession, getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'

export function PermisosAdminPage() {
  const session = getAuthSession()
  const defaultEmpresaId = session?.empresas[0]?.id

  return (
    <div data-testid="adminPermisosPage" style={{ padding: 16 }}>
      <PermisosAdminPageSdk
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
        i18nNamespace="common"
        tenancyMode="single"
        defaultEmpresaId={defaultEmpresaId}
      />
    </div>
  )
}
