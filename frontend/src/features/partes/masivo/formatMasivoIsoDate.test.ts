import { describe, expect, it } from 'vitest'
import { formatMasivoIsoDate } from './formatMasivoIsoDate'

describe('formatMasivoIsoDate', () => {
  it('convierte ISO a dd/MM/yyyy en locales latinos', () => {
    expect(formatMasivoIsoDate('2026-01-01', 'es')).toBe('01/01/2026')
    expect(formatMasivoIsoDate('2026-11-06', 'fr')).toBe('06/11/2026')
  })

  it('convierte ISO a MM/dd/yyyy en inglés', () => {
    expect(formatMasivoIsoDate('2026-11-06', 'en')).toBe('11/06/2026')
  })

  it('keeps non-ISO values', () => {
    expect(formatMasivoIsoDate('')).toBe('')
    expect(formatMasivoIsoDate('06/11/2026')).toBe('06/11/2026')
  })
})
