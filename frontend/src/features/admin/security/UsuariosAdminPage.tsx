import { UsuariosAdminPage as UsuariosAdminPageSdk } from '@paqsuite/react-core'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'

export function UsuariosAdminPage() {
  return (
    <div data-testid="adminUsuariosPage" style={{ padding: 16 }}>
      <UsuariosAdminPageSdk
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
        i18nNamespace="common"
      />
    </div>
  )
}
