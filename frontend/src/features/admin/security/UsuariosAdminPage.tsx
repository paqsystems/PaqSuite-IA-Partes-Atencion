import { isNativeApp, UsuariosAdminPage as UsuariosAdminPageSdk } from '@paqsuite/react-core'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { NotifyUsuarioAccessToolbar } from './NotifyUsuarioAccessDialog'

export function UsuariosAdminPage() {
  return (
    <div data-testid="adminUsuariosPage" style={{ padding: 16 }}>
      {!isNativeApp() ? <NotifyUsuarioAccessToolbar /> : null}
      <UsuariosAdminPageSdk
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
        i18nNamespace="common"
      />
    </div>
  )
}
