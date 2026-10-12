import {
  EmpresasAdminPage as EmpresasAdminPageSdk,
  useEmpresaAppearanceBridge,
  type EmpresaListItem,
} from '@paqsuite/react-core'
import { useCallback } from 'react'
import { getAuthSession, getAuthToken, patchAuthSession } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'

export function EmpresasAdminPage() {
  const { previewTheme, restoreTheme, applyCommitted } = useEmpresaAppearanceBridge()

  const handleEmpresaSaved = useCallback(
    (item: EmpresaListItem) => {
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
      applyCommitted(item.theme)
    },
    [applyCommitted],
  )

  return (
    <div data-testid="adminEmpresasPage" style={{ padding: 16 }}>
      <EmpresasAdminPageSdk
        accessToken={getAuthToken()}
        platform={buildAuthPlatformHeaders()}
        i18nNamespace="common"
        tenancyMode="single"
        onPreviewTheme={previewTheme}
        onRestoreCommittedTheme={restoreTheme}
        onEmpresaSaved={handleEmpresaSaved}
      />
    </div>
  )
}
