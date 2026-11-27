import { apiRequest, type ApiClientResult } from '@paqsuite/react-core'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { getAuthToken } from '../../auth/authSessionStore'

export type EmissionDesignHydrateResult = {
  processCode: string
  hydrated: number
  skipped: number
  failed: Array<{ code: string; reason: string }>
}

export async function postEmissionDesignHydrateSidecar(
  processCode: string,
  reportCodes?: string[],
): Promise<ApiClientResult<EmissionDesignHydrateResult>> {
  const token = getAuthToken() ?? undefined
  const platform = buildAuthPlatformHeaders()

  return apiRequest<EmissionDesignHydrateResult>(
    `/api/v1/emissions/design/processes/${encodeURIComponent(processCode)}/reports/hydrate-sidecar`,
    {
      method: 'POST',
      headers: token ? { Authorization: `Bearer ${token}` } : undefined,
      platform,
      body:
        reportCodes && reportCodes.length > 0
          ? JSON.stringify({ reportCodes })
          : undefined,
    },
  )
}