import { useMemo } from 'react'
import { ParametrosGeneralesPage as CoreParametrosPage } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { useParams } from 'react-router-dom'
import { getAuthSession, getAuthToken } from '../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../auth/platformContext'

/** ABM parámetros GEN (`/parametros/:programa` — Auth | Partes). */
export function ParametrosGeneralesPage() {
  const { programa = 'Auth' } = useParams<{ programa: string }>()
  const { t } = useTranslation()
  const session = getAuthSession()
  const platform = useMemo(
    () => buildAuthPlatformHeaders(),
    [session?.activeCompanyId, session?.empresas[0]?.id, session?.tenancy],
  )

  return (
    <CoreParametrosPage
      programa={programa}
      t={(key) => t(key)}
      platform={platform}
      accessToken={getAuthToken()}
    />
  )
}
