import { describe, expect, it, vi, beforeEach } from 'vitest'
import { postEmissionDesignHydrateSidecar } from './emissionDesignHydrateApi'

vi.mock('../../auth/authSessionStore', () => ({
  getAuthToken: () => 'test-token',
}))

vi.mock('../../auth/platformContext', () => ({
  buildAuthPlatformHeaders: () => ({ cliente: 'DEMO' }),
}))

vi.mock('@paqsuite/react-core', () => ({
  apiRequest: vi.fn(),
}))

import { apiRequest } from '@paqsuite/react-core'

describe('postEmissionDesignHydrateSidecar', () => {
  beforeEach(() => {
    vi.mocked(apiRequest).mockReset()
  })

  it('llama POST hydrate-sidecar con proceso', async () => {
    vi.mocked(apiRequest).mockResolvedValue({
      kind: 'ok',
      envelope: { error: 0, respuesta: 'ok', resultado: { hydrated: 1, skipped: 0, failed: [] } },
    })

    await postEmissionDesignHydrateSidecar('partes.informes.consultaDetallada')

    expect(apiRequest).toHaveBeenCalledWith(
      '/api/v1/emissions/design/processes/partes.informes.consultaDetallada/reports/hydrate-sidecar',
      expect.objectContaining({ method: 'POST' }),
    )
  })

  it('envía reportCodes cuando se filtra variante', async () => {
    vi.mocked(apiRequest).mockResolvedValue({
      kind: 'ok',
      envelope: { error: 0, respuesta: 'ok', resultado: { hydrated: 1, skipped: 0, failed: [] } },
    })

    await postEmissionDesignHydrateSidecar('partes.informes.consultaDetallada', ['partes.foo'])

    expect(apiRequest).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({
        body: JSON.stringify({ reportCodes: ['partes.foo'] }),
      }),
    )
  })
})