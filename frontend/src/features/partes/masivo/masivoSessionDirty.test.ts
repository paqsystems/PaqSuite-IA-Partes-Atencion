import { describe, expect, it } from 'vitest'
import { computeMasivoSessionDirty } from './masivoSessionDirty'

describe('computeMasivoSessionDirty', () => {
  const base = {
    selectedCount: 0,
    touchSinCargo: false,
    touchPresencial: false,
    touchFecha: false,
    applyTipoTareaId: null as number | null,
    applyUsuarioId: null as number | null,
  }

  it('returns false when nothing pending', () => {
    expect(computeMasivoSessionDirty(base)).toBe(false)
  })

  it('returns true when there is selection', () => {
    expect(computeMasivoSessionDirty({ ...base, selectedCount: 2 })).toBe(true)
  })

  it('returns true when batch fields were touched', () => {
    expect(computeMasivoSessionDirty({ ...base, touchFecha: true })).toBe(true)
  })
})
