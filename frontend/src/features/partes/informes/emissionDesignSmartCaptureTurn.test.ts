import { describe, expect, it } from 'vitest'
import { APPLY_URL, turnUrlFor } from './emissionDesignSmartCaptureTurn'

describe('emissionDesignSmartCaptureTurn helpers', () => {
  it('arma URL de turno por processCode', () => {
    expect(turnUrlFor('partes.informes.consultaDetallada')).toBe(
      '/api/v1/emissions/design/processes/partes.informes.consultaDetallada/assistant/turn',
    )
  })

  it('usa path same-origin de apply (proxy Vite)', () => {
    expect(APPLY_URL).toBe('/api/layout/apply')
  })
})
