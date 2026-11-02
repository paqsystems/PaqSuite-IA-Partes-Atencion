import { describe, expect, it } from 'vitest'
import {
  buildPaqueteHorasHostContext,
  shouldDisablePaqueteHorasEmit,
  shouldMountPaqueteHorasEmit,
} from './paqueteHorasHostContext'

describe('paqueteHorasHostContext', () => {
  it('builds hostContext with selected ids', () => {
    const ctx = buildPaqueteHorasHostContext({
      fechaDesde: '2026-01-01',
      fechaHasta: '2026-01-31',
      clienteId: 5,
      tipoClienteId: null,
      selectedTareaIds: [10, 11],
    })
    expect(ctx.selectedTareaIds).toEqual([10, 11])
    expect(ctx.clienteId).toBe(5)
  })

  it('disables emit without selection or cliente', () => {
    expect(shouldDisablePaqueteHorasEmit(false, 0, false)).toBe(true)
    expect(shouldDisablePaqueteHorasEmit(false, 2, true)).toBe(true)
    expect(shouldDisablePaqueteHorasEmit(false, 2, false)).toBe(false)
  })

  it('does not mount emit on native', () => {
    expect(shouldMountPaqueteHorasEmit(true, true)).toBe(false)
    expect(shouldMountPaqueteHorasEmit(false, true)).toBe(true)
  })
})
