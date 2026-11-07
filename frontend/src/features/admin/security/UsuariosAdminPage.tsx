import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { PartesUsuariosAdminPage } from './partesUsuariosAdminPage'

/**
 * GEN-06 usuarios — CC #16: formulario host con notify por usuario (deuda Framework: props en UsuariosFormPopup).
 */
export function UsuariosAdminPage() {
  return (
    <div data-testid="adminUsuariosPage" style={{ padding: 16 }}>
      <PartesUsuariosAdminPage
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
        i18nNamespace="common"
      />
    </div>
  )
}
