import { describe, expect, it } from 'vitest'
import {
  getEmissionHostContextSnapshot,
  isEmissionHostContextUrl,
  setEmissionHostContextSnapshot,
} from './emissionHostContextBridge'

describe('emissionHostContextBridge', () => {
  it('guarda el snapshot actual de filtros', () => {
    setEmissionHostContextSnapshot({
      fechaDesde: '2026-08-01',
      fechaHasta: '2026-08-02',
      clienteId: 1,
      usuarioId: null,
      tipoTareaId: null,
      estadoCerrado: 'todas',
    })
    expect(getEmissionHostContextSnapshot()?.clienteId).toBe(1)
  })

  it('solo inyecta en POST jobs y preview', () => {
    expect(isEmissionHostContextUrl('/api/v1/emissions/jobs', 'POST')).toBe(true)
    expect(isEmissionHostContextUrl('/api/v1/emissions/preview', 'post')).toBe(true)
    expect(isEmissionHostContextUrl('/api/v1/emissions/jobs/abc', 'GET')).toBe(false)
    expect(isEmissionHostContextUrl('/api/v1/emissions/processes/x', 'GET')).toBe(false)
  })
})
