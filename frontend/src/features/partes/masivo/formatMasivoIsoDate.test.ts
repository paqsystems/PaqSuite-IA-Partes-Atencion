import { describe, expect, it } from 'vitest'
import { formatMasivoIsoDate } from './formatMasivoIsoDate'

describe('formatMasivoIsoDate', () => {
  it('converts ISO date to dd/MM/yyyy', () => {
    expect(formatMasivoIsoDate('2026-01-01')).toBe('01/01/2026')
    expect(formatMasivoIsoDate('2026-11-06')).toBe('06/11/2026')
  })

  it('keeps non-ISO values', () => {
    expect(formatMasivoIsoDate('')).toBe('')
    expect(formatMasivoIsoDate('06/11/2026')).toBe('06/11/2026')
  })
})
